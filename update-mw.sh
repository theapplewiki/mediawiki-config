#!/bin/bash
set -e

source .env
cd "$(dirname "$0")"

cd mediawiki
git pull origin $MW_GIT_REF
git submodule update --init --recursive
cd ..

docker compose build --pull --no-cache
docker compose up -d --remove-orphans

./update-exts.sh

rm -rf cache/*
nginx -s reload

cd jobrunner
composer update --no-dev --optimize-autoloader --ignore-platform-req=ext-sockets
docker compose restart jobrunner
cd ..
