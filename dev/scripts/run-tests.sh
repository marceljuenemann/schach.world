#!/usr/bin/env bash
# Runs the PHP tests in the webserver container. Pass phpunit arguments to run
# only some tests, e.g. dev/scripts/run-tests.sh tests/League
docker exec nsv-webserver ./bin/phpunit "$@"
