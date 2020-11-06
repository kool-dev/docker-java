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

ENV GRADLE_HOME /usr/local/gradle
ENV PATH ${GRADLE_HOME}/bin:${PATH}

WORKDIR /app

RUN echo $TZ > /etc/timezone \
    # build-deps
    && yum install -y unzip \
    # dockerize
    && curl -L https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-alpine-linux-amd64-v0.6.1.tar.gz | tar xz \
    && mv dockerize /usr/local/bin/dockerize \
@unless ($prod)
    # deps
    && curl -L http://repos.fedorapeople.org/repos/dchen/apache-maven/epel-apache-maven.repo -o /etc/yum.repos.d/epel-apache-maven.repo \
    && yum install -y maven \
    && curl -L https://downloads.gradle-dn.com/distributions/gradle-6.7-bin.zip -o gradle-6.7-bin.zip \
    && unzip gradle-6.7-bin.zip \
    && mv gradle-6.7 /usr/local/gradle \
    # hotswap
    && mkdir -p /usr/lib/jvm/default/lib/dcevm \
    @if ($version <= 7)
    && curl -L https://github.com/dcevm/dcevm/releases/download/light-jdk7u79%2B3/DCEVM-light-7u79-installer.jar -o /tmp/DCEVM-light-7u79-installer.jar \
    && unzip /tmp/DCEVM-light-7u79-installer.jar -d /tmp/ \
    && cp /tmp/linux_amd64_compiler2/product/libjvm.so /usr/lib/jvm/default/lib/dcevm/libjvm.so \
    @else
    && curl -L https://github.com/dcevm/dcevm/releases/download/light-jdk8u181/DCEVM-8u181-installer.jar -o /tmp/DCEVM-8u181-installer.jar \
    && unzip /tmp/DCEVM-8u181-installer.jar -d /tmp/ \
    && cp /tmp/linux_amd64_compiler2/product/libjvm.so /usr/lib/jvm/default/lib/dcevm/libjvm.so \
    @endif
    && mkdir -p /kool \
    && curl -L -o /kool/hotswap-agent.jar https://github.com/HotswapProjects/HotswapAgent/releases/download/RELEASE-1.4.1/hotswap-agent-1.4.1.jar \
@endunless
    && yum remove -y unzip \
    && rm -rf /var/cache/yum/* /tmp/*

COPY kool.vmoptions /kool/kool.tmpl
COPY entrypoint /kool/entrypoint
RUN chmod +x /kool/entrypoint

@if ($prod)
EXPOSE $DEBUG_PORT
@endif

ENTRYPOINT [ "dockerize", "-template", "/kool/kool.tmpl:/kool/kool.vmoptions", "/kool/entrypoint" ]
