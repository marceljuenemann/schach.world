#!/usr/bin/env bash
#
# Rebuilds the test database from scratch: drops it, loads the shared schema
# and fixtures from dev/dbinit/ (files 10-39) and applies Doctrine migrations.
#
# Called automatically by tests/bootstrap.php at the start of every phpunit run
# (skip with SKIP_DB_RESET=1). Can also be run by hand:
#
#   dev/scripts/reset-test-db.sh <host> <port> <user> <password> <database>
#
set -euo pipefail

if [[ $# -ne 5 ]]; then
  echo "Usage: $0 <host> <port> <user> <password> <database>" >&2
  exit 2
fi
host=$1 port=$2 user=$3 password=$4 database=$5

# Safety net against wiping the dev database through a misconfigured .env.
if [[ "$database" != *test* ]]; then
  echo "Refusing to reset database '$database': name must contain 'test'." >&2
  exit 1
fi

root_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)

mysql_cmd=(mysql --host="$host" --port="$port" --user="$user" --protocol=TCP)
export MYSQL_PWD=$password
# The MariaDB client in the webserver image verifies server certificates by
# default, which fails against the self-signed one of the local MySQL server.
if mysql --version | grep -q MariaDB; then
  mysql_cmd+=(--skip-ssl-verify-server-cert)
fi

"${mysql_cmd[@]}" -e "DROP DATABASE IF EXISTS \`$database\`; CREATE DATABASE \`$database\`;"

# The dbinit files select the dev database with `USE nsv-main;`, so strip that
# line to load them into the test database instead.
for file in "$root_dir"/dev/dbinit/[1-3][0-9]-*.sql; do
  sed '/^USE `nsv-main`;/d' "$file"
done | "${mysql_cmd[@]}" "$database"

"$root_dir/bin/console" doctrine:migrations:migrate --em main --env=test --no-interaction --quiet
