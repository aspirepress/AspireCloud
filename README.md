# AspirePress CDN

This project is designed to function as a CDN/API endpoint system for distributing WordPress assets (themes, plugins, core) to users of the [AspirePress Updater](https://github.com/aspirepress/updater-plugin). It is free software, with the condition that it must not be used for commercial gain when distributing freely available products.

## Setup

1. Run `make init` to initialize the project.
2. Configure WordPress to use your local version of the CDN.
3. You should now be online!

## XDebug Instructions for PHPStorm

1. Go to **Settings > PHP > Debug** and check "Break at first line of PHP scripts."
2. Go to **Settings > PHP > Servers** and create a server with your desired hostname.
3. Edit the `docker-compose.override.yml.dist` file so that the server name matches the one you entered in Step #2.
4. Copy `docker-compose.override.yml.dist` to `docker-compose.override.yml` to include it in Docker. Then run `make down up` to restart Docker.
5. Go to **Run > Edit Configurations**. Add a PHP Remote Debug configuration. Select your server and enter the PHPSTORM IDE key.
6. Click the debug icon to start listening for debug connections.
7. Refresh the page. It should stop at the first line of execution. If not, repeat the steps and use `xdebug_info()` to verify XDebug’s activity.
8. Once debugging works, remove the "Break at first line..." setting from Step #1 to allow the program to progress until it hits breakpoints.

## Notes

AspirePress CDN operates as an API and a pseudo pull-through cache against WordPress.org. This means that if AspirePress CDN provides the requested endpoint, it attempts to deliver the resource; otherwise, it passes the request through to WordPress.org and returns their response to the end user.

The long-term goal is to gradually implement WordPress.org APIs to reduce reliance on their website and endpoints.

**Important**: Please do not use this project to flood or harass the WordPress.org website. We don't want to get banned from using their resources!

## License

This project is licensed under the [MIT License](https://opensource.org/license/mit). You may exercise all rights granted by the MIT license, including using this project for commercial purposes.

## Trademarks

AspirePress is a trademark of Tailwinds, LLC. We reserve all rights to this trademark. You may use it to accurately describe what you are offering (e.g., "we host an AspirePress mirror"), but **you may NOT use the AspirePress trademark** in any way that implies endorsement or official affiliation with AspirePress (e.g., "we're an official AspirePress partner" or "we are AspirePress").

For clarification or questions, reach out to support@aspirepress.org. Permission to use the trademark for legitimate purposes will not be unreasonably withheld.

If you earn money from your use of this code, you may only use the trademark to imply that your service is "powered by AspirePress." For example, a hosting company called FooHosting might say, "FooHosting CDN - Powered by AspirePress."

If you use the trademark, you **must** link to AspirePress.org in a prominent location and include the following text:

> "AspirePress is a trademark of Tailwinds LLC. All rights reserved. Used under fair use and in compliance with the trademark policy."

## Contributing

By contributing to this project, you grant AspirePress and its members an exclusive, royalty-free, global, irrevocable license to use, relicense, redistribute, modify, and otherwise utilize any source code you contribute. Contribution does not guarantee that you will be granted rights to use the project for your own purposes. By contributing, you acknowledge that AspirePress owns the license to your contributed code, and you may not revoke it or reuse the code without written permission from AspirePress.

You also agree to indemnify and hold AspirePress and its members harmless from any harm, including litigation or copyright enforcement, that arises from code you commit that you do not own. **Do not commit code you do not own or that is copyrighted by someone else!**

AspirePress permits the use of any open-source license, except for CopyLeft licenses (e.g., GPL, LGPL). You may contribute open-source code that complies with this condition.