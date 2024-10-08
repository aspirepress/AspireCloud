# AspireCloud

This project is designed to function as a CDN/API endpoint system for distributing WordPress assets (themes, plugins, core) to users of the [AspirePress Updater](https://github.com/aspirepress/updater-plugin). It is free software, with the condition that it must not be used for commercial gain when distributing freely available products.

## Setup

1. Run `make init` to initialize the project.
2. Configure WordPress to use your local version of AspireCloud.
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

AspireCloud operates as an API and a pseudo pull-through cache against WordPress.org. This means that if AspireCloud provides the requested endpoint, it attempts to deliver the resource; otherwise, it passes the request through to WordPress.org and returns their response to the end user.

The long-term goal is to gradually implement WordPress.org APIs to reduce reliance on their website and endpoints.

**Important**: Please do not use this project to flood or harass the WordPress.org website. We don't want to get banned from using their resources!

## License

This project is licensed under the [MIT License](https://opensource.org/license/mit). You may exercise all rights granted by the MIT license, including using this project for commercial purposes.
