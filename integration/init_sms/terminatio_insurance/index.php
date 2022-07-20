<?php
	
	if (empty($_POST['ar_date'])) exit;

	$file = dirname(dirname(__FILE__)) . "/cron_init/data.json";
    $current = file_get_contents($file);
    $ardate_send = json_decode($current, true);

    $name = trim($_POST['name']);
    $phone = preg_replace("/[^0-9]/", '', $_POST['phone']);
    $email = trim($_POST['email']);

    if (!empty($_POST['date_create'])) {
    	$date_create = strtotime($_POST['date_create']);
    	if ($date_create < strtotime('01.11.2017')) {
    		exit("old date");
    	}
    }

    if (!empty($phone))
    {
	    foreach ($_POST['ar_date'] as $data) {
	    	$mtime = strtotime(date($data['date']));
			$setDate = strtotime("-45 day", $mtime);
			if ($setDate > strtotime(date('Y-m-d'))) {
				$ardate_send[] = array(
					'type' => 'forty_five_days',
			    	'date_send' => $setDate,
			    	'date' => $data['date'],
			    	'name' => $name,
			    	'phone' => $phone,
			    	'service' => $data['service'],
			    	'num_policy' => $data['num_policy']
			    );
			}
		}
    }

    if (!empty($email))
    {
	    foreach ($_POST['ar_date'] as $data) {
	    	$mtime = strtotime(date($data['date']));
			$setDate = strtotime("-1 month", $mtime);
			if ($setDate > strtotime(date('Y-m-d'))) {
				$ardate_send[] = array(
					'type' => 'month',
			    	'date_send' => $setDate,
			    	'date' => $data['date'],
			    	'name' => $name,
			    	'email' => $email,
			    	'service' => $data['service'],
			    	'num_policy' => $data['num_policy'],
			    	'manager_name' => $_POST['manager_name']
			    );
			}
		}
    }

    if (!empty($ardate_send)) {
    	file_put_contents($file, json_encode($ardate_send));
	}

?>