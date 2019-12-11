<?php

class Utils
{
	public static function sharpFormat($str, $args)
	{
		foreach ($args as $key => $val)
		{
			$str = str_replace('{'.$key.'}', $val, $str);
		}
		return $str;
	}

	public static function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
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
			return [];
		}

		return json_decode($response);
	}
}
