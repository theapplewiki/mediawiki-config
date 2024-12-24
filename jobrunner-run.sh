#!/bin/bash
set -e

php /jobrunner/redisJobRunnerService --config-file=/jobrunner.json &
php /jobrunner/redisJobChronService --config-file=/jobrunner.json &

wait -n
exit 1
