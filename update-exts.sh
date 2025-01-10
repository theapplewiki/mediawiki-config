#!/bin/bash
set -e

source .env
cd "$(dirname "$0")"
git submodule init

for i in html/{extensions,skins}/*; do
	echo -n "$i -> "
	if [[ ! -e $i/.git ]]; then
		echo "Not a git repo?"
		continue
	fi

	cd $i
	rev=$(git rev-parse --abbrev-ref HEAD)
	if [[ $rev =~ REL* ]]; then
		rev=$MW_GIT_REF
	fi
	echo $rev
	git pull --ff-only origin $rev || :
	git submodule update --init --recursive || :
	cd ../../..
done

docker compose exec mediawiki \
	composer update --no-dev --optimize-autoloader

for i in $WIKIS; do
	docker compose exec mediawiki \
		maintenance/run --wiki $i update --quick
	docker compose exec mediawiki \
		maintenance/run --wiki $i update --quick
done

docker compose restart mediawiki jobrunner
rm -rf cache/*
