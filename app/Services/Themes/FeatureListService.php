<?php

namespace App\Services\Themes;

class FeatureListService
{
    /**
     * @return array<string, array<string,string>>
     * @mago-expect analysis:invalid-array-element-key (Laravel's __ function is hopeless)
     */
    public function getFeatureList(?string $wpVersion = null): array
    {
        $tags = [
            __('Colors') => [
                'black' => __('Black'),
                'blue' => __('Blue'),
                'brown' => __('Brown'),
                'gray' => __('Gray'),
                'green' => __('Green'),
                'orange' => __('Orange'),
                'pink' => __('Pink'),
                'purple' => __('Purple'),
                'red' => __('Red'),
                'silver' => __('Silver'),
                'tan' => __('Tan'),
                'white' => __('White'),
                'yellow' => __('Yellow'),
                'dark' => __('Dark'),
                'light' => __('Light'),
            ],
            __('Columns') => [
                'one-column' => __('One Column'),
                'two-columns' => __('Two Columns'),
                'three-columns' => __('Three Columns'),
                'four-columns' => __('Four Columns'),
                'left-sidebar' => __('Left Sidebar'),
                'right-sidebar' => __('Right Sidebar'),
            ],
            __('Layout') => [
                'fixed-layout' => __('Fixed Layout'),
                'fluid-layout' => __('Fluid Layout'),
                'responsive-layout' => __('Responsive Layout'),
            ],
            __('Features') => [
                'accessibility-ready' => __('Accessibility Ready'),
                'blavatar' => __('Blavatar'),
                'buddypress' => __('BuddyPress'),
                'custom-background' => __('Custom Background'),
                'custom-colors' => __('Custom Colors'),
                'custom-header' => __('Custom Header'),
                'custom-menu' => __('Custom Menu'),
                'editor-style' => __('Editor Style'),
                'featured-image-header' => __('Featured Image Header'),
                'featured-images' => __('Featured Images'),
                'flexible-header' => __('Flexible Header'),
                'front-page-post-form' => __('Front Page Posting'),
                'full-width-template' => __('Full Width Template'),
                'microformats' => __('Microformats'),
                'post-formats' => __('Post Formats'),
                'rtl-language-support' => __('RTL Language Support'),
                'sticky-post' => __('Sticky Post'),
                'theme-options' => __('Theme Options'),
                'threaded-comments' => __('Threaded Comments'),
                'translation-ready' => __('Translation Ready'),
            ],
            __('Subject') => [
                'holiday' => __('Holiday'),
                'photoblogging' => __('Photoblogging'),
                'seasonal' => __('Seasonal'),
            ],
        ];

        // Pre 3.8 installs get width tags instead of layout tags.
        if (isset($wpVersion) && version_compare($wpVersion, '3.7.999', '<')) {
            unset($tags[__('Layout')]);
            $tags[__('Width')] = [
                'fixed-width' => __('Fixed Width'),
                'flexible-width' => __('Flexible Width'),
            ];

            if (array_key_exists('accessibility-ready', $tags[__('Features')])) {
                unset($tags[__('Features')]['accessibility-ready']);
            }
        }

        if (!isset($wpVersion) || version_compare($wpVersion, '3.9-beta', '>')) {
            $tags[__('Layout')] = array_merge($tags[__('Layout')], $tags[__('Columns')]);
            unset($tags[__('Columns')]);
        }

        // See https://core.trac.wordpress.org/ticket/33407.
        if (!isset($wpVersion) || version_compare($wpVersion, '4.6-alpha', '>')) {
            unset($tags[__('Colors')]);
            $tags[__('Layout')] = [
                'grid-layout' => __('Grid Layout'),
                'one-column' => __('One Column'),
                'two-columns' => __('Two Columns'),
                'three-columns' => __('Three Columns'),
                'four-columns' => __('Four Columns'),
                'left-sidebar' => __('Left Sidebar'),
                'right-sidebar' => __('Right Sidebar'),
            ];

            unset($tags[__('Features')]['blavatar']);
            $tags[__('Features')]['footer-widgets'] = __('Footer Widgets');
            $tags[__('Features')]['custom-logo'] = __('Custom Logo');
            asort($tags[__('Features')]); // To move footer-widgets to the right place.

            $tags[__('Subject')] = [
                'blog' => __('Blog'),
                'e-commerce' => __('E-Commerce'),
                'education' => __('Education'),
                'entertainment' => __('Entertainment'),
                'food-and-drink' => __('Food & Drink'),
                'holiday' => __('Holiday'),
                'news' => __('News'),
                'photography' => __('Photography'),
                'portfolio' => __('Portfolio'),
            ];
        }

        // See https://core.trac.wordpress.org/ticket/46272.
        if (!isset($wpVersion) || version_compare($wpVersion, '5.2-alpha', '>=')) {
            $tags[__('Layout')]['wide-blocks'] = __('Wide Blocks');
            $tags[__('Features')]['block-styles'] = __('Block Editor Styles');
            asort($tags[__('Features')]); // To move block-styles to the right place.
        }

        // See https://core.trac.wordpress.org/ticket/50164.
        if (!isset($wpVersion) || version_compare($wpVersion, '5.5-alpha', '>=')) {
            $tags[__('Features')]['block-patterns'] = __('Block Editor Patterns');
            $tags[__('Features')]['full-site-editing'] = __('Full Site Editing');
            asort($tags[__('Features')]);
        }

        // See https://core.trac.wordpress.org/ticket/53556.
        if (!isset($wpVersion) || version_compare($wpVersion, '5.8.1-alpha', '>=')) {
            $tags[__('Features')]['template-editing'] = __('Template Editing');
            asort($tags[__('Features')]);
        }

        // See https://core.trac.wordpress.org/ticket/56869.
        if (!isset($wpVersion) || version_compare($wpVersion, '6.0-alpha', '>=')) {
            $tags[__('Features')]['style-variations'] = __('Style Variations');
            asort($tags[__('Features')]);
        }

        // Only return tag slugs, to stay compatible with bbpress-version of Themes API.
        foreach ($tags as $title => $group) {
            $tags[$title] = array_keys($group);
        }

        return $tags;
    }
}
