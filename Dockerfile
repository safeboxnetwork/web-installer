FROM alpine

ENV NGINX_VERSION=1.29.4
ARG PHP_VERSION="83"



RUN \
  build_pkgs="build-base linux-headers openssl-dev pcre-dev wget zlib-dev" && \
  runtime_pkgs="ca-certificates openssl pcre zlib tzdata " && \
  apk --no-cache add ${build_pkgs} ${runtime_pkgs} && \
  cd /tmp && \
  wget https://nginx.org/download/nginx-${NGINX_VERSION}.tar.gz && \
  tar xzf nginx-${NGINX_VERSION}.tar.gz && \
  cd /tmp/nginx-${NGINX_VERSION} && \
  ./configure \
  --prefix=/etc/nginx \
  --sbin-path=/usr/sbin/nginx \
  --conf-path=/etc/nginx/nginx.conf \
  --error-log-path=/var/log/nginx/error.log \
  --http-log-path=/var/log/nginx/access.log \
  --pid-path=/var/run/nginx.pid \
  --lock-path=/var/run/nginx.lock \
  --http-client-body-temp-path=/var/cache/nginx/client_temp \
  --http-proxy-temp-path=/var/cache/nginx/proxy_temp \
  --http-fastcgi-temp-path=/var/cache/nginx/fastcgi_temp \
  --http-uwsgi-temp-path=/var/cache/nginx/uwsgi_temp \
  --http-scgi-temp-path=/var/cache/nginx/scgi_temp \
  --user=nginx \
  --group=nginx \
  --with-cc-opt="-Wno-stringop-truncation -Wno-stringop-overflow -Wno-unterminated-string-initialization" \
  --with-http_ssl_module \
  --with-http_realip_module \
  --with-http_addition_module \
  --with-http_sub_module \
  --with-http_flv_module \
  --with-http_gunzip_module \
  --with-http_gzip_static_module \
  --with-http_random_index_module \
  --with-http_secure_link_module \
  --with-http_stub_status_module \
  --with-http_auth_request_module \
  --with-mail \
  --with-file-aio \
  --with-threads \
  --with-stream_realip_module \
  --with-http_slice_module \
  --with-http_v2_module && \
  make && \
  make install && \
  sed -i -e 's/#access_log  logs\/access.log  main;/access_log \/dev\/stdout;/' -e 's/#error_log  logs\/error.log  notice;/error_log stderr notice;/' /etc/nginx/nginx.conf && \
  addgroup -S nginx && \
  adduser -D -S -h /var/cache/nginx -s /sbin/nologin -G nginx nginx && \
  rm -rf /tmp/* && \
  apk del ${build_pkgs} && \
  rm -rf /var/cache/apk/*

RUN apk --no-cache add php${PHP_VERSION} \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-pecl-redis \
    curl \
    vim \
    git && \
    rm -rf /var/cache/apk/*

COPY nginx.conf /etc/nginx/nginx.conf
RUN mkdir -p /usr/share/nginx/html
COPY *.html  /usr/share/nginx/html
COPY *.php   /usr/share/nginx/html
COPY *.css   /usr/share/nginx/html
COPY *.js    /usr/share/nginx/html
COPY img     /usr/share/nginx/html/img

RUN \
    chown -R nginx:nginx /usr/share/nginx/html && \
    mkdir -p /var/log/nginx && \
    touch /var/log/nginx/access.log && \
    touch /var/log/nginx/error.log && \
    chown -R nginx:nginx /var/log/nginx

RUN sed -i 's,listen       80;,listen       8080;,' /etc/nginx/nginx.conf \
    && sed -i '/user  nginx;/d' /etc/nginx/nginx.conf \
    && sed -i 's,^.*pid,pid /tmp/nginx.pid,' /etc/nginx/nginx.conf \
    && sed -i "/^http {/a \    proxy_temp_path /tmp/proxy_temp;\n    client_body_temp_path /tmp/client_temp;\n    fastcgi_temp_path /tmp/fastcgi_temp;\n    uwsgi_temp_path /tmp/uwsgi_temp;\n    scgi_temp_path /tmp/scgi_temp;\n" /etc/nginx/nginx.conf \
    && sed -i 's#"\$http_x_forwarded_for"#\$http_x_forwarded_for#g' /etc/nginx/nginx.conf

RUN \
    mkdir -p /usr/share/nginx/html/ && \
    chown -R nginx:nginx /usr/share/nginx/html/ && \
    && chown -R :nginx /var/cache/nginx \
    && chmod -R g+w /var/cache/nginx \
    && chown -R :nginx /etc/nginx \
    && chmod -R g+w /etc/nginx \
    && chown -R :nginx /usr/share/nginx \
    && chmod -R g+w /usr/share/nginx \
    && chmod -R o-rwx /usr/share/nginx && \
    mkdir -p /var/log/php83 && \
    chown -R nginx: /var/log/php83

VOLUME ["/var/cache/nginx"]

EXPOSE 8080

USER nginx

CMD php-fpm83 & nginx
