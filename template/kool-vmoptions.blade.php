-server
-Xms@{{ .Env.VM_OPTIONS_XMS }} -Xmx@{{ .Env.VM_OPTIONS_XMX }}
@if ($version >= 8)
-XX:MetaspaceSize=@{{ .Env.VM_OPTIONS_METASPACE_SIZE }}
-XX:MaxMetaspaceSize=@{{ .Env.VM_OPTIONS_MAX_METASPACE_SIZE }}
@else
-XX:PermSize=@{{ .Env.VM_OPTIONS_PERM_SIZE }} -XX:MaxPermSize=@{{ .Env.VM_OPTIONS_PERM_SIZE }}
@endif
-Xmn@{{ .Env.VM_OPTIONS_XMN }}
-XX:SurvivorRatio=@{{ .Env.VM_OPTIONS_SURVIVOR_RATIO }}
-XX:+UseCMSInitiatingOccupancyOnly -XX:CMSInitiatingOccupancyFraction=@{{ .Env.CMS_TRIGGER_PERCENT }}
-XX:+ScavengeBeforeFullGC -XX:+CMSScavengeBeforeRemark
-Dfile.encoding=@{{ .Env.JVM_FILE_ENCODING }}
-Dsun.net.inetaddr.ttl=@{{ .Env.JVM_TTL }}
-Duser.language=@{{ .Env.JVM_USER_LANGUAGE }}
-Duser.country=@{{ .Env.JVM_USER_COUNTRY }}
@unless ($prod)
#-Djava.rmi.server.hostname=@{{ .Env.RMI_SERVER_HOSTNAME }}
#-Dcom.sun.management.jmxremote
#-Dcom.sun.management.jmxremote.port=@{{ .Env.JVM_JMXREMOTE_PORT }}
#-Dcom.sun.management.jmxremote.authenticate=@{{ .Env.JVM_JMXREMOTE_AUTHENTICATE }}
#-Dcom.sun.management.jmxremote.ssl=@{{ .Env.JVM_JMXREMOTE_SSL }}
#-agentlib:jdwp=transport=dt_socket,address=127.0.0.1:@{{ .Env.DEBUG_PORT }},suspend=@{{ .Env.DEBUG_SUSPEND }},server=n
-XX:TieredStopAtLevel=1
-noverify
#-XXaltjvm=dcevm
#-javaagent:/kool/hotswap-agent.jar
@endunless
