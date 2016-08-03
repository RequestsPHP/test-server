<?php

namespace Requests\TestServer;

use Exception;

function get_routes() {
	global $request_data, $base_url;
	$routes = [];

	// Request data!
	$routes['/get'] = function () use ($request_data) {
		if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
			throw new Exception('Method not allowed', 405);
		}
		return $request_data;
	};
	$routes['/post'] = function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generate_post_data();
	};
	$routes['/put'] = function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generate_post_data();
	};
	$routes['/patch'] = function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generate_post_data();
	};
	$routes['/delete'] = function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generate_post_data();
	};
	$routes['/options'] = function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generate_post_data();
	};
	$routes['/trace'] = function () use ($request_data) {
		if ($_SERVER['REQUEST_METHOD'] !== 'TRACE') {
			throw new Exception('Method not allowed', 405);
		}

		return $request_data;
	};
	$routes['/purge'] = function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'PURGE') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generate_post_data();
	};
	$routes['/lock'] = function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'LOCK') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generate_post_data();
	};

	// Cookies!
	$routes['/cookies'] = function () {
		return [
			'cookies' => $_COOKIE,
		];
	};
	$routes['/cookies/set'] = function () {
		foreach ($_GET as $key => $value) {
			setcookie($key, $value, 0, '/');
		}

		Response::redirect('/cookies');
		exit;
	};
	$routes['/cookies/set/<key>/<value>'] = function ($args) {
		$expiry = isset($_GET['expiry']) ? (int) $_GET['expiry'] : 0;
		setcookie($args['key'], $args['value'], $expiry, '/');

		Response::redirect('/cookies');
		exit;
	};
	$routes['/cookies/delete'] = function () {
		foreach ($_GET as $key => $value) {
			setcookie($key, '', time() - 3600, '/');
		}

		Response::redirect('/cookies');
		exit;
	};

	$routes['/basic-auth/<user>/<password>'] = function ($args) {
		$supplied = [
			'user'     => empty($_SERVER['PHP_AUTH_USER']) ? false : $_SERVER['PHP_AUTH_USER'],
			'password' => empty($_SERVER['PHP_AUTH_PW'])   ? false : $_SERVER['PHP_AUTH_PW'],
		];

		if ($args['user'] !== $supplied['user'] || $args['password'] !== $supplied['password']) {
			http_response_code(401);
			header( 'WWW-Authenticate: Basic realm="Fake Realm"' );
			return;
		}

		return [
			'authenticated' => true,
			'user' => $args['user'],
		];
	};

	// Redirects!
	$routes['/redirect/<number>'] = function ($args) use ($routes) {
		$num = (int) max((int) $args['number'], 1);
		if ($num === 1) {
			Response::redirect('/get');
			exit;
		}

		$num--;

		Response::redirect(sprintf('/redirect/%d', $num));
		exit;
	};
	$routes['/redirect-to'] = function () {
		$location = $_GET['url'];
		header('Location: ' . $location, true, 302);
		exit;
	};
	$routes['/relative-redirect/<number>'] = function ($args) {
		$num = (int) max((int) $args['number'], 1);
		if ($num === 1) {
			Response::redirect('/get', 302, true);
			exit;
		}

		$num--;

		Response::redirect(sprintf('/relative-redirect/%d', $num), 302, true);
		exit;
	};

	// Miscellaneous!
	$routes['/delay/<delay>'] = function ($args) use ($routes) {
		$delay = min($args['delay'], 10);
		sleep($delay);

		return $routes['/get'];
	};
	$routes['/status/<code>'] = function ($args) use ($base_url) {
		$code = (int) $args['code'];

		switch ($code) {
			case 301:
			case 302:
			case 303:
			case 307:
				header('Location: ' . $base_url . '/get');
				break;

			case 401:
				header('WWW-Authenticate: Basic realm="Fake Realm"');
				break;

			case 407:
				header('Proxy-Authenticate: Basic realm="Fake Realm"');
				break;
		}


		http_response_code($code);
		exit;
	};
	$routes['/stream/<num>'] = function ($args) use ($request_data) {
		$response = $request_data;
		$num = min($args['num'], 100);
		$generate_stream = function () use ($num, $response) {
			foreach (range(0, $num - 1) as $n) {
				$response['id'] = $n;
				yield json_encode( $response, JSON_PRETTY_PRINT ) . "\n";
			}
		};

		header('Transfer-Encoding: chunked');
		foreach ( $generate_stream() as $response ) {
			printf("%x\r\n%s\r\n", strlen($response), $response);
			flush();
		}
		echo "0\r\n\r\n";
		exit;
	};
	$routes['/gzip'] = function () use ($request_data) {
		$response = $request_data;
		$response['gzipped'] = true;

		$response = json_encode($response, JSON_PRETTY_PRINT);
		$response = gzencode($response, 4, FORCE_GZIP);

		header('Content-Encoding: gzip');
		header('Content-Length: ' . strlen($response));

		echo $response;
		exit;
	};
	$routes['/bytes/<bytes>'] = function ($args) {
		header('Content-Type: application/octet-stream');

		mt_srand('RequestsPHP');
		$sent = 0;
		$desired = min((int) $args['bytes'], 10000);
		while ($sent < $desired) {
			$next = mt_rand(0, 255);
			echo chr($next);
			$sent++;
		}
		exit;
	};

	// Finally, the index!
	$routes['/'] = function () use ($routes) {
		header('Content-Type: text/html; charset=utf-8');

		echo '<ul>';
		foreach ($routes as $url => $_) {
			echo '<li><code>' . htmlspecialchars( $url ) . '</code></li>';
		}
		echo '</ul>';
		exit;
	};

	return $routes;
}
