<?php
(function() {
	$dsn = @$_ENV['SENTRY_DSN'];
	if (!isset($dsn) || empty($dsn)) {
		return;
	}

	require_once __DIR__ . '/html/vendor/autoload.php';

	\Sentry\init([
		'dsn' => $dsn,
		'traces_sample_rate' => 0.02,
		'profiles_sample_rate' => 0.5
	]);
})();
