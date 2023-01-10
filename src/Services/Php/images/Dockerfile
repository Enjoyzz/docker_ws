ARG PHP_IMAGE
FROM ${PHP_IMAGE}

# https://wiki.alpinelinux.org/wiki/Setting_the_timezone
ARG TZ='UTC'
RUN echo "${TZ}" && apk --update add tzdata && \
    cp /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone && \
    apk del tzdata

# Copy xdebug configuration for remote debugging
COPY php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Добавим свой php.ini, можем в нем определять свои значения конфига
COPY php/php.ini /usr/local/etc/php/conf.d/40-custom.ini
COPY php/sendmail /usr/local/bin

ARG USER="user"
ARG UID="1000"
RUN echo ${USER}:${UID}
RUN adduser --system ${USER} --uid ${UID}

ARG WORK_DIR="/var/www"
WORKDIR ${WORK_DIR}

USER ${USER}

ENV ENV=/etc/profile
COPY php/alias.sh /etc/profile.d/alias.sh