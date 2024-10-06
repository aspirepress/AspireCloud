#!/usr/bin/env bash

# Remove wp-services network if it exists
network_name=wp-services

if docker network inspect "$network_name" &> /dev/null; then
  docker network rm "$network_name"
  echo "Removed network $network_name"
fi
