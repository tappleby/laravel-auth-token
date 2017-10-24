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
	/**
	 * Transforms login and password into fields that are received via POST
	 *
	 * Rules are also specified
	 */
	'login_credential'            => 'username',
    'login_credential_rules'      => array('required'),
    'password_credential'         => 'password',
    'password_credential_rules'   => array('required'),
);