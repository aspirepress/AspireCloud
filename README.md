# Skeleton App

This skeleton application is a Mezzio skeleton, designed for rapid deployment and testing of a Mezzio application. It
does NOT ship with a large number of libraries; rather, it allows you to create your own application using the libraries
you desire.

## Setup

1. Run `docker compose build` to build the Docker files.
2. Run `docker compose run --rm webapp composer install` to install the Composer dependencies in `\vendor`
3. Run `make install-node` to install the Node components.
4. Run `make assets` to build the Tailwind CSS assets.
5. (Optional) Add application.local to your `/etc/hosts` file
6. Run `docker compose up -d` to start the Docker containers.
7. Visit https://application.local (or localhost if you did not do step #4).
8. See the "Hello World!" underlined and bold. 
9. Replace with your own application.

## XDebug Instructions for PHPStorm

1. Go to **Settings > PHP > Debug** and check "Break at first line of PHP scripts".
2. Go to **Settings > PHP > Servers** and create a server for your desired hostname.
3. Edit the `docker-compose.yml` file so that the server name matches the one you entered in Step #2.
4. Go to **Run > Edit Configurations**. Add a PHP Remote Debug configuration. Select your server and enter the PHPSTORM IDE key.
5. Click the debug icon to start debug listening.
6. Refresh the page. It should break on the first line. If not, repeat the steps and use `xdebug_info()` to verify what XDebug is doing.
7. Remove the "Break at first line..." setting from #1 to allow the program to progress until breakpoints are set.

## Notes

Presently, nginx is configured to use application.local as the URL. You can change this by replacing the `application`
name with a name of your choosing in the `docker/nginx/default` and `docker/nginx/Dockerfile` files.