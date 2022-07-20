<?php

    $sapi = php_sapi_name();

    if ($sapi != "cli") exit;

	$file = __DIR__ . "/data.json";
    $current = file_get_contents($file);
    $ardate_send = json_decode($current, true);

    if (empty($ardate_send))
        exit;

    $arParamSmS = array();
    $arParamMail = array();
    $arNowSave = array();
    $toDay = strtotime(date('Y-m-d'));
    foreach ($ardate_send as $arData) {
    	if ($arData['date_send'] == $toDay) {
    	    $clean_name     = explode('/', $arData['name']);
            $name           = $clean_name[0];
            switch ($arData['type']) {
                case 'five_days':
                    $arParamSmS[$arData['phone']] = 'Здравствуйте, ' . $arData['name'] . '! Напоминаем Вам о необходимости оплатить очередной взнос по КАСКО до ' . $arData['date'] . ' г. С уважением, страховой супермаркет "КАПИТАЛ" тел.: 300-33-12, г. Воронеж, ул. Донбасская, д. 44, офис 8 (3 этаж).';
                    break;
                case 'forty_five_days':
                    $arParamSmS[$arData['phone']] = 'Здравствуйте, ' . $arData['name'] . '! Напоминаем Вам об окончании полиса ' . $arData['service'] . ' № ' . $arData['num_policy'] . ' ' . $arData['date'] . '. Вы можете получить консультацию по телефону 300-33-12 или продлить полис страхования по адресу г. Воронеж, ул. Донбасская 44, 3 этаж, 8 кабинет. С уважением, страховой супермаркет "КАПИТАЛ".';
                    break;
                case 'month':
                    $arParamMail[$arData['email']] = array(
                        'subject' => 'Ваш полис заканчивается.', 
                        'message' => 'Здравствуйте, ' . $arData['name'] . '! Напоминаем Вам об окончании полиса ' . $arData['service'] . ' № ' . $arData['num_policy'] . ' ' . $arData['date'] . '. Вы можете получить консультацию по телефону 300-33-12 или по адресу г. Воронеж, ул. Донбасская 44, 3 этаж, 8 кабинет. С уважением, страховой супермаркет "КАПИТАЛ", Ваш менеджер ' . $arData['manager_name'] . '.'
                    );
                    break;
                case 'successful':
                    $arParamMail[$arData['email']] = array(
                        'subject' => 'СПАСИБО за то, что выбрали страховой супермаркет "Капитал"!',
                        'message' => 'Здравствуйте, ' . $name . '! Хотим еще раз поблагодарить Вас за то, что воспользовались нашими услугами. Будем рады помочь Вашим родным, друзьям и знакомым. Кроме ' . $arData['service'] . ' мы так же оказываем помощь в оформлении всех видов страхования, в том числе ипотечного страхования и страхования детей.'
                    );
                    break;
            }
    	} else {
            $arNowSave[] = $arData;
        }
    }

    if (empty($arParamSmS) && empty($arParamMail)) {
        exit;
    }

    /*save file*/
    $arNowSave = json_encode($arNowSave);
    file_put_contents($file, $arNowSave);
    /*end save file*/

    /*send*/
    if (!empty($arParamSmS)) {
        require_once dirname(dirname(__FILE__)) . "/function_sms.php";
        send($arParamSmS);
    }

    if (!empty($arParamMail)) {
        
        $headers  = "From: «КАПИТАЛ» cтраховой супермаркет <info@broker-kapital.ru>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";
        $headers .= "Bcc: sdevgenia@gmail.com\r\n";

        foreach ($arParamMail as $email => $value) {
            mail($email, $value['subject'], $value['message'], $headers);
        }

    }
    /*end send*/

?>