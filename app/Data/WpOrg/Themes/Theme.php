<?php

namespace App\Data\WpOrg\Themes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class Theme extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $version,
        public readonly string $preview_url,
        public readonly string $author,
        public readonly string $screenshot_url,
        public readonly int $rating,    // appears to be between 0-100
        public readonly int $num_ratings,    // appears to be between 0-100
        public readonly int $homepage,
        public readonly string $description,
        public readonly string|Optional $template,
    ) {}
}

/*
 * https://api.wordpress.org/themes/info/1.1/?action=query_themes&request[tag]=sticky-post&request[tag]=two-columns&request[search]=gutenberg
{
"info": { "page": 1, "pages": 55, "results": 659 },
"themes": [
    {
        "name": "Blogdash",
        "slug": "blogdash",
        "version": "1.0.0",
        "preview_url": "https://wp-themes.com/blogdash/",
        "author": "peregrinethemes",
        "screenshot_url": "//ts.w.org/wp-content/themes/blogdash/screenshot.jpg?ver=1.0.0",
        "rating": 100,
        "num_ratings": 1,
        "homepage": "https://wordpress.org/themes/blogdash/",
        "description": "Blogdash is the perfect pick for bloggers seeking a lightweight, customizable theme that suits them just right. With plenty of options to adjust colors and typography, making your site unique is a breeze. It’s SEO friendly and fully compatible with WPML, Gutenberg, Elementor, WooCommerce, and supports translation and RTL. Live preview: https://demo.peregrine-themes.com/bloghash/blogdash.",
        "template": "bloghash"
    },

... what we are currently sending back:

{
  "info": { "page": 1, "pages": 131, "results": 13047 },
  "themes": [
    {
      "name": "100 Bytes",
      "slug": "100-bytes",
      "tags": {
        "blog": "Blog",
        "one-column": "One column",
        "full-width-template": "Full width template"
      },
      "author": {
        "author": "Marc Armengou",
        "avatar": "https://secure.gravatar.com/avatar/76e5967738212577d98ad75204656d48?s=96&d=monsterid&r=g",
        "profile": "https://profiles.wordpress.org/marc4/",
        "author_url": "https://www.marcarmengou.com/",
        "display_name": "Marc Armengou",
        "user_nicename": "marc4"
      },
      "rating": 0,
      "version": "1.1.3",
      "homepage": "https://wordpress.org/themes/100-bytes/",
      "requires": false,
      "sections": {
        "description": "100 Bytes is a theme that aims to look as optimal as possible to deliver your message to your audience using WordPress as a content manager. The idea is simple, make a theme that looks good everywhere, with as little CSS code as possible. In this case the limit is 100 Bytes of CSS information. Actually the compressed CSS code contains 82 bytes of information, but 100 bytes sounds better."
      },
      "versions": {
        "1.0": "https://downloads.wordpress.org/theme/100-bytes.1.0.zip",
        "1.0.1": "https://downloads.wordpress.org/theme/100-bytes.1.0.1.zip",
        "1.0.2": "https://downloads.wordpress.org/theme/100-bytes.1.0.2.zip",
        "1.0.3": "https://downloads.wordpress.org/theme/100-bytes.1.0.3.zip",
        "1.0.4": "https://downloads.wordpress.org/theme/100-bytes.1.0.4.zip",
        "1.0.5": "https://downloads.wordpress.org/theme/100-bytes.1.0.5.zip",
        "1.0.6": "https://downloads.wordpress.org/theme/100-bytes.1.0.6.zip",
        "1.0.7": "https://downloads.wordpress.org/theme/100-bytes.1.0.7.zip",
        "1.0.8": "https://downloads.wordpress.org/theme/100-bytes.1.0.8.zip",
        "1.0.9": "https://downloads.wordpress.org/theme/100-bytes.1.0.9.zip",
        "1.1.0": "https://downloads.wordpress.org/theme/100-bytes.1.1.0.zip",
        "1.1.1": "https://downloads.wordpress.org/theme/100-bytes.1.1.1.zip",
        "1.1.2": "https://downloads.wordpress.org/theme/100-bytes.1.1.2.zip",
        "1.1.3": "https://downloads.wordpress.org/theme/100-bytes.1.1.3.zip"
      },
      "downloaded": 1430,
      "num_ratings": 0,
      "preview_url": "https://wp-themes.com/100-bytes/",
      "reviews_url": "https://wordpress.org/support/theme/100-bytes/reviews/",
      "is_community": false,
      "last_updated": "2024-01-13",
      "requires_php": "5.6",
      "creation_time": "2023-05-31 03:11:40",
      "download_link": "https://downloads.wordpress.org/theme/100-bytes.1.1.3.zip",
      "is_commercial": false,
      "screenshot_url": "//ts.w.org/wp-content/themes/100-bytes/screenshot.png?ver=1.1.3",
      "aspirepress_meta": {
        "seen": "2024-10-21T18:38:36+00:00",
        "added": "2024-10-21T18:38:36+00:00",
        "updated": "2024-10-21T18:38:36+00:00",
        "finalized": null,
        "processed": null
      },
      "last_updated_time": "2024-01-13 15:57:28",
      "external_support_url": false,
      "external_repository_url": ""
    },

 */
