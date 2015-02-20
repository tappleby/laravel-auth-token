<?php

return array(
	/**
	 * Transforms username and password into the appropriate fields for Auth::attempt
	 *
	 * Can also include additional conditions eg: 'active' => true
	 */

	'format_credentials' => function ($username, $password) {
		return array(
			'email' => $username,
			'password' => $password
		);
	},
	'max_simaltaneous_logins' => 3
);