#!/bin/bash
set -e

source .env
cd "$(dirname "$0")"

docker compose down

cd mediawiki
git pull origin $MW_GIT_REF
git submodule update --init --recursive
cd ..

# docker compose build --pull --no-cache
docker compose up -d

./update-exts.sh

rm -rf cache/*

cd jobrunner
composer update --no-dev --optimize-autoloader
cd ..

systemctl daemon-reload
systemctl restart wiki-*.service
