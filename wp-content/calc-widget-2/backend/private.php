<?php
define("ITFORM_DEBUG", false);
define("ITFORM_ATTACH_SIZE", 10000000); // 10Mb

define("ITFORM_LOGIN", 'module@broker-kapital.ru');
define("ITFORM_PASSWORD", 'z0qQZ3En');

//Удалите старый файл если изменили логин и пароль
//Измените имя файла вкотором храниться токен!!!
define("ITFORM_TOKEN_FILE", 'youbetterkeepitisdfsdfsdf535nsercet');

if (ITFORM_DEBUG)
    error_reporting(E_ALL);

//Закоментируйте что бы спользовать mail();
function ItSetAuth($mail)
{
    $mail->isMail();
}
