<?php

class Utils
{
	/**
	 * @param $str string
	 * @param $args string[]
	 * @return string
	 */
	public static function sharpFormat($str, $args)
	{
		foreach ($args as $key => $val)
		{
			$str = str_replace('{'.$key.'}', $val, $str);
		}
		return $str;
	}

	/**
	 * @param $haystack string
	 * @param $needle string
	 * @return bool
	 */
	public static function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	/**
	 * @param $filter string
	 * @param $name string
	 * @return bool
	 */
	public static function isRepoFilterMatch($filter, $name)
	{
		$f0 = explode('/', $filter);
		$f1 = explode('/', $name);

		if (count($f0) !== 2) return false;
		if (count($f1) !== 2) return false;

		if ($f0[0] !== $f1[0] && $f0[0] !== '*') return false;
		if ($f0[1] !== $f1[1] && $f0[1] !== '*') return false;

		return true;
	}

	/**
	 * @param $logger ILogger
	 * @param $url string
	 * @param $authtoken string
	 * @return array|mixed
	 */
	public static function getJSON($logger, $url, $authtoken) {
		if (array_key_exists('HTTP_USER_AGENT', $_SERVER)) {
			$options  =
				[
					'http'  =>
						[
							'user_agent' => $_SERVER['HTTP_USER_AGENT'],
							'header' => 'Authorization: token ' . $authtoken,
						],
					'https' =>
						[
							'user_agent' => $_SERVER['HTTP_USER_AGENT'],
							'header' => 'Authorization: token ' . $authtoken,
						],
				];
		} else {
			$options  =
				[
					'http' =>
						[
							'user_agent' => 'ExtendedGitGraph_for_mikescher.com',
							'header' => 'Authorization: token ' . $authtoken,
						],
					'https' =>
						[
							'user_agent' => 'ExtendedGitGraph_for_mikescher.com',
							'header' => 'Authorization: token ' . $authtoken,
						],
				];
		}

		$context  = stream_context_create($options);

		$response = @file_get_contents($url, false, $context);

		if ($response === false)
		{
			$logger->proclog("Error recieving json: '" . $url . "'");
			$logger->proclog(print_r(error_get_last(), true));
			return [];
		}

		return json_decode($response);
	}
}
