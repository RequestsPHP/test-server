#!/usr/bin/env bash

# Echo all commands to the terminal.
set -x

# Note: The value of $0 has changed with Composer 2.2, so we're relying on
# Composer 2.2+ as the required minimum now.
SERVERDIR="$(dirname $0)"
PORT=${PORT:-"80"}

PHPBIN=${PHPBIN:-"$(which php)"}
SERVERADDRESS="-S 0.0.0.0:$PORT"
ARGS="$SERVERADDRESS $SERVERDIR/serve.php"
PIDFILE="$SERVERDIR/http.pid"

$PHPBIN -- $ARGS
