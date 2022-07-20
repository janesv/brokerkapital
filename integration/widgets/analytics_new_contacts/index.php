<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

    if (isset($_SERVER["HTTP_ORIGIN"])) {
        header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
    }

    if (empty($_POST['contact_ids']) || empty($_POST['user_id'])) exit;

    require_once __DIR__ . '/../auth_amo.php';
    require_once __DIR__ . '/data/index.php';

    function error($text) {
        return json_encode(array('status' => $text));
    }

    if (empty($arFieldData)) exit;

    $select_user = trim($_POST['user_id']);
    $contactIds = $_POST['contact_ids'];

    #account get
    $response = OctoLab\amoCRM\dao\Account::current(array('with' => 'users'));
    $account = json_decode($response->getBody(), true);

    $ar_users = array();
    if (!empty($account['_embedded']['users'])) {
        $ar_users = $account['_embedded']['users'];
    } else {
        exit(error("error: account get"));
    }

    #sort users
    $is_admin = false;
    $arSortUsers = array(
        'managers' => array()
    );

    foreach ($ar_users as $users) {
        if ($select_user == $users['id'] && $users['is_admin']) {
            $is_admin = true;
        }
        if ($users['group_id'] == 0) {
            $arSortUsers['managers'][] = array('id' => $users['id'], 'name' => $users['name']);
        }
    }

    #get contacts
    $arrayContacts = array();
    while (count($contactIds) > 0) {

        $_contactIds = array_splice($contactIds, 0, 250);
        $response = OctoLab\amoCRM\dao\Contact::get(array("id" => $_contactIds));
        $response = json_decode($response->getBody(), true);

        if (!empty($response["_embedded"]["items"])) {
            $arrayContacts = array_merge($arrayContacts, $response["_embedded"]["items"]);
        }
    }

    if (empty($arrayContacts)) {
    	exit(error("error: contact get"));
    }

    #fields
    $fields_source 			 = 182869;
    $fields_design_manager	 = 414995;
    $fields_service			 = 376693;

    #main process
    $arData = array(
    	'managers' => array(
    		'count' => 0,
    		'list' => array()
    	)
    );

//     foreach ($arrayContacts as $contact) {

//       $contact_id      = $contact['id'];
//       $contact_name    = $contact['name'];
//       $user_id         = $contact['responsible_user_id'];

//     	$price        	 = 0;
// 		$count					 = 0;
//     	$leads 					 = $contact['leads'];
// 		$first_lead			 = array();
// 		$first_lead_type = "";
// 		$leadIds = array();

// 		#get leads for a contact
// 		$arrayLeads = array();

// 		foreach ($leads as $lead) {
// 			array_push($leadIds, $lead['id']);
// 		}

// 	    while (count($leadIds) > 0) {

// 	        $_leadIds = array_splice($leadIds, 0, 250);
// 	        $response = OctoLab\amoCRM\dao\Lead::get(array("id" => $_leadIds));
// 	        $response = json_decode($response->getBody(), true);

// 	        if (!empty($response["_embedded"]["items"])) {
// 	            $arrayLeads = array_merge($arrayLeads, $response["_embedded"]["items"]);
// 	        }
// 	    }

// 	    if (empty($arrayLeads)) {
// 	    	exit(error("error: lead get"));
// 	    }

// 			foreach ($arrayLeads as $lead) {

//         $lead_id      = $lead['id'];
//         $lead_name    = $lead['name'];
// 				$created_at   = $lead['created_at'];
// 	    	$custom_field = $lead['custom_fields'];

// 				if (!empty($first_lead)) {
// 					if ($first_lead['created_at'] > $created_at) {
// 						$first_lead = $lead;
// 					}
// 				} else {
// 					$first_lead = $lead;
// 				}


// 			if (!empty($first_lead)) {
// 				$first_lead_type = $first_lead['custom_fields'][376693]['values'][0]['value'];
// 			}


//     		if (!isset($arData['managers']['list'][$user_id])) {
//     			$arData['managers']['list'][$user_id] = array(
// 										'count'                => $count,
//                     'lead_types'           => array()
//     			);
// 	    	}


// 				$lead_type_id = $first_lead['custom_fields'][376693]['values'][0]['enum'];
// 				if (!isset($arData['managers']['list'][$user_id]['lead_types'][$lead_type_id])) {
// 					$arData['managers']['list'][$user_id]['lead_types'][$lead_type_id] = array(
// 						'name'  => $first_lead_type,
// 						'count' => 1,
//             'items' => array()
// 					);
// 				} else {
// 					$arData['managers']['list'][$user_id]['lead_types'][$lead_type_id]['count'] += 1;
// 				}

// 				$count += 1;
// 			}

//         // итоговая число сделок менеджера
//         $arData['managers']['list'][$user_id]['count'] += $count;

//         $arData['managers']['list'][$user_id]['lead_types'][$lead_type_id]['items'][] = array(
//             'id'    => $contact_id,
//             'name'  => $contact_name,
//             'price' => $_price
//         );
//     }

//     #delete superfluous
//     if (!$is_admin) {
//         foreach ($arData['managers']['list'] as $__user_id => $value) {
//             if ($select_user != $__user_id) {
//                 unset($arData['managers']['list'][$__user_id]);
//             }
//         }
//     }

//     #init managers
//     foreach ($arData['managers']['list'] as $id => $ar_data) {
// 			$arData['managers']['count'] += $ar_data['count'];
//     }

    //echo json_encode(array('status' => 'ok', 'ar_data' => $arData));
    echo json_encode(array('status' => 'ok'));

?>