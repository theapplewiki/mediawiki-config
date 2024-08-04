#!/bin/bash
set -e

if [[ $1 == --quiet ]]; then
	qflag=-q
fi

source .env

date=$(date +%Y-%m-%d)
backupdir=/tmp/backup-$date
rm -rf $backupdir
mkdir $backupdir
chmod 000 $backupdir

for i in applewiki; do
	docker compose exec database \
		bash -c 'mariadb-dump --single-transaction -uroot -p"$MYSQL_ROOT_PASSWORD" '$i | zstd $qflag --fast=8 -o $backupdir/$i.sql.zst
	tar c \
		*.conf *.ini *.php *.service *.sh *.yml *.json Dockerfile \
		html /etc/nginx | zstd $qflag --fast=8 -o $backupdir/config.tar.zst
	tar c -C $backupdir $i.sql.zst config.tar.zst > $backupdir/backup.tar
	s3cmd $qflag --storage-class=GLACIER put $backupdir/backup.tar s3://$BACKUP_S3_BUCKET/$i-$date.tar
	rm -rf $backupdir
done

curl -fsSL "$BACKUP_HEARTBEAT_URL"
