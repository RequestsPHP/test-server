<?php

namespace Requests\TestServer;

use Exception;

function get_routes()
{
	global $request_data, $base_url;
	$routes = [];

	// Request data!
	$routes['/get'] = static function () use ($request_data) {
		if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
			exit;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
			throw new Exception('Method not allowed', 405);
		}
		return $request_data;
	};
	$routes['/post'] = static function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generatePostData();
	};
	$routes['/put'] = static function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generatePostData();
	};
	$routes['/patch'] = static function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generatePostData();
	};
	$routes['/delete'] = static function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generatePostData();
	};
	$routes['/options'] = static function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generatePostData();
	};
	$routes['/trace'] = static function () use ($request_data) {
		if ($_SERVER['REQUEST_METHOD'] !== 'TRACE') {
			throw new Exception('Method not allowed', 405);
		}

		return $request_data;
	};
	$routes['/purge'] = static function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'PURGE') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generatePostData();
	};
	$routes['/lock'] = static function () {
		if ($_SERVER['REQUEST_METHOD'] !== 'LOCK') {
			throw new Exception('Method not allowed', 405);
		}

		return Response::generatePostData();
	};

	// Cookies!
	$routes['/cookies'] = static function () {
		return [
			'cookies' => $_COOKIE,
		];
	};
	$routes['/cookies/set'] = static function () {
		foreach ($_GET as $key => $value) {
			setcookie($key, $value, 0, '/');
		}

		Response::redirect('/cookies');
		exit;
	};
	$routes['/cookies/set/<key>/<value>'] = static function ($args) {
		$expiry = isset($_GET['expiry']) ? (int) $_GET['expiry'] : 0;
		setcookie($args['key'], $args['value'], $expiry, '/');

		Response::redirect('/cookies');
		exit;
	};
	$routes['/cookies/delete'] = static function () {
		foreach ($_GET as $key => $value) {
			setcookie($key, '', time() - 3600, '/');
		}

		Response::redirect('/cookies');
		exit;
	};

	$routes['/basic-auth/<user>/<password>'] = static function ($args) {
		$supplied = [
			'user'     => empty($_SERVER['PHP_AUTH_USER']) ? false : $_SERVER['PHP_AUTH_USER'],
			'password' => empty($_SERVER['PHP_AUTH_PW'])   ? false : $_SERVER['PHP_AUTH_PW'],
		];

		if ($args['user'] !== $supplied['user'] || $args['password'] !== $supplied['password']) {
			http_response_code(401);
			header('WWW-Authenticate: Basic realm="Fake Realm"');
			return;
		}

		return [
			'authenticated' => true,
			'user' => $args['user'],
		];
	};

	// Redirects!
	$routes['/redirect/<number>'] = static function ($args) use ($routes) {
		$num = (int) max((int) $args['number'], 1);
		if ($num === 1) {
			Response::redirect('/get');
			exit;
		}

		$num--;

		Response::redirect(sprintf('/redirect/%d', $num));
		exit;
	};
	$routes['/redirect-to'] = static function () {
		$location = $_GET['url'];
		header('Location: ' . $location, true, 302);
		exit;
	};
	$routes['/relative-redirect/<number>'] = static function ($args) {
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
	$routes['/delay/<delay>'] = static function ($args) use ($routes) {
		$delay = min($args['delay'], 10);
		sleep($delay);

		return $routes['/get'];
	};
	$routes['/status/<code>'] = static function ($args) use ($base_url) {
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
	$routes['/stream/<num>'] = static function ($args) use ($request_data) {
		$response = $request_data;
		$num = min($args['num'], 100);
		$generate_stream = static function () use ($num, $response) {
			foreach (range(0, $num - 1) as $n) {
				$response['id'] = $n;
				yield json_encode($response, JSON_PRETTY_PRINT) . "\n";
			}
		};

		header('Transfer-Encoding: chunked');
		foreach ($generate_stream() as $response) {
			printf("%x\r\n%s\r\n", strlen($response), $response);
			flush();
		}
		echo "0\r\n\r\n";
		exit;
	};
	$routes['/gzip'] = static function () use ($request_data) {
		$response = $request_data;
		$response['gzipped'] = true;

		$response = json_encode($response, JSON_PRETTY_PRINT);
		$response = gzencode($response, 4, FORCE_GZIP);

		header('Content-Encoding: gzip');
		header('Content-Length: ' . strlen($response));

		echo $response;
		exit;
	};
	$routes['/bytes/<bytes>'] = static function ($args) {
		header('Content-Type: application/octet-stream');

		mt_srand(0);
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
	$routes['/'] = static function () use ($routes) {
		header('Content-Type: text/html; charset=utf-8');

		echo '<ul>';
		foreach ($routes as $url => $_) {
			echo '<li><code>' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401) . '</code></li>';
		}
		echo '</ul>';
		exit;
	};

	return $routes;
}
