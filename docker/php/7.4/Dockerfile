FROM enjoys/php:7.4-fpm-alpine

# https://wiki.alpinelinux.org/wiki/Setting_the_timezone
ARG TZ='UTC'
RUN echo "${TZ}" && apk --update add tzdata && \
    cp /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone && \
    apk del tzdata

# Copy xdebug configuration for remote debugging
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Добавим свой php.ini, можем в нем определять свои значения конфига
COPY php.ini /usr/local/etc/php/conf.d/40-custom.ini
COPY sendmail /usr/local/bin

ARG USER_NAME="user"
ARG USER_ID="1000"
RUN echo ${USER_NAME}:${USER_ID}
RUN adduser --system ${USER_NAME} --uid ${USER_ID}

ARG WORK_DIR="/var/www"
WORKDIR ${WORK_DIR}

USER ${USER_NAME}

ENV ENV=/etc/profile
COPY alias.sh /etc/profile.d/alias.sh