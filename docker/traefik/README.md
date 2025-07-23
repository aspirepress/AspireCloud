# Docker Network Proxy

## AspirePress Users

The steps below are of interest to anyone wanting to use the proxy with their own services.
If you are building AspireCloud using the Makefile, all the below steps have already been performed,
and you only need edit your `/etc/hosts` file as described in the project README.

----

## How to proxy your own services

The proxy depends on an external network being defined, so run the following in your shell (you only ever need to do
this once)

    docker network create traefik

### Enabling the proxy in docker-compose.yml

Add the following to the service you want proxied, substituting `myservice` and `myhostname` appropriately (`myservice`
can be anything you want, but it must be unique across all your docker containers)

    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.myservice.rule=Host(`myhostname.local`)"
      - "traefik.http.routers.myservice-https.rule=Host(`myhostname.local`)"
      - "traefik.http.routers.myservice-https.tls=true"
    networks:
      - traefik

### Local development on MacOS
Edit the docker-compose.yaml file located at the root of the project and add the following to the service you want proxied, substituting `myservice` and `myhostname` appropriately (`myservice`
can be anything you want, but it must be unique across all your docker containers)

    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.myservice.rule=Host(`myhostname`)"
      - "traefik.http.routers.myservice.entrypoints=web,web-secure"
      - "traefik.http.routers.myservice.tls=true"
      - "traefik.http.services.myservice.loadbalancer.server.port=80"
    networks:
      - traefik

In order to overwrite the default ports 80,443 and 8080 used by Traefik, you can add the following to the

copy `.env.example` file and rename it to `.env`:
edit the `.env` file and set the following variables:
```
TRAEFIK_HTTP_PORT=8088
TRAEFIK_HTTPS_PORT=8443
TRAEFIK_DASHBOARD_PORT=8090
TRAEFIK_CONFIG_FILE=./traefik.yaml
TRAEFIK_CONFIG_DIR=./traefik-config.d
TRAEFIK_CERTS_DIR=./certs
```

Add the following to the top level keys

    networks:
      traefik:
        external: true

### Add an entry to your hosts file

Add the following lines to your `/etc/hosts` file (`C:\Windows\System32\drivers\etc\hosts` on Windows)

```
127.0.0.1 myhostname.local
::1       myhostname.local
```

