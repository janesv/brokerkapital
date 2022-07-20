<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 *
 * Basic example of the library configuration.
 */
$app->register('query', array(
		'url' => 'https://{subdomain}.amocrm.ru',
		'user' => '{USER_LOGIN}',
		'token' => '{USER_HASH}',
	))
	->setRuntimePath('{path}');