#!/usr/bin/env bash
# Runs the PHP tests and rewrites all snapshots that don't match. Review the
# changes in tests/**/__snapshots__/ with git diff before committing.
# Pass phpunit arguments to limit it, e.g. dev/scripts/update-snapshots.sh tests/League
docker exec -e UPDATE_SNAPSHOTS=true nsv-webserver ./bin/phpunit "$@"
