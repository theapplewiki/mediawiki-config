#!/bin/bash
cd /srv/wiki
set -e

source .env

for i in $WIKIS; do
	# Update sitemap
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i generateSitemap --quiet --fspath=/var/www/html/sitemap --urlpath=/sitemap

	# Process Echo notifications
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i Echo:processEchoEmailBatch --quiet
done

# Make backup
nice -n 19 ionice -c 3 ./backup.sh --quiet
