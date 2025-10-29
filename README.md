[//]: # (@formatter:off)
# AspireCloud

This project is designed to function as a CDN/API endpoint system for distributing WordPress assets (themes, plugins, core) to users of the [AspirePress Updater](https://github.com/aspirepress/updater-plugin).

# 🪧 CloudFest Hackathon 2025: see [/docs/readme.hackathon.md](./docs/readme.hackathon.md)

## Setup

### Quick Start

```
make init
```

Next configure WordPress to use your local version of AspireCloud, and you're good to go! 

## Using https://api.aspiredev.org instead of localhost

The local dev instance can be reached this way by enabling a [Traefik](https://hub.docker.com/_/traefik) proxy server:

    make traefik-up

You will then be able to reach the instance at https://api.aspiredev.org

## Notes

AspireCloud operates as an API and a pseudo pull-through cache against WordPress.org. This means that if AspireCloud provides the requested endpoint, it attempts to deliver the resource; otherwise, it passes the request through to WordPress.org and returns their response to the end user.

The long-term goal is to gradually implement WordPress.org APIs to reduce reliance on their website and endpoints.

**Important**: Please do not use this project to flood or harass the WordPress.org website. We don't want to get banned from using their resources!

## License

This project is licensed under the [MIT License](https://opensource.org/license/mit). You may exercise all rights granted by the MIT license, including using this project for commercial purposes.
