<?php

	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

    if (isset($_SERVER["HTTP_ORIGIN"])) {
        header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
    }

    if (empty($_POST['lead_ids']) || empty($_POST['user_id'])) exit;

    require_once __DIR__ . '/../auth_amo.php';
    require_once __DIR__ . '/data/index.php';

    function error($text) {
        return json_encode(array('status' => $text));
    }

    if (empty($arFieldData)) exit;

    $select_user = trim($_POST['user_id']);
    $leadIds = $_POST['lead_ids'];

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

    #get leads
    $arrayLeads = array();
    while (count($leadIds) > 0) {

        $_leadIds = array_splice($leadIds, 0, 250);
        $response = OctoLab\amoCRM\dao\Lead::get(array("id" => $_leadIds));
        $response = json_decode($response->getBody(), true);

        if (!empty($response["_embedded"]["items"])) {
            $arrayLeads = array_merge($arrayLeads, $response["_embedded"]["items"]);
        }
    }
    
    if (empty($arrayLeads)) {
    	exit(error("error: lead get"));
    }

    #fields
    $fields_source 			 = 182869;
    $fields_design_manager	 = 414995;
    $fields_service			 = 376693;
    
    $fields_policy_kasko     = 366645;
    $fields_commission_kasko = 366647;
    $fields_tax_kasko        = 366649;
    $fields_prev_lead_id     = 489580;

    $enums_kasko             = 772963;
    $enums_osago             = 772961;
    $enums_making            = 772981;
    $enums_tax_kasko         = 756425;
    $enums_diagnostic_cards  = 772979;
    
    $osago_tipographskiy     = 772959;

    #service|price field id open file
    $file = __DIR__ . "/../service/params.json";
    $current = file_get_contents($file);
    $arServiceFile = json_decode($current, true);

    #main process
    $arData = array(
    	'managers' => array(
    		'company_price' => 0,
    		'list' => array()
    	)
    );

    foreach ($arrayLeads as $lead) {

        $lead_id      = $lead['id'];
        $lead_name    = $lead['name'];
    	$user_id      = $lead['responsible_user_id'];
    	$main_contact = $lead['main_contact'];
    	$price        = (float) $lead['sale'];
    	$custom_field = $lead['custom_fields'];
    	//$custom_field_1 = $custom_field[376693]['values'];
//     	if ($user_id == 125700) {
//     	file_put_contents(__DIR__ . "/test.log", date('d-m-Y H:i:s')." data: $lead_id, $lead_name, $custom_field_1\n", FILE_APPEND);
// }

    	#custom field get
    	$source = "";
    	$design_manager = "";
    	$arService = array();
    	$cost_policy = 0;
        $commission = 0;
        $tax = 13; // change 0 -> 13
        $prev_lead_id = "";
        
        $source_agent = "Агент привлеченец";
        $source_is_agent = false;
        
    	foreach ($custom_field as $fields) {
    	    
            if ($fields['id'] == $fields_source) {
                $source = $fields['values'][0]['value'];
            } else if ($fields['id'] == $fields_design_manager) {
            	$design_manager = $fields['values'][0]['value'];
            } else if ($fields['id'] == $fields_service) {
                $arService = $fields['values'];
            } else if ($fields['id'] == $fields_policy_kasko) {
                $cost_policy = round(str_replace(",", ".", $fields['values'][0]['value']), 2);
            } else if ($fields['id'] == $fields_commission_kasko) {
                $commission = round($fields['values'][0]['value'], 2);
            } else if ($fields['id'] == $fields_tax_kasko) {
                if ($fields['values'][0]['enum'] == $enums_tax_kasko) {
                    $tax = 10;
                }
            } else if ($fields['id'] == $fields_prev_lead_id) {
                $prev_lead_id = $fields['values'][0]['value'];
            }
    	}

    	if (empty($arService)) {
    		continue;
    	}

    	#type user get
    	$type_user = "";
    	foreach ($arSortUsers as $_type => $_ar_users) {
    		foreach ($_ar_users as $users) {
    			if ($user_id == $users['id']) {
    				$type_user = $_type;
    				break;
    			}
    		}
    	}
        if ($type_user == "managers") {

    		if (!isset($arData['managers']['list'][$user_id])) {
    			$arData['managers']['list'][$user_id] = array(
    				'price'                => $price,
                    'ar_service'           => array(),
    				'ar_service_partners'  => array(
                        'kasko' => array(
                            'price' => 0,
                            'items' => array()
                        ),
                        'osago' => array(
                            'price' => 0,
                            'items' => array()
                        ),
                        'making' => array(
                            'price' => 0,
                            'items' => array()
                        ),
                        'other' => array(
                            'price' => 0,
                            'items' => array()
                        )
                    )
    			);
	    	} else {
	    		$arData['managers']['list'][$user_id]['price'] += $price;
	    	}

    		foreach ($arService as $_service) {
                
                if ($_service['enum'] == $osago_tipographskiy) {
                    $service_id = $enums_osago;
                    $_service['value'] = "ОСАГО";
                } else {
                    $service_id = $_service['enum'];
                }
                if (!isset($arServiceFile[$service_id])) {
                	file_put_contents(__DIR__ . "/error.log", date('d-m-Y H:i:s')." error: $service_id\n", FILE_APPEND);
                    continue;
                }

                $_price = 0;
                $company_price_id = $arServiceFile[$service_id]["policy_cost"];
                if (isset($arServiceFile[$service_id]["company_price"])) {
                    $company_price_id = $arServiceFile[$service_id]["company_price"];
                }
                
                foreach ($custom_field as $fields) {
                    if ($fields['id'] == $company_price_id) {
                        $_price = round(str_replace(",", ".", $fields['values'][0]['value']), 2);
                    }
                }

				if (!isset($arData['managers']['list'][$user_id]['ar_service'][$service_id])) {
    					$arData['managers']['list'][$user_id]['ar_service'][$service_id] = array(
    						'name'  => $_service['value'],
    						'count' => 1,
    						'price' => $_price,
                            'items' => array()
    					);
				} else {
					$arData['managers']['list'][$user_id]['ar_service'][$service_id]['count'] ++;
                    $arData['managers']['list'][$user_id]['ar_service'][$service_id]['price'] += $_price;
				}
				
				if (isset($source) && $source == $source_agent) {
				    $source_is_agent = true;
				}

                $arData['managers']['list'][$user_id]['ar_service'][$service_id]['items'][] = array(
                    'id'    => $lead_id,
                    'name'  => $lead_name,
                    'main_contact' => $main_contact,
                    'price' => $_price,
                    'source' => $source,
                    'source_is_agent' => $source_is_agent,
                    'prev_lead_id' => $prev_lead_id
                );
    		}
    	}
    }
    
    

    #delete superfluous
    if (!$is_admin) {
        foreach ($arData['managers']['list'] as $__user_id => $value) {
            if ($select_user != $__user_id) {
                unset($arData['managers']['list'][$__user_id]);
            }
        }
    }


    echo json_encode(array('status' => 'ok', 'ar_data' => $arData));

?>