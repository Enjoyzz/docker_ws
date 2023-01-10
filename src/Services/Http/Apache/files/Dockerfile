FROM httpd:2.4.33-alpine
RUN apk update; \
    apk upgrade;

# Copy apache vhost file to proxy php requests to php-fpm container
COPY vhost.conf /usr/local/apache2/conf.d/vhost.conf

RUN { \
  echo 'Include conf.d/*.conf'; \
} >> /usr/local/apache2/conf/httpd.conf