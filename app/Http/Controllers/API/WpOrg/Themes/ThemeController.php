<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Data\WpOrg\Themes\QueryThemesResponse;
use App\Data\WpOrg\Themes\ThemeInformationRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\ThemeCollection;
use App\Http\Resources\ThemeResource;
use App\Models\WpOrg\Theme;
use App\Models\WpOrg\SyncTheme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use function Safe\json_decode;
use function Safe\preg_match;

class ThemeController extends Controller
{
    /**
    * @param Request $request
    *
    * @return JsonResponse
    */
    public function info(Request $request): JsonResponse|Response
    {
        try {
            $action = $request->query('action');
            $response = match ($action) {
                'query_themes' => $this->doQueryThemes(QueryThemesRequest::fromRequest($request)),
                'theme_information' => $this->doThemeInformation(ThemeInformationRequest::fromRequest($request)),
                'hot_tags' => $this->doHotTags(),
                'feature_list' => $this->doFeatureList(),
                default => $this->unknownAction()
            };
            return $response;
        } catch (ValidationException $e) {
            // Handle validation errors and return a custom response
            $firstErrorMessage = collect($e->errors())->flatten()->first();
            return  $this->sendResponse(['error' => $firstErrorMessage], 400);
        }
    }

    private function doQueryThemes(QueryThemesRequest $req): JsonResponse|Response
    {
        $page = $req->page;
        $perPage = $req->per_page;
        $skip = ($page - 1) * $perPage;

        // TODO: process search and other filters
        $themes = Theme::query()
            ->when($req->browse, function ($query, $browse) {
                if ($browse === 'popular') {
                    $query->orderBy('rating', 'desc');
                } else {
                    $query->orderBy('last_updated', 'desc');
                }
            })
            ->when($req->search, function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%");
                //->orWhere('description', 'like', "%{$search}%");
            })->when($req->theme, function ($query, $search) {
                $query->where('slug', 'ilike', $search);
            })->when($req->author, function (Builder $query, string $author) {
                $query->whereHas('author', function (Builder $query) use ($author) {
                    $query->where('user_nicename', 'like', "%{$author}%");
                });
            })->when($req->tags, function (Builder $query, array $tags) {
                collect($tags)->each(function ($tag) use ($query) {
                    $query->whereJsonContains('tags', $tag);
                });
            })
            ->skip($skip)
            ->take($perPage)
            ->with('author')
            ->get();
        $total = DB::table('themes')->count();

        $collection = collect($themes)->map(fn($theme) => (new ThemeResource($theme))->additional(['fields' => $req->fields]));

