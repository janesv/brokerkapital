<?php

	if (empty($_POST['date']) || empty($_POST['phone'])) exit;

	$file = dirname(dirname(__FILE__)) . "/cron_init/data.json";
    $current = file_get_contents($file);
    $ardate_send = json_decode($current, true);

    $name = trim($_POST['name']);
    $phone = preg_replace("/[^0-9]/", '', $_POST['phone']);

    $arParamSend = array();
	foreach ($_POST['date'] as $time) {
		$setDate = strtotime("-5 day", $time);
		$tDate = date('d.m.Y', $time);
		if ($setDate == strtotime(date('Y-m-d'))){
			$arParamSend[$phone] = 'Добрый день, ' . $name . '. Напоминаем Вам о необходимости оплатить очередной взнос по КАСКО до ' . $tDate . ' г. С уважением, страховой супермаркет "КАПИТАЛ" тел.: 300-33-12, г. Воронеж, ул. Донбасская, д. 44, офис 8 (3 этаж).';
		}else if ($setDate > strtotime(date('Y-m-d'))) {
			$ardate_send[] = array(
				'type' => 'five_days',
		    	'date_send' => $setDate,
		    	'date' => $tDate,
		    	'name' => $name,
		    	'phone' => $phone
		    );
		}
	}

	if (!empty($ardate_send)) {
		$ardate_send = json_encode($ardate_send);
    	file_put_contents($file, $ardate_send);
	}

	if (!empty($arParamSend)) {
		require_once dirname(dirname(__FILE__)) . "/function_sms.php";
		send($arParamSend);
	}

?>