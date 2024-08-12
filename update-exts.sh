#!/bin/bash
set -e

source .env
cd "$(dirname "$0")"/html

for i in {extensions,skins}/*; do
	[[ -f $i/.git ]] || continue
	printf '\n\n%s\n\n' "$i"
	cd $i
	if [[ $(git rev-parse --abbrev-ref HEAD) =~ REL* ]]; then
		git pull origin $MW_GIT_REF || :
	else
		git pull origin $(git rev-parse --abbrev-ref HEAD) || :
	fi
	git submodule update --init --recursive || :
	cd -
done

cd ..

docker compose exec mediawiki \
	composer update --no-dev --optimize-autoloader
docker compose exec mediawiki \
	composer install --no-dev --optimize-autoloader

# rm -rf cache/*

for i in $WIKIS; do
	docker compose exec mediawiki \
		maintenance/run --wiki $i update --quick
	docker compose exec mediawiki \
		maintenance/run --wiki $i update --quick
done
