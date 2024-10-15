#!/usr/bin/env bash

# Create network if it doesn't exist already
network_name=${1?no network name specified}

if ! docker network inspect "$network_name" &> /dev/null; then
  docker network create "$network_name"
  echo "Created network $network_name"
fi

