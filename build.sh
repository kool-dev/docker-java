#!/bin/bash

for version in $(jq -r '.builds[].name' fwd-template.json); do
    docker build -t kooldev/java:"${version}" "${version}"
done