        return $this->sendResponse(new ThemeCollection($collection, $page, (int) ceil($total / $perPage), $total));
    }

    /** @return JsonResponse|Response */
    private function doThemeInformation(ThemeInformationRequest $request): JsonResponse|Response
    {
        $theme = Theme::query()->where('slug', $request->slug)->first();

        if (!$theme) {
            return $this->sendResponse(['error' => 'Theme not found'], 404);
        }
        return $this->sendResponse((new ThemeResource($theme))->additional(['fields' => $request->fields]));
    }

    /** @return JsonResponse|Response */
    private function doHotTags(): JsonResponse|Response
    {
        return $this->sendResponse(['error' => 'Not Implemented'], 400);
    }

    /** @return JsonResponse|Response */
    private function doFeatureList(): JsonResponse|Response
    {
        $request = request();
        $tags = [
            __('Colors')   => [
                'black'  => __('Black'),
                'blue'   => __('Blue'),
                'brown'  => __('Brown'),
                'gray'   => __('Gray'),
                'green'  => __('Green'),
                'orange' => __('Orange'),
                'pink'   => __('Pink'),
                'purple' => __('Purple'),
                'red'    => __('Red'),
                'silver' => __('Silver'),
                'tan'    => __('Tan'),
                'white'  => __('White'),
                'yellow' => __('Yellow'),
                'dark'   => __('Dark'),
                'light'  => __('Light'),
            ],
            __('Columns')  => [
                'one-column'    => __('One Column'),
                'two-columns'   => __('Two Columns'),
                'three-columns' => __('Three Columns'),
                'four-columns'  => __('Four Columns'),
                'left-sidebar'  => __('Left Sidebar'),
                'right-sidebar' => __('Right Sidebar'),
            ],
            __('Layout')   => [
                'fixed-layout'      => __('Fixed Layout'),
                'fluid-layout'      => __('Fluid Layout'),
                'responsive-layout' => __('Responsive Layout'),
            ],
            __('Features') => [
                'accessibility-ready'   => __('Accessibility Ready'),
                'blavatar'              => __('Blavatar'),
                'buddypress'            => __('BuddyPress'),
                'custom-background'     => __('Custom Background'),
                'custom-colors'         => __('Custom Colors'),
                'custom-header'         => __('Custom Header'),
                'custom-menu'           => __('Custom Menu'),
                'editor-style'          => __('Editor Style'),
                'featured-image-header' => __('Featured Image Header'),
                'featured-images'       => __('Featured Images'),
                'flexible-header'       => __('Flexible Header'),
                'front-page-post-form'  => __('Front Page Posting'),
                'full-width-template'   => __('Full Width Template'),
                'microformats'          => __('Microformats'),
                'post-formats'          => __('Post Formats'),
                'rtl-language-support'  => __('RTL Language Support'),
                'sticky-post'           => __('Sticky Post'),
                'theme-options'         => __('Theme Options'),
                'threaded-comments'     => __('Threaded Comments'),
                'translation-ready'     => __('Translation Ready'),
            ],
            __('Subject')  => [
                'holiday'       => __('Holiday'),
                'photoblogging' => __('Photoblogging'),
                'seasonal'      => __('Seasonal'),
            ],
        ];
        $wpVersion = $this->getWpVersion($request);

        // Pre 3.8 installs get width tags instead of layout tags.
        if (isset($wpVersion) && version_compare($wpVersion, '3.7.999', '<')) {
            unset($tags[ __('Layout') ]);
            $tags[ __('Width') ] = [
                'fixed-width'    => __('Fixed Width'),
                'flexible-width' => __('Flexible Width'),
            ];

            if (array_key_exists('accessibility-ready', $tags[ __('Features') ])) {
                unset($tags[ __('Features') ]['accessibility-ready']);
            }
        }

        if (! isset($wpVersion) || version_compare($wpVersion, '3.9-beta', '>')) {
            $tags[ __('Layout') ] = array_merge($tags[ __('Layout') ], $tags[ __('Columns') ]);
            unset($tags[ __('Columns') ]);
        }

        // See https://core.trac.wordpress.org/ticket/33407.
        if (! isset($wpVersion) || version_compare($wpVersion, '4.6-alpha', '>')) {
            unset($tags[ __('Colors') ]);
            $tags[ __('Layout') ] = [
                'grid-layout'   => __('Grid Layout'),
                'one-column'    => __('One Column'),
                'two-columns'   => __('Two Columns'),
                'three-columns' => __('Three Columns'),
                'four-columns'  => __('Four Columns'),
                'left-sidebar'  => __('Left Sidebar'),
                'right-sidebar' => __('Right Sidebar'),
            ];

            unset($tags[ __('Features') ]['blavatar']);
            $tags[ __('Features') ]['footer-widgets'] = __('Footer Widgets');
            $tags[ __('Features') ]['custom-logo']    = __('Custom Logo');
            asort($tags[ __('Features') ]); // To move footer-widgets to the right place.

            $tags[ __('Subject') ] = [
                'blog'           => __('Blog'),
                'e-commerce'     => __('E-Commerce'),
                'education'      => __('Education'),
                'entertainment'  => __('Entertainment'),
                'food-and-drink' => __('Food & Drink'),
                'holiday'        => __('Holiday'),
                'news'           => __('News'),
                'photography'    => __('Photography'),
                'portfolio'      => __('Portfolio'),
            ];
        }

        // See https://core.trac.wordpress.org/ticket/46272.
        if (! isset($wpVersion) || version_compare($wpVersion, '5.2-alpha', '>=')) {
            $tags[ __('Layout') ]['wide-blocks']    = __('Wide Blocks');
            $tags[ __('Features') ]['block-styles'] = __('Block Editor Styles');
            asort($tags[ __('Features') ]); // To move block-styles to the right place.
        }

        // See https://core.trac.wordpress.org/ticket/50164.
        if (! isset($wpVersion) || version_compare($wpVersion, '5.5-alpha', '>=')) {
            $tags[ __('Features') ]['block-patterns']    = __('Block Editor Patterns');
            $tags[ __('Features') ]['full-site-editing'] = __('Full Site Editing');
            asort($tags[ __('Features') ]);
        }

        // See https://core.trac.wordpress.org/ticket/53556.
        if (! isset($wpVersion) || version_compare($wpVersion, '5.8.1-alpha', '>=')) {
            $tags[ __('Features') ]['template-editing'] = __('Template Editing');
            asort($tags[ __('Features') ]);
        }

        // See https://core.trac.wordpress.org/ticket/56869.
        if (! isset($wpVersion) || version_compare($wpVersion, '6.0-alpha', '>=')) {
            $tags[ __('Features') ]['style-variations'] = __('Style Variations');
            asort($tags[ __('Features') ]);
        }

        // Only return tag slugs, to stay compatible with bbpress-version of Themes API.
        foreach ($tags as $title => $group) {
            $tags[ $title ] = array_keys($group);
        }


        return $this->sendResponse($tags);
    }


    private function unknownAction(): Response
    {
        return $this->sendResponse(
            ['error' => 'Action not implemented. <a href="https://codex.wordpress.org/WordPress.org_API">API Docs</a>";}'],
            404
        );
    }

    /**
     * Send response based on API version.
     *
     * @param array<string,mixed>|ThemeCollection $response
     * @param int $statusCode
     * @return Response|JsonResponse
     */
    private function sendResponse(array|ThemeCollection|ThemeResource $response, int $statusCode = 200): JsonResponse|Response
    {
        $version = request()->route('version');
        if ($version === '1.0') {
            return response(serialize((object) $response), $statusCode);
        }
        return response()->json($response, $statusCode);
    }

    private function getWpVersion(Request $request): string|null
    {
        $version = $request->route('version');
        if (version_compare($version, '1.2', '>=')) {
            return $request->query('wp_version');
        } elseif (preg_match('|WordPress/([^;]+)|', $request->server('HTTP_USER_AGENT'), $matches)) {
            // Get version from user agent since it's not explicitly sent to feature_list requests in older API branches.
            return $matches[1];
        }
        return null;
    }
}
