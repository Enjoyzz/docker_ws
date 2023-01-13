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

ARG USER_ID
ARG GROUP_ID
ARG USER_NAME
ARG GROUP_NAME
ARG WORK_DIR='/var/www'

RUN addgroup -g ${GROUP_ID} ${GROUP_NAME} && \
    adduser -D -u ${USER_ID} -s /bin/bash ${USER_NAME} -G ${GROUP_NAME} && \
    mkdir -p ${WORK_DIR} && \
    chown ${USER_NAME}: ${WORK_DIR}

WORKDIR ${WORK_DIR}

USER ${APP_USER_NAME}

ENV ENV=/etc/profile
COPY php/alias.sh /etc/profile.d/alias.sh