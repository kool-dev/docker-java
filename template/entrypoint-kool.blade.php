#!/bin/sh
set -e
dockerize -template /kool/kool.tmpl:/kool/kool.vmoptions

# Run as current user
CURRENT_USER=${ASUSER:-${UID:-0}}
VM_OPTIONS=$(< /kool/kool.vmoptions grep -Ev "^(#.*|.*=$)" | tr -s "\n" " ")
if [ -n "${CURRENT_USER}" ] && [ "${CURRENT_USER}" != "0" ]; then
    usermod -u "${CURRENT_USER}" kool
fi

if [ "$1" = "sh" ] || [ "$1" = "bash" ] || [ "$1" = "java" ] || [ "$1" = "jshell" ] || [ "$1" = "mvn" ]  || [ "$1" = "gradle" ]; then
  exec "${@}"
  exit 0
fi

@unless ($prod)
export KOOL=true
export GRADLE_USER_HOME=".gradle"
export MAVEN_OPTS="-Dmaven.repo.local=.m2/repository"
if [ -z "${CLASSPATH}" ]; then
  if [ -f pom.xml ]; then
    if [ -z "$(command -v mvn)" ]; then
      echo "This image not contains Maven"
      exit 1;
    fi

    CLASSPATH=$(mvn -q exec:exec -Dexec.executable=echo -Dexec.args="%classpath" | tail -n 1 | tr -s "\n" " ")
  fi

  if [ -f build.gradle ]; then
    if [ -z "$(command -v gradle)" ]; then
      echo "This image not contains Gradle"
      exit 1;
    fi

    if ! [ -f kool.gradle ]; then
      cat > kool.gradle <<EOF
task classPath {
    if (System.getenv('KOOL')) {
        doLast {
            println "\${sourceSets.main.runtimeClasspath.collect().join(':')}"
        }
    }
}
EOF
      printf "\napply from: 'kool.gradle'" >> build.gradle
    fi

    CLASSPATH=$(gradle -q classPath | tail -n 1 | tr -s "\n" " ")
  fi
fi

@endif

# Run entrypoint if provided
if [ -n "${ENTRYPOINT}" ] && [ -f "${ENTRYPOINT}" ]; then
    bash "${ENTRYPOINT}"
fi

@if ($prod)
exec su-exec kool java ${VM_OPTIONS} ${JAVA_OPTIONS} -jar ${JAR_FILE} "${@}"
@else
exec su-exec kool java ${VM_OPTIONS} ${JAVA_OPTIONS} -classpath ${CLASSPATH} ${MAIN_CLASS} "${@}"
@endif
