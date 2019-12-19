<?php

return
[
	'output_logfile'     => true,
	'logfile'            => __DIR__ . "/../output/egg{num}.log",
	'logfile_count'      => 8,

	'output_session'     => true,
	'session_var'        => 'ajax_progress_egg_refresh',

	'output_stdout'      => true,

	'data_cache_file'    => __DIR__ . "/../output/cache.sqlite3",
	'output_cache_files' => __DIR__ . "/../output/cache_{ident}.html",

	'identities'         => [ 'mailport@mikescher.de', 'mailport@mikescher.com', 'pubgit@mikescher.com' ],

	'remotes' =>
	[
		[
			'name'         => 'github::personal',
			'type'         => 'github',
			'url'          => 'https://github.com',
			'filter'       => 'Mikescher/*',
			'exclusions'   =>
			[
				'Mikescher/Befunge-93', 'Mikescher/CyberChef', 'Mikescher/gitea',
				'Mikescher/javascript-rsa', 'Mikescher/monogame', 'Mikescher/MonoGame.Extended',
				'Mikescher/notepad-plus-plus', 'Mikescher/Xamarin.Forms',
				'Mikescher/A-Practical-Guide-To-Evil-Lyx',
			],
			'oauth_id'     => 'd51cb5eb4036e5b5b871',
			'oauth_secret' => file_get_contents(__DIR__ . '/github_secret.secret'),
			'token_cache'  => __DIR__ . '/../output/gh_token_cache.secret'
		],
		[
			'name'       => 'gitea::personal',
			'type'       => 'gitea',
			'url'        => 'https://gogs.mikescher.com',
			'filter'     => 'Mikescher/*',
			'exclusions' => [],
			'username'   => 'Mikescher',
			'password'   => file_get_contents(__DIR__ . '/gitea_password.secret'),
		],
		[
			'name'       => 'gitea::blackforestbytes',
			'type'       => 'gitea',
			'url'        => 'https://gogs.mikescher.com',
			'filter'     => 'Blackforestbytes/*',
			'exclusions' => [],
			'username'   => 'Mikescher',
			'password'   => file_get_contents(__DIR__ . '/gitea_password.secret'),
		],
	],
];