#!/bin/bash
set -e

source .env
cd "$(dirname "$0")"/html

for i in {extensions,skins}/*; do
	[[ -f $i/.git ]] || continue
	printf '\n\n%s\n\n' "$i"
	cd $i
	rev=$(git rev-parse --abbrev-ref HEAD)
	if [[ $rev =~ REL* ]]; then
		rev=$MW_GIT_REF
	fi
	git pull origin $rev || :
	git submodule update --init --recursive || :
	cd -
done

cd ..

docker compose exec mediawiki \
	composer update --no-dev --optimize-autoloader
docker compose exec mediawiki \
	composer install --no-dev --optimize-autoloader

for i in $WIKIS; do
	docker compose exec mediawiki \
		maintenance/run --wiki $i update --quick
	docker compose exec mediawiki \
		maintenance/run --wiki $i update --quick
done

docker compose restart mediawiki
rm -rf cache/*
