#!/bin/bash
ver=REL1_41

set -e

cd /srv/applewiki/html
for i in {extensions,skins}/*; do
	[[ -d $i/.git ]] || continue
	printf '\n\n%s\n\n' "$i"
	cd $i
	if [[ $(git rev-parse --abbrev-ref HEAD) =~ REL* ]]; then
		git pull origin $ver || :
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

docker compose exec mediawiki \
	php maintenance/run.php --wiki applewiki update --quick
docker compose exec mediawiki \
	php maintenance/run.php --wiki applewiki update --quick
