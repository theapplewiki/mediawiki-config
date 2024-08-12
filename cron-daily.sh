#!/bin/bash
cd /srv/wiki
set -e

source .env

for i in $WIKIS; do
	# Update sitemap
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i generateSitemap --quiet --fspath=/var/www/html/sitemap --compress=no --urlpath=/sitemap

	for i in html/sitemap/*.xml; do
		nice -n 19 ionice -c 3 gzip -9 < "$i" > "$i".gz
	done

	# Update special pages for miser mode
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i updateSpecialPages --quiet

	# Process Echo notifications
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i Echo:processEchoEmailBatch --quiet
done

# Make backup
nice -n 19 ionice -c 3 ./backup.sh --quiet
