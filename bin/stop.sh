SERVERDIR="$PWD/$(dirname $0)"

PIDFILE="$SERVERDIR/http.pid"

start-stop-daemon --stop --pidfile $PIDFILE --make-pidfile && rm $SERVERDIR/http.pid
