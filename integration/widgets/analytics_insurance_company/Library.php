<?php

	class Amo {

		private $params = array();
		private $serviceFieldId = 376693;
		
		function __construct() {
			require_once __DIR__ . "/../../auth_amo.php";
			$file = __DIR__ . "/../../service/params.json";
		    $current = file_get_contents($file);
		    $this->params = json_decode($current, true);
		}

		public function getLeadByIds($ids) {
			$arElement = array();
			
		    while (count($ids) > 0) {

		        $ids_tmp = array_slice($ids, 0, 249);
		        $ids = array_slice($ids, 249);

		        $response = OctoLab\amoCRM\dao\Lead::get(array('id' => $ids_tmp));
		        $response = json_decode($response->getBody(), true);

		        if (!empty($response['_embedded']['items'])) {
		        	$arElement = array_merge($arElement, $response['_embedded']['items']);
		        }

		        if (!empty($ids)) {
		            sleep(1);
		        }
		    }
		    
		    return $arElement;
		}

		private function getServiceEnum($fields) {
			$services = array();
	        foreach ($fields as $values) {
	            if ($values["id"] == $this->serviceFieldId) {
	                $services = $values["values"];
	                break;
	            }
	        }
	        return $services;
		}

		private function sortByCustomFields($array, $on) {

		    $new_array = array();
		    $sortable_array = array();

		    if (count($array) > 0) {

		        foreach ($array as $k => $v) {
		        	$sortable_array[$k] = ( isset($v["custom_fields"][$on]["value"]) ? $v["custom_fields"][$on]["value"] : "" );
		        }

		        arsort($sortable_array);
		        foreach($sortable_array as $k => $v) {
		            $new_array[$k] = $array[$k];
		        }
		    }

		    return array_values($new_array);
		}

		public function getInsuranceCompany($arLead, $receivedPayment) {

			$arLeadResponse = array();

		    foreach ($arLead as $lead) {
		    	
		        $services = $this->getServiceEnum($lead["custom_fields"]);

		        foreach ($services as $service) {

		            if (!isset($this->params[$service["enum"]]))
		                continue;

		            $arFieldId = $this->params[$service["enum"]];
		            $arFieldCell = array(
		                "lead_id" => $lead["id"],
		                "lead_name" => $lead["name"],
		                "service_name" => $service["value"],
		                "custom_fields" => array()
		            );

		            foreach ($arFieldId as $key => $field_id) {
		            	$value = 0;
		            	if ($key == "insurance_company") $value = "";
		            	foreach ($lead["custom_fields"] as $fields) {
			            	if ($fields["id"] == $field_id) {
		            			$value = $fields["values"][0]["value"];
		            			break;
		            		}
			            }
		            	$arFieldCell["custom_fields"][$key] = array(
		            		"id" => $field_id,
		            		"value" => $value
		            	);
		            }

		            if ($receivedPayment == 100) {
		                $arLeadResponse[] = $arFieldCell;
		            } else if (isset($arFieldCell["custom_fields"]["received_payment"]["value"]) && $receivedPayment == $arFieldCell["custom_fields"]["received_payment"]["value"]) {
		            	$arLeadResponse[] = $arFieldCell;
		            }
		        }
		    }

		    $arLeadResponse = $this->sortByCustomFields($arLeadResponse, "insurance_company");

		    if ($receivedPayment == 100) {
		    	$arr_received_payment = array();
		    	foreach ($arLeadResponse as $key => $value) {
		    		if (isset($value["custom_fields"]["received_payment"]["value"]) && $value["custom_fields"]["received_payment"]["value"] == 1) {
		    			$arr_received_payment[] = $value;
		    			unset($arLeadResponse[$key]);
		    		}
		    	}
		    	$arLeadResponse = array_merge($arLeadResponse, $arr_received_payment);
		    	$arr_received_payment = NULL;
		    }

		    return $arLeadResponse;
		}

	}

?>