<?php

if ( ! function_exists( 'Requests\\TestServer\\get_routes' ) ) {
	require dirname( __DIR__ ) . '/lib/utils.php';
	require dirname( __DIR__ ) . '/lib/routes.php';
}

ini_set('html_errors', false);
header('Content-Type: application/json; charset=utf-8');

$base_url = 'http://' . $_SERVER['HTTP_HOST'];

$headers = null;
if (function_exists('apache_request_headers')) {
	$headers = apache_request_headers();
}
elseif (function_exists('getallheaders')) {
	$headers = getallheaders();
}
else {
	$headers = array();
	foreach ($_SERVER as $name => $value) {
		if ($name === 'CONTENT_TYPE') {
			if ($value !== '') {
				$headers['content-type'] = $value;
			}
			continue;
		}
		if ($name === 'CONTENT_LENGTH') {
			if ($value !== '') {
				$headers['content-length'] = $value;
			}
			continue;
		}
		if (strpos($name, 'HTTP_') !== 0) {
			continue;
		}

		// Strip HTTP_ prefix and lowercase
		$key = strtolower(substr($name, 5));
		$key = str_replace('_', ' ', $key);
		$key = ucwords($key);
		$key = str_replace(' ', '-', $key);
		$headers[$key] = $value;
	}
}

// Are we reverse proxied?
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	// Ensure caching is off
	header('Cache-Control: no-cache');
}

$request_data = [
	'url' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
	'headers' => $headers,
	'origin' => $_SERVER['REMOTE_ADDR'],
	'args' => empty($_SERVER['QUERY_STRING']) ? new stdClass : Requests\TestServer\parse_params_rfc( $_SERVER['QUERY_STRING'] ),
];

$routes = Requests\TestServer\get_routes();

$data = null;
$here = $_SERVER['REQUEST_URI'];
if (strpos($here, '?') !== false) {
	$here = substr($here, 0, strpos($here, '?'));
}

try {
	foreach ($routes as $route => $callback) {
		$route = preg_replace('#<(\w+)>#i', '(?P<\1>\w+)', $route);
		$match = preg_match('#^' . $route . '$#i', $here, $matches);
		if (empty($match))
			continue;

		$data = $callback;
		break;
	}

	if (empty($data)) {
		throw new Exception('Requested URL not found', 404);
	}

	while (is_callable($data)) {
		$data = call_user_func($data, $matches);
	}
}
catch (Exception $e) {
	http_response_code($e->getCode());
	$data = [ 'message' => $e->getMessage() ];
}

echo json_encode($data, JSON_PRETTY_PRINT);