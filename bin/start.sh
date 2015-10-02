SERVERDIR="$PWD/$(dirname $0)"

PHPBIN=${PHPBIN:-"$(which php)"}
SERVERADDRESS="-S 0.0.0.0:80"
ARGS="$SERVERADDRESS $SERVERDIR/serve.php"
PIDFILE="$SERVERDIR/http.pid"

start-stop-daemon --start --background --pidfile $PIDFILE --make-pidfile --exec $PHPBIN -- $ARGS
