#!/bin/bash
cd /srv/applewiki
set -e

for i in applewiki; do
	# Update sitemap
	nice docker compose exec mediawiki \
		php maintenance/run.php --wiki $i generateSitemap --quiet --fspath=/var/www/html/sitemap --compress=no --urlpath=/sitemap

	for i in html/sitemap/*.xml; do
		nice -n 19 ionice -c 3 gzip -9 < "$i" > "$i".gz
	done

	# Needed due to $wgMiserMode
	nice docker compose exec mediawiki \
		php maintenance/run.php --wiki $i updateSpecialPages --quiet
done

# Make backup
nice -n 19 ionice -c 3 ./backup.sh --quiet
