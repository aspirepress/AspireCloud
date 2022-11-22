FROM nginx:latest

RUN apt-get update && apt install -y wget libnss3-tools \
        && wget -O mkcert \
        https://github.com/FiloSottile/mkcert/releases/download/v1.4.3/mkcert-v1.4.3-linux-amd64 \
        && chmod +x mkcert \
        && mv mkcert /usr/local/bin \
        && mkcert -install \
        && mkcert *.application.local
