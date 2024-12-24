#!/bin/bash
cd /srv/wiki
set -e

source .env

for i in $WIKIS; do
	# Hack workaround for RecentChanges not being updated automatically for some reason
	nice docker compose exec mediawiki \
		maintenance/run --wiki $1 rebuildrecentchanges --quiet
done
