FROM {{ $from }}

ENV ASUSER='' \
    UID='' \
    VM_OPTIONS_XMS=256m \
    VM_OPTIONS_XMX=256m \
@if ($version >= 8)
    VM_OPTIONS_MAX_METASPACE_SIZE=512m \
@else
    VM_OPTIONS_PERM_SIZE=512m \
@endif
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
@if ($prod)
    JAR_FILE='/app/application.jar'
@else
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

RUN echo $TZ > /etc/timezone \
    # build-deps
    && apt-get update \
    && apt-get install -o APT::Immediate-Configure=false -y --no-install-recommends curl libarchive-tools \
    # dockerize
    && curl -L https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-alpine-linux-amd64-v0.6.1.tar.gz | tar xz \
    && mv dockerize /usr/local/bin/dockerize \
@unless ($prod)
    # deps
    @if ($version === 11)
    && mkdir -p /usr/share/man/man1/ \
    @endif
    && apt-get install -y --no-install-recommends maven gradle \
    # hotswap
    && mkdir -p /usr/lib/jvm/java-{{ $version }}-openjdk-amd64/jre/lib/amd64/dcevm \
    @if ($version <= 7)
    && curl -L https://github.com/dcevm/dcevm/releases/download/light-jdk7u79%2B3/DCEVM-light-7u79-installer.jar | bsdtar -xf- -C /tmp/ \
    && cp /tmp/linux_amd64_compiler2/product/libjvm.so /usr/lib/jvm/java-{{ $version }}-openjdk-amd64/jre/lib/amd64/dcevm/libjvm.so \
    @else
    && curl -L https://github.com/dcevm/dcevm/releases/download/light-jdk8u181/DCEVM-8u181-installer.jar | bsdtar -xf- -C /tmp/ \
    && cp /tmp/linux_amd64_compiler2/product/libjvm.so /usr/lib/jvm/java-{{ $version }}-openjdk-amd64/jre/lib/amd64/dcevm/libjvm.so \
    @endif
    && mkdir -p /kool \
    && curl -L -o /kool/hotswap-agent.jar https://github.com/HotswapProjects/HotswapAgent/releases/download/RELEASE-1.4.1/hotswap-agent-1.4.1.jar \
@endunless
    && apt-get purge -y --auto-remove curl libarchive-tools \
    && rm -rf /var/lib/apt/lists/* /tmp/*

COPY kool.vmoptions /kool/kool.tmpl
COPY entrypoint /kool/entrypoint
RUN chmod +x /kool/entrypoint

@if ($prod)
EXPOSE $DEBUG_PORT
@endif

ENTRYPOINT [ "dockerize", "-template", "/kool/kool.tmpl:/kool/kool.vmoptions", "/kool/entrypoint" ]
