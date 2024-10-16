#!/usr/bin/env bash

set -o errexit

# Remove network if it exists
network_name=${1?no network name specified}

if docker network inspect "$network_name" &> /dev/null; then
  docker network rm "$network_name"
  echo "Removed network $network_name"
fi
