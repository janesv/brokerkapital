<?php
	function send($arParam) {
		require_once __DIR__ . '/lib_sms/sms.ru.php';
		$smsru = new SMSRU('DAEB306B-2BFA-45A1-8D92-140A1E4B595E');
		$data = new stdClass();
		$data->multi = $arParam;
		$sms = $smsru->send_one($data);

		if ($sms->status != "OK") {
		    file_put_contents(
		    	__DIR__ . '/error.log',
		    	"Код ошибки: $sms->status_code.\nТекст ошибки: $sms->status_text.\nдата ".date('d-m-Y H:i:s')."\n".var_export($arParam, 1)."\n---\n",
		    FILE_APPEND);
		}
	}
?>