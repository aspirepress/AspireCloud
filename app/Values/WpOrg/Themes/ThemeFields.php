<?php

namespace App\Values\WpOrg\Themes;

use Illuminate\Http\Request;

trait ThemeFields
{
    public const allFields = [
        'description' => false,
        'downloaded' => false,
        'download_link' => false,
        'last_updated_time' => false,
        'creation_time' => false,
        'parent' => false,
        'rating' => false,
        'ratings' => false,
        'reviews_url' => false,
        'screenshot_count' => false,
        'screenshot_url' => true,
        'screenshots' => false,
        'sections' => false,
        'tags' => false,
        'template' => false,
        'versions' => false,
        'theme_url' => false,
        'homepage' => false,
        'extended_author' => false,
        'photon_screenshots' => false,
        'active_installs' => false,
        'requires' => false,
        'requires_php' => false,
        'trac_tickets' => false,
        'is_commercial' => false,
        'is_community' => false,
        'external_repository_url' => false,
        'external_support_url' => false,
        'upload_date' => false,
    ];

    /**
     * Get the fields to be returned in the response.
     *
     * @param array<string,bool> $defaultFields
     * @return array<string,bool>
     */
    public static function getFields(Request $request, array $defaultFields = []): array
    {
        if (version_compare($request->route('version'), '1.2', '>=')) {
            $defaultFields['extended_author'] = true;
            $defaultFields['external_repository_url'] = true;
            $defaultFields['external_support_url'] = true;
            $defaultFields['is_commercial'] = true;
            $defaultFields['is_community'] = true;
            $defaultFields['num_ratings'] = true;
            $defaultFields['parent'] = true;
            $defaultFields['requires'] = true;
            $defaultFields['requires_php'] = true;

            // These aren't actually served in .org protocol version 1.2, but it's harmless to add them
            // Eventually we'll just serve everything but sections and description by default.
            $defaultFields['creation_time'] = true;
            $defaultFields['download_link'] = true;
            $defaultFields['last_updated_time'] = true;
            $defaultFields['rating'] = true;
            $defaultFields['ratings'] = true;
            $defaultFields['upload_date'] = true;
        }
        $specifiedFields = $request->query('fields');
        if ($specifiedFields == null) {
            return array_merge(self::allFields, $defaultFields);
        }

        if (!is_array($specifiedFields)) {
            $specifiedFields = explode(',', $specifiedFields);
        }

        // Indexed array: eg: [ 'field1', 'field2' ]
        if (array_keys($specifiedFields) === range(0, count($specifiedFields) - 1)) {
            // Convert [ 'field1', 'field2' ] => [ 'field1' => true, 'field2' => true ]
            $specifiedFields = array_combine($specifiedFields, array_fill(0, count($specifiedFields), true));
        } else {
            // [ 'field1' => 1, 'field2' => 'false'] => [ 'field1' => true, 'field2' => false ]
            $specifiedFields = array_map(function ($value) {
                if (is_string($value)) {
                    $value = strtolower($value); // Make the string case-insensitive
                    if ($value === '1' || $value === 'true') {
                        return true;
                    } elseif ($value === '0' || $value === 'false') {
                        return false;
                    }
                }
                return $value;
            }, $specifiedFields);
        }

        return array_merge(self::allFields, $specifiedFields, $defaultFields);
    }
}
