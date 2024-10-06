#!/usr/bin/env bash

# Create wp-services network if it doesn't exist
network_name=wp-services

if ! docker network inspect "$network_name" &> /dev/null; then
  docker network create "$network_name"
  echo "Created network $network_name"
fi
