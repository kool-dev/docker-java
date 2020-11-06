FROM openjdk:9-jre-slim

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
    JAR_FILE='/app/application.jar'

WORKDIR /app

RUN echo $TZ > /etc/timezone \
    # build-deps
    && apt-get update \
    && apt-get install -o APT::Immediate-Configure=false -y --no-install-recommends curl libarchive-tools \
    # dockerize
    && curl -L https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-alpine-linux-amd64-v0.6.1.tar.gz | tar xz \
    && mv dockerize /usr/local/bin/dockerize \
    && apt-get purge -y --auto-remove curl libarchive-tools \
    && rm -rf /var/lib/apt/lists/* /tmp/*

COPY kool.vmoptions /kool/kool.tmpl
COPY entrypoint /kool/entrypoint
RUN chmod +x /kool/entrypoint

EXPOSE $DEBUG_PORT

ENTRYPOINT [ "dockerize", "-template", "/kool/kool.tmpl:/kool/kool.vmoptions", "/kool/entrypoint" ]
