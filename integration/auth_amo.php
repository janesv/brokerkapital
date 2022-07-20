<?php
	require_once __DIR__ . '/amocrm/API.php';
	$app->register('query', array(
	    'url' => 'https://brokerkapital.amocrm.ru',
	    'user' => 'broker-kapital2017@yandex.ru',
	    'token' => 'b09084e2ad08ef9020cff57c39501c19'
	))->setRuntimePath(__DIR__);
?>