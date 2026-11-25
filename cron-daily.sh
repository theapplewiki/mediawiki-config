#!/bin/bash
cd /srv/wiki
# set -e

source .env

for i in $WIKIS; do
	# Process Echo notifications
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i Echo:processEchoEmailBatch --quiet

	# Update CirrusSearch completion index
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i CirrusSearch:UpdateSuggesterIndex --quiet

	# Update sitemap
	nice docker compose exec mediawiki \
		maintenance/run --wiki $i generateSitemap --quiet --fspath=/var/www/html/sitemap --urlpath=/sitemap
done

# Make backup
nice -n 19 ionice -c 3 ./backup.sh --quiet
