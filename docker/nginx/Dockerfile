FROM nginx:latest

ENV URL='application.local'

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log

RUN apt-get update && apt install -y wget libnss3-tools \
        && wget -O mkcert \
        https://github.com/FiloSottile/mkcert/releases/download/v1.4.3/mkcert-v1.4.3-linux-amd64 \
        && chmod +x mkcert \
        && mv mkcert /usr/local/bin \
        && mkcert -install \
        && mkcert -cert-file application -key-file application-key *.${URL}
