FROM {{ $from }}

ENV ASUSER='' \
    UID='' \
    VM_OPTIONS_XMX=256m \
@if ($version >= 8)
    VM_OPTIONS_MAX_METASPACE_SIZE=128m \
@else
    VM_OPTIONS_PERM_SIZE=256m \
@endif
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
@if ($prod)
    VM_OPTIONS_METASPACE_SIZE=128m \
    VM_OPTIONS_XMS=256m \
    JAR_FILE='/app/application.jar'
@else
    GRADLE_USER_HOME=/home/kool/.gradle \
    MAVEN_OPTS="-Dmaven.repo.local=/home/kool/.m2/repository" \
    VM_OPTIONS_METASPACE_SIZE=64m \
    VM_OPTIONS_XMS=32m \
    SDKMAN_DIR=/usr/local/sdkman \
    RMI_SERVER_HOSTNAME='' \
    JVM_JMXREMOTE_PORT='' \
    JVM_JMXREMOTE_AUTHENTICATE='' \
    JVM_JMXREMOTE_SSL='' \
    DEBUG_PORT=9000 \
    DEBUG_SUSPEND=n \
    CLASSPATH='' \
    MAIN_CLASS=''
@endif

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
@unless ($prod)
       libc6-compat zip \
    && ln -s /lib/libc.musl-x86_64.so.1 /lib/ld-linux-x86-64.so.2 \
    && mkdir -p /usr/lib/jvm/default-jvm/jre/lib/amd64/dcevm \
    && curl -L https://github.com/dcevm/dcevm/releases/download/light-jdk8u181%2B2/DCEVM-8u181-installer-build2.jar | bsdtar -xf- -C /tmp/ \
    && cp /tmp/linux_amd64_compiler2/product/libjvm.so /usr/lib/jvm/default-jvm/jre/lib/amd64/dcevm/libjvm.so \
    && mkdir -p /kool \
    && curl -L -o /kool/hotswap-agent.jar https://github.com/HotswapProjects/HotswapAgent/releases/download/RELEASE-1.4.1/hotswap-agent-1.4.1.jar \
    && curl -s https://get.sdkman.io | bash \
@endunless
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/*

@unless ($prod)
RUN apk add --no-cache curl \
    && bash -c "source /usr/local/sdkman/bin/sdkman-init.sh && sdk install maven && sdk install gradle {{ $gradleVersion }}"

ENV PATH="$SDKMAN_DIR/candidates/maven/current/bin:$SDKMAN_DIR/candidates/gradle/current/bin:$PATH"
@endunless

COPY kool.vmoptions /kool/kool.tmpl
COPY entrypoint /kool/entrypoint
RUN chmod +x /kool/entrypoint

@if ($prod)
EXPOSE $DEBUG_PORT
@endif

ENTRYPOINT [ "/kool/entrypoint" ]
