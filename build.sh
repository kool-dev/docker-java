#!/bin/bash
set -e

for version in $(jq -r '.builds[].name' fwd-template.json); do
    echo "BUILDING VERSION: ${version}"
    docker build -t kooldev/java:"${version}" "${version}"
    echo "-----------------------------------------------------------------------------------------"
done
