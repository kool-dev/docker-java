#!/bin/sh
set -e

# Run as current user
CURRENT_USER=${ASUSER:-${UID:-0}}
VM_OPTIONS=$(< /kool/kool.vmoptions grep -Ev "^#.*")
@if ($prod)
RUN="java ${VM_OPTIONS} ${JAVA_OPTIONS} -jar ${JAR_FILE}"
@else ($prod)
if [ -e "${CLASSPATH}" ]; then
  if [ -f pom.xml ]; then
    if [ -e "$(command -v mvn)" ]; then
      echo "This image not contains Maven"
      exit 1;
    fi

    CLASSPATH=$(mvn -q exec:exec -Dexec.executable=echo -Dexec.args="%classpath")
  fi

  if [ -f build.gradle ]; then
    if [ -n "$(command -v gradle)" ]; then
      echo "This image not contains Gradle"
      exit 1;
    fi

    if ! [ -f kool.gradle ]; then
      cat > kool.gradle <<EOF
task classPath {
    println "\${sourceSets.main.runtimeClasspath.collect().join(':')}"
}

EOF
      printf "\napply from: 'kool.gradle'\n" >> build.gradle
    fi

    CLASSPATH=$(gradle -q classPath)
  fi
fi

RUN="java ${VM_OPTIONS} ${JAVA_OPTIONS} -cp ${CLASSPATH} ${MAIN_CLASS}"
@endif

if [ -n "${CURRENT_USER}" ] && [ "${CURRENT_USER}" != "0" ]; then
    usermod -u "${CURRENT_USER}" kool
fi

# Run entrypoint if provided
if [ -n "${ENTRYPOINT}" ] && [ -f "${ENTRYPOINT}" ]; then
    bash "${ENTRYPOINT}"
fi

if [ -n "${CURRENT_USER}" ] && [ "${CURRENT_USER}" != "0" ]; then
    exec su-exec kool "${RUN}" "${@}"
else
    exec "${RUN}" "${@}"
fi
