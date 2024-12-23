#!/bin/bash
cd /srv/wiki
set -e

source .env

for i in $WIKIS; do
	# Update special pages for miser mode
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i updateSpecialPages --quiet
done
