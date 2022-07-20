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
        'managers' => array(),
        'partners' => array()
    );
    
    foreach ($ar_users as $users) {
        if ($select_user == $users['id'] && $users['is_admin']) {
            $is_admin = true;
        }
        if ($users['group_id'] == 0) {
            $arSortUsers['managers'][] = array('id' => $users['id'], 'name' => $users['name']);
        } else if ($users['group_id'] == 161686) {
            $arSortUsers['partners'][] = array('id' => $users['id'], 'name' => $users['name']);
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

    $enums_kasko             = 772963;
    $enums_osago             = 772961;
    $enums_making            = 772981;
    $enums_tax_kasko         = 756425;
    $enums_diagnostic_cards  = 772979;

    #service|price field id open file
    $file = __DIR__ . "/../service/params.json";
    $current = file_get_contents($file);
    $arServiceFile = json_decode($current, true);

    #main process
    $arData = array(
    	'managers' => array(
    		'company_price' => 0,
    		'list' => array()
    	),
    	'partners' => array(
    		'company_price' => 0,
    		'list' => array()
    	)
    );

    foreach ($arrayLeads as $lead) {

        $lead_id      = $lead['id'];
        $lead_name    = $lead['name'];
    	$user_id      = $lead['responsible_user_id'];
    	//$price        = (float) $lead['sale'];
    	$custom_field = $lead['custom_fields'];

    	#custom field get
    	$source = "";
    	$design_manager = "";
    	$arService = array();
    	$cost_policy = 0;
        $commission = 0;
        $tax = 13; // change 0 -> 13
    	foreach ($custom_field as $fields) {
            if ($fields['id'] == $fields_source) {
                $source = $fields['values'][0]['enum'];
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

    	if ($type_user == "partners") {

    		#design manager id get
            $design_manager_id = "";
	    	if (!empty($design_manager)) {
	    		foreach ($arSortUsers['managers'] as $users) {
	    			if (mb_strtolower($design_manager) == mb_strtolower($users['name'])) {
	    				$design_manager_id = $users['id'];
                        if (!isset($arData['managers']['list'][$design_manager_id])) {
                            $arData['managers']['list'][$design_manager_id] = array(
                                'price'                 => 0,
                                'ar_service'            => array(),
                                'ar_service_partners'   => array(
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
                        }
	    				break;
	    			}
	    		}
	    	}

	    	if (!isset($arData['partners']['list'][$user_id])) {
    			$arData['partners']['list'][$user_id] = array(
    				'price' => 0,
    				'price_company' => 0,
    				'ar_service' => array()
    			);
	    	}

	    	foreach ($arService as $_service) {

                $service_id = $_service['enum'];
    			if (!isset($arServiceFile[$service_id])) {
                    file_put_contents(__DIR__ . "/error.log", date('d-m-Y H:i:s')." error: $service_id\n", FILE_APPEND);
                    continue;
                }

                #field service - payment get
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

				if (!isset($arData['partners']['list'][$user_id]['ar_service'][$service_id])) {
					$arData['partners']['list'][$user_id]['ar_service'][$service_id] = array(
						'name'            => $_service['value'],
						'count'           => 1,
						'price_partners'  => 0,
                        'price_managers'  => 0,
                        'price_company'   => 0,
                        'items'           => array()
					);
				} else {
                    $arData['partners']['list'][$user_id]['ar_service'][$service_id]['count']++;
				}

                $arUserPercent   = "";
                $_price_partners = 0;
                $_price_managers = 0;
                $_price_company  = 0;

				if ($service_id == $enums_kasko) {
                    if (!empty($design_manager_id)) {
                        foreach ($arFieldData['data'] as $_data) {
                            if ($_data['service'] == $service_id) {
                                if (array_search($source, $_data['source']) !== false) {
                                    $arUserPercent = $_data['partners']; break;
                                }
                            }
                        }
                        if (is_array($arUserPercent) && !empty($arUserPercent['on_managers'][$user_id])) {

                        	$_price_managers = round($_price * (int) $arUserPercent['on_managers'][$user_id]['managers'] / 100, 2);
                    		$_price_partners = round($_price * (int) $arUserPercent['on_managers'][$user_id]['value'] / 100, 2);
                    		$_price_company = round($_price * (int) $arUserPercent['on_managers'][$user_id]['company'] / 100, 2);

                        } else if ($cost_policy > 0) {

                            if ($commission > 5) {

                                if ($tax > 0) {
                                    $_s = ($commission - (($commission/100 * $tax/100) * 100) - 5) / 100;
                                    $_price_company = round($cost_policy * 0.025, 2);
                                    $_price_managers = round($cost_policy * 0.025, 2);
                                    $_price_partners = round($cost_policy * $_s, 2);
                                }
                                
                            } else {
                                $_price_partners = round($_price / 2, 2);
                                $_price_company = round($_price_partners / 2, 2);
                                $_price_managers = round($_price_partners / 2, 2);
                            }
                            
                        }

                        $arData['managers']['list'][$design_manager_id]['ar_service_partners']['kasko']['price'] += $_price_managers;
                        $arData['managers']['list'][$design_manager_id]['ar_service_partners']['kasko']['items'][] = array(
                            'id'             => $lead_id,
                            'name'           => $lead_name,
                            'service_name'   => $_service['value'],
                            'price_managers' => $_price_managers,
                            'price_partners' => $_price_partners,
                            'id_partners'    => $user_id,
                            'price_company'  => $_price_company
                        );
                    }
				} else if ($service_id == $enums_osago || $service_id == $enums_making) {
                    foreach ($arFieldData['data'] as $_data) {
                        if ($_data['service'] == $service_id) {
                            $arUserPercent = $_data['partners']; break;
                        }
                    }
                    if (is_array($arUserPercent)) {
                    	if (!empty($design_manager_id) && !empty($arUserPercent['on_managers'][$user_id])) {
                    		$_price_managers = round($_price * (int) $arUserPercent['on_managers'][$user_id]['managers'] / 100, 2);
                    		$_price_partners = round($_price * (int) $arUserPercent['on_managers'][$user_id]['value'] / 100, 2);
                    		$_price_company = round($_price * (int) $arUserPercent['on_managers'][$user_id]['company'] / 100, 2);
                    		if ($service_id == $enums_osago) {
                                $type = "osago";
                            } else {
                                $type = "making";
                            }
                            $arData['managers']['list'][$design_manager_id]['ar_service_partners'][$type]['price'] += $_price_managers;
                            $arData['managers']['list'][$design_manager_id]['ar_service_partners'][$type]['items'][] = array(
                                'id'             => $lead_id,
                                'name'           => $lead_name,
                                'service_name'   => $_service['value'],
                                'price_managers' => $_price_managers,
                                'price_partners' => $_price_partners,
                                'id_partners'    => $user_id,
                                'price_company'  => $_price_company
                            );
                    	} else if (empty($design_manager_id) && !empty($arUserPercent['no_managers'][$user_id])) {
                    		$_price_partners = round($_price * (int) $arUserPercent['no_managers'][$user_id]['value'] / 100, 2);
                    		$_price_company = round($_price * (int) $arUserPercent['no_managers'][$user_id]['company'] / 100, 2);
                    	}
                    }
                } else if ($service_id == $enums_diagnostic_cards) {
                    foreach ($arFieldData['data'] as $_data) {
                        if ($_data['service'] == $service_id) {
                            $arUserPercent = $_data['partners']; break;
                        }
                    }
                    if (is_array($arUserPercent) && !empty($arUserPercent[$user_id])) {
                    	if ($_price > (int) $arUserPercent[$user_id]['company']) {
                    		$_price_company = (int) $arUserPercent[$user_id]['company'];
	                    	$_price_partners = $_price - $_price_company;
                    	} else {
                    		$_price_partners = $_price;
                    	}
                    }
                } else {
                    $_price_partners = round($_price / 2, 2);
                    if (!empty($design_manager_id)) {
                        $_price_managers = $_price_partners;
                        $arData['managers']['list'][$design_manager_id]['ar_service_partners']['other']['price'] += $_price_managers;
                        $arData['managers']['list'][$design_manager_id]['ar_service_partners']['other']['items'][] = array(
                            'id'             => $lead_id,
                            'name'           => $lead_name,
                            'service_name'   => $_service['value'],
                            'price_managers' => $_price_managers,
                            'price_partners' => $_price_partners,
                            'id_partners'    => $user_id,
                            'price_company'  => 0
                        );
                    } else {
                        $_price_company = $_price_partners;
                    }
                }

                $arData['partners']['company_price'] += $_price_company;
                $arData['partners']['list'][$user_id]['price'] += $_price_partners;
                $arData['partners']['list'][$user_id]['price_company'] += $_price_company;
                $arData['partners']['list'][$user_id]['ar_service'][$service_id]['price_partners'] += $_price_partners;
                $arData['partners']['list'][$user_id]['ar_service'][$service_id]['price_managers'] += $_price_managers;
                $arData['partners']['list'][$user_id]['ar_service'][$service_id]['price_company']  += $_price_company;

                $arData['partners']['list'][$user_id]['ar_service'][$service_id]['items'][] = array(
                    'id'                => $lead_id,
                    'name'              => $lead_name,
                    'price_partners'    => $_price_partners,
                    'price_managers'    => $_price_managers,
                    'id_managers'       => $design_manager_id,
                    'price_company'     => $_price_company
                );
    		}

    	} else if ($type_user == "managers") {

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

                $service_id = $_service['enum'];
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

                $arData['managers']['list'][$user_id]['ar_service'][$service_id]['items'][] = array(
                    'id'    => $lead_id,
                    'name'  => $lead_name,
                    'price' => $_price
                );
    		}
    	}
    }

    #delete superfluous
    if (!$is_admin) {
        foreach ($arData['partners']['list'] as $__user_id => $value) {
            if ($select_user != $__user_id) {
                unset($arData['partners']['list'][$__user_id]);
            }
        }
        foreach ($arData['managers']['list'] as $__user_id => $value) {
            if ($select_user != $__user_id) {
                unset($arData['managers']['list'][$__user_id]);
            }
        }
    }

    #init managers
    foreach ($arData['managers']['list'] as $id => $ar_data) {
    	if (!isset($arFieldData['plane'][$id])) {
    		continue;
    	}
    	$percentage_completed = 0;
        $price_diagnostic_cards = ( isset($ar_data['ar_service'][$enums_diagnostic_cards]) ? $ar_data['ar_service'][$enums_diagnostic_cards]['price'] : 0 );
        $_price_all = $ar_data['price'] + $ar_data['ar_service_partners']['osago']['price'] + $ar_data['ar_service_partners']['making']['price'] + $ar_data['ar_service_partners']['other']['price'];

    	$done = round($_price_all * 100 / (int) $arFieldData['plane'][$id], 2);
    	if ($done >= 120) {
			$percentage_completed = 0.5;
		} else if ($done >= 100) {
            $percentage_completed = 0.45;
        } else if ($done >= 80) {
			$percentage_completed = 0.3;
		} else {
			$percentage_completed = 0.25;
		}
        $_price_all += $ar_data['ar_service_partners']['kasko']['price'];
        $s = $_price_all - $price_diagnostic_cards;
		$wage = round($s * $percentage_completed, 2);
		$arData['managers']['company_price'] += $_price_all - $wage - ($price_diagnostic_cards * $percentage_completed);
		$arData['managers']['list'][$id]['plane'] = $arFieldData['plane'][$id];
		$arData['managers']['list'][$id]['percent'] = $done;
		$arData['managers']['list'][$id]['wage'] = $wage;
    }

    echo json_encode(array('status' => 'ok', 'ar_data' => $arData));

?>