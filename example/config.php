<?php

return
[
	'output_logfile' => true,
	'logfile'        => __DIR__ . "/../output/egg{num}.log",
	'logfile_count'  => 8,

	'output_session' => true,
	'session_var'    => 'ajax_progress_egg_refresh',

	'output_stdout'  => true,

	'cache_file'     => __DIR__ . "/../output/cache.sqlite3",

	'remotes' =>
	[
		[
			'name'         => 'github::personal',
			'type'         => 'github',
			'url'          => 'https://github.com',
			'filter'       => 'Mikescher/*',
			'oauth_id'     => 'd51cb5eb4036e5b5b871',
			'oauth_secret' => file_get_contents(__DIR__ . '/github_secret.secret'),
			'token_cache'  => __DIR__ . '/../output/gh_token_cache.secret'
		],
		[
			'name'     => 'gitea::personal',
			'type'     => 'gitea',
			'url'      => 'https://gogs.mikescher.com',
			'filter'   => 'Mikescher/*',
			'username' => 'Mikescher',
			'password' => file_get_contents(__DIR__ . '/gitea_password.secret'),
		],
		[
			'name'     => 'gitea::blackforestbytes',
			'type'     => 'gitea',
			'url'      => 'https://gogs.mikescher.com',
			'filter'   => 'Blackforestbytes/*',
			'username' => 'Mikescher',
			'password' => file_get_contents(__DIR__ . '/gitea_password.secret'),
		],
	],
];