# AspirePress CDN

This project is designed to act as a CDN/API endpoint system for distributing WP assets (themes, plugins, core) to users
of the [AspirePress Updater](https://github.com/aspirepress/updater-plugin). It is fully free software that can be
distributed under the condition that it not be used for commercial gain when distriuting freely available products.

## Setup

1. Run `make init` to initialize the project.
2. Configure WordPress to use your local version of the CDN.
3. You should now be online!

## XDebug Instructions for PHPStorm

1. Go to **Settings > PHP > Debug** and check "Break at first line of PHP scripts".
2. Go to **Settings > PHP > Servers** and create a server for your desired hostname.
3. Edit the `docker-compose.override.yml.dist` file so that the server name matches the one you entered in Step #2.
4. Copy the `docker-compose.override.yml.dist` file to `docker-compose.override.yml` for inclusion by Docker. Run `make down up` to restart Docker.
4. Go to **Run > Edit Configurations**. Add a PHP Remote Debug configuration. Select your server and enter the PHPSTORM IDE key.
5. Click the debug icon to start debug listening.
6. Refresh the page. It should break on the first line. If not, repeat the steps and use `xdebug_info()` to verify what XDebug is doing.
7. Remove the "Break at first line..." setting from #1 to allow the program to progress until breakpoints are set.

## Notes

AspirePress CDN operates as an API and a pseudo pull-through cache against WordPress.org. This means that if AspirePress
CDN implements the endpoint you're looking for, it attempts to deliver the requested resource; otherwise, it passes the
request through to WordPress.org and returns that response to the end user.

The goal is to slowly, over time, implement the WordPress.org APIs so that we can reduce reliance on the org website
and endpoints.

Please do not use this project to harass the .org website. We don't want our project banned from the .org!

## License

The license for this project is the [MIT License](https://opensource.org/license/mit). You may exercise all rights under
the MIT license, including but not limited to using this project for commercial purposes.

## Trademarks

AspirePress is a trademark of Tailwinds, LLC, and we reserve our rights to this trademark. You may use the trademark to
clearly describe what you are offering (e.g. "we host an AspirePress mirror") but you may NOT use the AspirePress trademark
in a way that implies endorsement of or official affiliation with the AsiprePress project (e.g. "we're an official
AspirePress partner" or "we are AspirePress"). For disambiguation or questions, reach out to support@aspirepress.org.
Permission to use the trademark for legitimate purposes will not be unreasonably withheld.

Note that if you earn money from your implementation of this code, you must only use the trademark in a way that implies
you are "powered by AspirePress". For example, a hosting company called FooHosting would say "FooHosting CDN - Powered
by AspirePress".

If you use the trademark, you MUST link to AspirePress.org in a prominent location, and include the following text:

"AspirePress is a trademark of Tailwinds LLC. All rights reserved. Used under fair use and in compliance with the
trademark policy."

## Contributing

If you contribute to this project, you grant an exclusive,royalty-free, global, irrevocable license to AspirePress and any members
of AspirePress to use, relicense, redistribute, copy, modify, change or otherwise utiize any source code you contribute.
Furthermore, contribution of source code is not a guarantee that you will be granted rights to use the project for your
own purposes. By contributing you acknowledge that the code you contribute becomes licensed by AspirePress, that you
may not revoke that license, and you may not use that code again without written permission from AspirePress.

You also agree to indemnify and hold harmless AspirePress and any members of AspirePress from any harm, including
litigation or copyright enforcement, for code you commit that you do not own. **DO NOT COMMIT CODE YOU DO NOT OWN OR THAT
IS COPYRIGHTED BY SOMEONE ELSE!**

AspirePress permits all open-source licenses to be used, except for CopyLeft licenses (GPL, LGPL, etc.) You may contribute
open source code that does not implement these licesnes.