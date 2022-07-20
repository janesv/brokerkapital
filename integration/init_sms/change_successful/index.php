<?php

    $lead_id = NULL;

    if (isset($_POST['leads']['add'][0]['id'])) {
        $lead_id = $_POST['leads']['add'][0]['id'];
    } else if (isset($_POST['leads']['status'][0]['id'])) {
        $lead_id = $_POST['leads']['status'][0]['id'];
    } else {
        exit;
    }

	require_once $_SERVER["DOCUMENT_ROOT"] . "/integration/auth_amo.php";
	$raw_response = OctoLab\amoCRM\dao\Lead::get(array('id' => $lead_id));
    $response = json_decode($raw_response->getBody(), true);
    
    if (empty($response['_embedded']['items']))
        exit;

    $ar_lead        = $response['_embedded']['items'][0];
    $clean_name     = explode('/', $ar_lead['name']);
    $name           = $clean_name[0];
    $user_id        = $ar_lead['responsible_user_id'];
    $custom_field   = $ar_lead['custom_fields'];

    $serviceName    = "";
    $arDeadline     = array();
    $ar_fieldIdDate = array(294997, 366301, 366357, 366705, 366727, 366747, 366769, 366789, 366811, 366839,
        366923, 366941, 366959, 366981, 367005, 381815, 381829, 381843);
    
    foreach ($custom_field as $field) {
    	if ($field['id'] == 376693) {
            foreach ($field['values'] as $service) {
                $serviceName .= $service['value'] . ", ";
            }
    	} else if (array_search($field['id'], $ar_fieldIdDate) !== false) {
            $date = $field['values'][0]['value'];
            if (strtotime($date) > strtotime("00:00")) {
                if (strtotime("-30 days", strtotime($date)) < strtotime("00:00")) {
                    $arDeadline[] = strtotime("23:59");
                } else {
                    $arDeadline[] = strtotime("23:59 -30 days", strtotime($date));
                }
            }
        }
    }

    if (empty($serviceName) || empty($arDeadline))
        exit;

    $serviceName = substr($serviceName, 0, -2);

    #set task
    $arTask = array();
    foreach ($arDeadline as $date) {
        $arTask[] = array(
            "element_id" => $ar_lead['id'],
            "element_type" => 2,
            "task_type" => 4,
            "created_by" => $user_id,
            "responsible_user_id" => $user_id,
            "text" => "ПОЗВОНИТЬ и продлить полис",
            "complete_till" => $date
        );
    }

    OctoLab\amoCRM\dao\Task::set(array(
        'add' => $arTask
    ));

    #get contact
    if (empty($ar_lead['main_contact']))
        exit;

    $contact_id = $ar_lead['main_contact']['id'];

    $raw_response = OctoLab\amoCRM\dao\Contact::get(array('id' => $contact_id));
    $response = json_decode($raw_response->getBody(), true);
    
    if (empty($response['_embedded']['items']))
        exit;

    $ar_contact = $response['_embedded']['items'][0];
    $custom_field = $ar_contact['custom_fields'];

    #get email
    $email = "";
    foreach ($custom_field as $field) {
        if ($field['id'] == '163593') {
            $email = trim($field['values'][0]['value']);
        }
    }

    if ($email == "")
        exit;

    #open file
    $file = dirname(dirname(__FILE__)) . "/cron_init/data.json";
    $current = file_get_contents($file);
    $ardate_send = json_decode($current, true);

    $ardate_send[] = array(
        'type' => 'successful',
        'date_send' => strtotime("00:00 +45 days"),
        'email' => $email,
        'name' => $name,
        'service' => $serviceName
    );

    file_put_contents($file, json_encode($ardate_send));

?>