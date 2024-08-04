#!/bin/bash
set -e
cd "$(dirname "$0")"
exec docker exec wiki-mediawiki-1 \
	nice -n 19 ionice -c 3 php maintenance/run.php runJobs "$@"
