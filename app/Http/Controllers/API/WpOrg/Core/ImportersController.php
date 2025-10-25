<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\WpOrg\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ImportersController extends Controller
{
    public function __invoke(string $version): JsonResponse|Response
    {
        $response = [
            "importers" => [
                "blogger" => [
                    "name" => "Blogger",
                    "description" => "Install the Blogger importer to import posts, comments, and users from a Blogger blog.",
                    "plugin-slug" => "blogger-importer",
                    "importer-id" => "blogger",
                ],
                "wpcat2tag" => [
                    "name" => "Categories and Tags Converter",
                    "description" => "Install the category/tag converter to convert existing categories to tags or tags to categories, selectively.",
                    "plugin-slug" => "wpcat2tag-importer",
                    "importer-id" => "wpcat2tag",
                ],
                "livejournal" => [
                    "name" => "LiveJournal",
                    "description" => "Install the LiveJournal importer to import posts from LiveJournal using their API.",
                    "plugin-slug" => "livejournal-importer",
                    "importer-id" => "livejournal",
                ],
                "movabletype" => [
                    "name" => "Movable Type and TypePad",
                    "description" => "Install the Movable Type importer to import posts and comments from a Movable Type or TypePad blog.",
                    "plugin-slug" => "movabletype-importer",
                    "importer-id" => "mt",
                ],
                "opml" => [
                    "name" => "Blogroll",
                    "description" => "Install the blogroll importer to import links in OPML format.",
                    "plugin-slug" => "opml-importer",
                    "importer-id" => "opml",
                ],
                "rss" => [
                    "name" => "RSS",
                    "description" => "Install the RSS importer to import posts from an RSS feed.",
                    "plugin-slug" => "rss-importer",
                    "importer-id" => "rss",
                ],
                "tumblr" => [
                    "name" => "Tumblr",
                    "description" => "Install the Tumblr importer to import posts &amp; media from Tumblr using their API.",
                    "plugin-slug" => "tumblr-importer",
                    "importer-id" => "tumblr",
                ],
                "wordpress" => [
                    "name" => "WordPress",
                    "description" => "Install the WordPress importer to import posts, pages, comments, custom fields, categories, and tags from a WordPress export file.",
                    "plugin-slug" => "wordpress-importer",
                    "importer-id" => "wordpress",
                ],
            ],
            "translated" => false,
        ];
        return $version === '1.0' ? new Response(serialize((object) $response)) : response()->json($response);
    }
}
