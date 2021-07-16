#!/bin/bash

docker network create split

a=$(docker rm -f redis-container)
b=$(docker rm -f split-synchronizer)

docker run -d --name redis-container \
--network=split \
-p 6379:6379 \
redis

docker run -d --name split-synchronizer \
-p 3010:3010 \
--network=split \
-e SPLIT_SYNC_API_KEY=$SPLIT_SDK_API_KEY \
-e SPLIT_SYNC_REDIS_HOST=redis-container \
-e SPLIT_SYNC_REDIS_PORT=6379 \
-e SPLIT_SYNC_REDIS_PREFIX=split_ \
-e SPLIT_SYNC_LOG_STDOUT=true \
splitsoftware/split-synchronizer