# AspireCloud

This project is designed to function as a CDN/API endpoint system for distributing WordPress assets (themes, plugins, core) to users of the [AspirePress Updater](https://github.com/aspirepress/updater-plugin). It is free software, with the condition that it must not be used for commercial gain when distributing freely available products.

## Setup

### Quick Start

```
make init
```

Next configure WordPress to use your local version of AspireCloud, and you're good to go! 

Note: you'll have to add `api.aspiredev.org` to your `/etc/hosts` file to point to `127.0.0.1`.

### Importing Plugins and Themes from AspireSync

* Check out and build [AspireSync](https://github.com/aspirepress/AspireSync).  

* **In AspireSync:** 
  * `aspiresync meta:dump:plugins > /path/to/plugins.jsonl`
  * `aspiresync meta:dump:themes > /path/to/themes.jsonl`

* **In AspireCloud** 
  * `php artisan sync:load /path/to/plugins.jsonl`
  * `php artisan sync:load /path/to/themes.jsonl`


## XDebug Instructions for PHPStorm

1. Go to **Settings > PHP > Debug** and check "Break at first line of PHP scripts."
2. Go to **Settings > PHP > Servers** and create a server with your desired hostname.
3. Edit the `docker-compose.override.yml.dist` file so that the server name matches the one you entered in Step #2.
4. Copy `docker-compose.override.yml.dist` to `docker-compose.override.yml` to include it in Docker. Then run `make down up` to restart Docker.
5. Go to **Run > Edit Configurations**. Add a PHP Remote Debug configuration. Select your server and enter the PHPSTORM IDE key.
6. Click the debug icon to start listening for debug connections.
7. Refresh the page. It should stop at the first line of execution. If not, repeat the steps and use `xdebug_info()` to verify XDebug’s activity.
8. Once debugging works, remove the "Break at first line..." setting from Step #1 to allow the program to progress until it hits breakpoints.

## Using https://api.aspiredev.org instead of localhost

The local dev instance can be reached this way by enabling a [Traefik](https://hub.docker.com/_/traefik) proxy server:

    make traefik-up

Next, add an entry to your `/etc/hosts` file (`C:\Windows\System32\drivers\etc\hosts` on Windows).  

    127.0.0.1 api.aspiredev.org
    ::1       api.aspiredev.org

### Note about SSL/TLS (https:// urls)

Because the proxy generates self-signed certs, you will get security warnings the first time you access the container after it is rebuilt.
Any other access will also need to disable certificate validation.   

Also note that plain old http://api.aspiredev.org always works. 

## Notes

AspireCloud operates as an API and a pseudo pull-through cache against WordPress.org. This means that if AspireCloud provides the requested endpoint, it attempts to deliver the resource; otherwise, it passes the request through to WordPress.org and returns their response to the end user.

The long-term goal is to gradually implement WordPress.org APIs to reduce reliance on their website and endpoints.

**Important**: Please do not use this project to flood or harass the WordPress.org website. We don't want to get banned from using their resources!

## License

This project is licensed under the [MIT License](https://opensource.org/license/mit). You may exercise all rights granted by the MIT license, including using this project for commercial purposes.
