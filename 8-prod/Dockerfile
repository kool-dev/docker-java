FROM amazoncorretto:8-alpine

ENV ASUSER='' \
    UID='' \
    VM_OPTIONS_XMX=256m \
    VM_OPTIONS_MAX_METASPACE_SIZE=128m \
    VM_OPTIONS_XMN=64m \
    VM_OPTIONS_SURVIVOR_RATIO=128 \
    CMS_TRIGGER_PERCENT=70 \
    JVM_FILE_ENCODING=UTF-8 \
    JVM_TTL=60 \
    JVM_USER_LANGUAGE='en' \
    JVM_USER_COUNTRY='US' \
    TZ='' \
    JAVA_OPTIONS='' \
    ENTRYPOINT='' \
    VM_OPTIONS_METASPACE_SIZE=128m \
    VM_OPTIONS_XMS=256m \
    JAR_FILE='/app/application.jar'

WORKDIR /app

RUN adduser -D -u 1337 kool \
    && echo $TZ > /etc/timezone \
    # build-deps
    && apk add --no-cache --virtual .build-deps curl libarchive-tools \
    # dockerize
    && curl -L https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-alpine-linux-amd64-v0.6.1.tar.gz | tar xz \
    && mv dockerize /usr/local/bin/dockerize \
    # deps
    && apk add --no-cache su-exec bash shadow tzdata \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*


COPY kool.vmoptions /kool/kool.tmpl
COPY entrypoint /kool/entrypoint
RUN chmod +x /kool/entrypoint

EXPOSE $DEBUG_PORT

ENTRYPOINT [ "/kool/entrypoint" ]
