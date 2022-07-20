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

		        $ids_tmp = array_slice($ids, 0, 300);
		        $ids = array_slice($ids, 300);

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

		private function getInsuranceCompanyPosition($arr, $search) {
			$position = -1;
	        foreach ($arr as $key => $values) {
	        	if ($values["name"] == $search) {
	        		$position = $key;
	        		break;
	        	}
	        }
	        return $position;
		}

		private function appendData($arrResult, $insurance_company, $service_id, $service_price, $lead_id, $lead_name) {
			$position = $this->getInsuranceCompanyPosition($arrResult, $insurance_company);

        	if ($position === -1) {
            	$position = count($arrResult);
            	$arrResult[] = array(
            		"name" => $insurance_company,
            		"services" => array()
            	);
            }

            if (!isset($arrResult[$position]["services"][$service_id])) {
            	$arrResult[$position]["services"][$service_id] = array(
            		"id" => $service_id,
            		"price" => 0,
        			"leads" => array()
            	);
            }

            $arrResult[$position]["services"][$service_id]["price"] += $service_price;
        	$arrResult[$position]["services"][$service_id]["leads"][$lead_id] = array(
            	"id" => $lead_id,
            	"name" => $lead_name
            );

            return $arrResult;
		}

		public function getInsuranceCompany($arLead) {

			$arLeadResponse = array(
				"policy_cost" => array()
        	);

			foreach ($arLead as $lead) {
		    	
		    	$lead_id = $lead["id"];
		    	$lead_name = $lead["name"];
		    	$custom_fields = $lead["custom_fields"];
		        $services = $this->getServiceEnum($custom_fields);

		        foreach ($services as $service) {

		        	$service_id = $service["enum"];

		            if (!isset($this->params[$service_id]["insurance_company"]))
		                continue;

		            $arFieldId = $this->params[$service_id];

		            $arFieldCell = array();
		            foreach ($arFieldId as $key => $field_id) {
		            	$value = 0;
		            	if ($key == "insurance_company") $value = "";
		            	foreach ($custom_fields as $fields) {
			            	if ($fields["id"] == $field_id) {
		            			$value = $fields["values"][0]["value"];
		            			break;
		            		}
			            }
		            	$arFieldCell[$key] = $value;
		            }

		            if (empty($arFieldCell["insurance_company"]))
		            	continue;

		            $insurance_company = $arFieldCell["insurance_company"];

		            if ($arFieldCell["policy_cost"] > 0) {

		            	$arLeadResponse["policy_cost"] = $this->appendData($arLeadResponse["policy_cost"], $insurance_company, $service_id, $arFieldCell["policy_cost"], $lead_id, $lead_name);
		            }

		        }
		    }

		    return $arLeadResponse;
		}
	}

?>