SERVERDIR="$PWD/$(dirname $0)"
PORT=${PORT:-"80"}

PHPBIN=${PHPBIN:-"$(which php)"}
SERVERADDRESS="-S 0.0.0.0:$PORT"
ARGS="$SERVERADDRESS $SERVERDIR/serve.php"
PIDFILE="$SERVERDIR/http.pid"

start-stop-daemon --start --background --pidfile $PIDFILE --make-pidfile --exec $PHPBIN -- $ARGS
