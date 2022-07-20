<?php
/**
 * @author Samigullin Kamil <feedback@kamilsk.com>
 * @link http://www.kamilsk.com/
 *
 * Basic example of the library usage.
 */
namespace OctoLab\amoCRM\dao;

require dirname(__DIR__) . '/API.php';
// при работе с библиотекой необходимо указать свои настройки
require dirname(__DIR__) . '/config/main.php';

$response = Account::current();
echo '<pre>';
var_dump(
	$response->getCode(),
	$response->getHeader(),
	$response->getBody()
);
exit;