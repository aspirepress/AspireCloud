<?php

namespace App\Controller\API\WpOrg\Themes;

use App\Controller\BaseController;
use App\Data\WpOrg\PageInfo;
use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Data\WpOrg\Themes\QueryThemesResponse;
use App\Data\WpOrg\Themes\ThemeInformationRequest;
use App\Repository\SyncThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class ThemesController extends BaseController {

    public function __construct(private SyncThemeRepository $repository, private EntityManagerInterface $em) {}


    #[Route('/themes/info/{version}')]
    public function info(
        Request $request,
        string $version,
        #[MapQueryParameter] string $action
    ): Response
    {
        $response = match ($action) {
            'query_themes' => $this->doQueryThemes(QueryThemesRequest::fromRequest($request)),
            'theme_information' => $this->doThemeInformation(ThemeInformationRequest::fromRequest($request)),
            'hot_tags' => $this->doHotTags(),
            'feature_list' => $this->doFeatureList(),
            default => $this->unknownAction()
        };
        return $this->sendResponse($response, $version);
    }

    private function doQueryThemes(QueryThemesRequest $req): QueryThemesResponse
    {
        $page = $req->page;
        $perPage = $req->per_page;
        $skip = ($page - 1) * $perPage;

        $themes = []; // TODO
        $total = $this->repository->count();
        $dql = <<<'DQL'
            SELECT t FROM App\Entity\SyncTheme t
        DQL;

        $themes = $this->em->createQuery($dql)
            ->setFirstResult($skip)
            ->setMaxResults($perPage)
            ->execute();

        dd($themes);

        // $themes = DB::table('themes')
        //     ->skip($skip)
        //     ->take($perPage)
        //     ->get()
        //     ->map(fn($theme) => json_decode($theme->metadata))
        //     ->toArray();
        // $total = DB::table('themes')->count();

        $pageInfo = new PageInfo(page: $page, pages: (int) ceil($total / $perPage), results: $total);
        return new QueryThemesResponse($pageInfo, $themes);
    }

    /** @return array<string, mixed> */
    private function doThemeInformation(ThemeInformationRequest $req): array
    {
        return ['req' => $req];
    }

    /** @return array<string, mixed> */
    private function doHotTags(): array
    {
        return ['error' => 'not implemented'];
    }

    /** @return array<string, mixed> */
    private function doFeatureList(): array
    {
        return ['error' => 'not implemented'];
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
     * @param array|QueryThemesResponse $response
     * @param string $version
     * @param int $statusCode
     * @return Response
     */
    private function sendResponse(array|QueryThemesResponse $response, string $version, int $statusCode = 200): Response
    {
        if ($version === '1.0') {
            if (is_object($response) && method_exists($response, 'toStdClass')) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $response = $response->toStdClass();
            }
            return new Response(serialize((object)$response), $statusCode);
        }
        return $this->json($response, $statusCode);
    }
}

