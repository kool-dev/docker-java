FROM openjdk:8-jdk-alpine

ENV ASUSER='' \
    UID='' \
    VM_OPTIONS_XMS=256m \
    VM_OPTIONS_XMX=256m \
    VM_OPTIONS_MAX_METASPACE_SIZE=512m \
    VM_OPTIONS_XMN=64m \
    VM_OPTIONS_SURVIVOR_RATIO=128 \
    CMS_TRIGGER_PERCENT=70 \
    JVM_FILE_ENCODING=UTF-8 \
    JVM_TTL=60 \
    JVM_USER_LANGUAGE='' \
    JVM_USER_COUNTRY='' \
    TZ='' \
    JAVA_OPTIONS='' \
    ENTRYPOINT='' \
    RMI_SERVER_HOSTNAME='' \
    JVM_JMXREMOTE_PORT='' \
    JVM_JMXREMOTE_AUTHENTICATE='' \
    JVM_JMXREMOTE_SSL='' \
    DEBUG_PORT=9000 \
    DEBUG_SUSPEND=n \
    CLASSPATH='' \
    MAIN_CLASS=''

WORKDIR /app

RUN adduser -D -u 1337 kool \
    && addgroup kool root \
    && echo $TZ > /etc/timezone \
    # build-deps
    && apk add --no-cache --virtual .build-deps curl libarchive-tools \
    # dockerize
    && curl -L https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-alpine-linux-amd64-v0.6.1.tar.gz | tar xz \
    && mv dockerize /usr/local/bin/dockerize \
    # deps
    && apk add --no-cache su-exec bash shadow tzdata \
       maven gradle \
    # hotswap
    && mkdir -p /usr/lib/jvm/default-jvm/jre/lib/amd64/dcevm \
    && curl -L https://github.com/dcevm/dcevm/releases/download/light-jdk8u181/DCEVM-8u181-installer.jar | bsdtar -xf- -C /tmp/ \
    && cp /tmp/linux_amd64_compiler2/product/libjvm.so /usr/lib/jvm/default-jvm/jre/lib/amd64/dcevm/libjvm.so \
    && mkdir -p /kool \
    && curl -L -o /kool/hotswap-agent.jar https://github.com/HotswapProjects/HotswapAgent/releases/download/RELEASE-1.4.1/hotswap-agent-1.4.1.jar \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /home/kool/.composer/cache

COPY kool.vmoptions /kool/kool.tmpl
COPY entrypoint /kool/entrypoint
RUN chmod +x /kool/entrypoint


ENTRYPOINT [ "dockerize", "-template", "/kool/kool.tmpl:/kool/kool.vmoptions", "/kool/entrypoint" ]
