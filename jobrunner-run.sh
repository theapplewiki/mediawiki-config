#!/bin/bash
set -e
export DEBUG=0

php /jobrunner/redisJobRunnerService --config-file=/jobrunner.json &
php /jobrunner/redisJobChronService --config-file=/jobrunner.json &

wait -n
exit 1
