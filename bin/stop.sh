# Note: The value of $0 has changed with Composer 2.2, so we're relying on
# Composer 2.2+ as the required minimum now.
SERVERDIR="$(dirname $0)"

PIDFILE="$SERVERDIR/http.pid"

start-stop-daemon --stop --pidfile $PIDFILE --make-pidfile && rm $SERVERDIR/http.pid
