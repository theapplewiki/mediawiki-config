#!/bin/bash
set -e

docker compose build --pull --no-cache
docker compose down
docker compose up -d

./update-exts.sh

rm -rf cache/*

cd jobrunner
composer update --no-dev --optimize-autoloader
cd ..

systemctl daemon-reload
systemctl restart applewiki-*
