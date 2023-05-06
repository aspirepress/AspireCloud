# Skeleton App

This skeleton application is a Mezzio skeleton, designed for rapid deployment and testing of a Mezzio application. It
does NOT ship with a large number of libraries; rather, it allows you to create your own application using the libraries
you desire.

## Setup

1. Run `docker compose build` to build the Docker files.
2. Run `docker compose run --rm webapp composer install` to install the Composer dependencies in `\vendor`
3. Run `make assets` to build the Tailwind CSS assets.
4. (Optional) Add application.local to your `/etc/hosts` file
5. Visit https://application.local (or localhost if you did not do step #4).
6. See the "Hello World!" underlined and bold.
7. Replace with your own application.

## Notes

Presently, nginx is configured to use application.local as the URL. You can change this by replacing the `application`
name with a name of your choosing in the `docker/nginx/default` and `docker/nginx/Dockerfile` files.