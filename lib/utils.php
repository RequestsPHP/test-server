<?php

namespace Requests\TestServer;

class Response
{
	public static function redirect($path, $code = 302, $relative = false)
	{
		global $base_url;
		$url = $path;
		if (!$relative) {
			$url = $base_url . $path;
		}

		header('Location: ' . $url, true, $code);
	}

	public static function generate_post_data()
	{
		global $request_data;
		$data = $request_data;
		$data['data'] = file_get_contents('php://input');

		$data['form'] = '';
		if (strpos($data['data'], '&') !== false) {
			$data['form'] = parse_params_rfc($data['data']);
		}

		$data['json'] = json_decode($data['data']);

		$data['files'] = array_map(function ($data) {
			return file_get_contents($data['tmp_name']);
		}, $_FILES);

		return $data;
	}
}

function parse_params_rfc($input)
{
	if (!isset($input) || !$input) {
		return [];
	}

	$pairs = explode('&', $input);

	$parsed = [];
	foreach ($pairs as $pair) {
		$split = explode('=', $pair, 2);
		$parameter = urldecode($split[0]);
		$value = isset($split[1]) ? urldecode($split[1]) : '';
		$parsed[$parameter] = $value;
	}
	return $parsed;
}
