# Docker Network Proxy

## AspirePress Users
The steps below are of interest to anyone wanting to use the proxy with their own services. 
If you are building AspireCloud using the Makefile, all the below steps have already been performed, 
and you only need edit your `/etc/hosts` file as described in the project README.  

----

## How to proxy your own services 

The proxy depends on an external network being defined, so run the following in your shell (you only ever need to do this once)

    docker network create traefik

### Enabling the proxy in docker-compose.yml

Add the following to the service you want proxied, substituting `myservice` and `myhostname` appropriately (`myservice` can be anything you want, but it must be unique across all your docker containers)
    
    labels:
      - "traefik.enable=true"
      - "traefik.http.routers.myservice.rule=Host(`myhostname.local`)"
      - "traefik.http.routers.myservice-https.rule=Host(`myhostname.local`)"
      - "traefik.http.routers.myservice-https.tls=true"
    networks:
      - traefik
            
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

