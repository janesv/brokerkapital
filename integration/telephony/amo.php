<?php

class Amo {

    function __construct() {
        require_once dirname(dirname(__FILE__)) . "/auth_amo.php";
    }

    private function error_append($text) {
        file_put_contents(__DIR__ . "/error.log",
            var_export($text, 1)."\n".date('d-m-Y H:i:s')."\n---\n\n",
        FILE_APPEND);
    }

    public function init($ar_data) {

        $phone = $ar_data['phone'];
        if (iconv_strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        $no_lead = true;

        $response = OctoLab\amoCRM\dao\Contact::get(array('query' => $phone));
        $response = json_decode($response->getBody(), true);

        if (isset($response['_embedded']) && !empty($response['_embedded']['items'])) {

            $contact = $response['_embedded']['items'][0];
            
            $ar_data['contacts_id'] = $contact["id"];
            $ar_data['user_id'] = $contact['responsible_user_id'];
            $ar_data['name'] = "Повторное обращение: " . $contact['name'];

            if (!empty($contact["leads"])) {
                $response = OctoLab\amoCRM\dao\Lead::get(array('id' => $contact["leads"]['id']));
                $response = json_decode($response->getBody(), true);
                if (isset($response['_embedded']) && !empty($response['_embedded']['items'])) {
                    foreach ($response['_embedded']['items'] as $leads) {
                        if ($leads['status_id'] != "142" && $leads['status_id'] != "143") {
                            $no_lead = false;
                        }
                    }
                }
            }            
            
        } else {
            $ar_distribution = $this->distribution();
            if (is_array($ar_distribution) && isset($ar_distribution['id'])) {
                $ar_data['user_id'] = $ar_distribution['id'];
                $ar_data['sip'] = $ar_distribution['sip'];
                $ar_data['contacts_id'] = $this->new_contact($ar_data);
            }
        }

        if (isset($ar_data['contacts_id']) && $no_lead) {

        	$group_id = $this->user_get($ar_data['user_id']);

        	if ($group_id == 161686) {
        		$ar_data['pipeline_id'] = 1040761;
        	} else {
        		$ar_data['pipeline_id'] = 758578;
        	}

            $this->new_lead($ar_data);
        }

        return ( isset($ar_data['sip']) ? $ar_data['sip'] : false );
    }

    private function distribution() {

        $transfer = ( isset($_GET['transfer']) ? preg_replace("/[^0-9]/", '', $_GET['transfer']) : "" );

        $dir = __DIR__ . "/distribution/managers.json";
        $current = file_get_contents($dir);
        $arData = json_decode($current, true);

        $ar_juxtapose = array();
        
        if (iconv_strlen($transfer) == 3) {
            foreach ($arData['juxtapose_sip'] as $value) {
                if ($value['sip'] == $transfer) {
                    $ar_juxtapose = $value;
                }
            }
        } else {
            $count = $arData['selection'];
            $_count = count($arData['managers']);
            if ($_count == 0) return false;
            if ($count >= $_count) $count = 0;
            $arData['selection'] = $count + 1;
            file_put_contents($dir, json_encode($arData));
            $ar_juxtapose = $arData['managers'][$count];
        }
        
        return $ar_juxtapose;

    }

    private function user_get($user_id) {
		$response = OctoLab\amoCRM\dao\Account::current(array('with' => 'users'));
	    $account = json_decode($response->getBody(), true);

	    if ($account && $account['_embedded'] && $account['_embedded']['users']) {
			$group_id = "";
			foreach ($account['_embedded']['users'] as $user) {
				if ($user['id'] == $user_id) {
					$group_id = $user['group_id'];
					break;
				}
			}
	        return $group_id;
	    }
	}

    private function new_contact($data) {

        $raw_response = OctoLab\amoCRM\dao\Contact::set(array(
                        'add' => array(
                            array(
                                'name' => $data['name'],
                                'responsible_user_id' => $data['user_id'],
                                'custom_fields' => array(
                                    array(
                                        'id' => '163591',
                                        'values' => array(
                                            array(
                                                "value" => $data['phone'],
                                                "enum" => "WORK"
                                            )
                                        )
                                    )
                                )
                            )
                        )));

        $response = json_decode($raw_response->getBody(), true);
        $response = $response['_embedded'];

        if (isset($response['errors'])) {
            $this->error_append($response['errors']);
            return false;
        }

        $contact_id = $response['items'][0]['id'];

        return $contact_id;
    }

    private function new_lead($data) {
        
        $raw_response = OctoLab\amoCRM\dao\Lead::set(array(
                        'add' => array(
                            array(
                                'name' => $data['name'],
                                'contacts_id' => $data['contacts_id'],
                                'pipeline_id' => $data['pipeline_id'],
                                'responsible_user_id' => $data['user_id'],
                                'custom_fields' => array(
                                    array(
                                        'id' => '182869',
                                        'values' => array(
                                            array(
                                                "value" => '381057'
                                            )
                                        )
                                    )
                                )
                            )
                        )));

        $response = json_decode($raw_response->getBody(), true);
        $response = $response['_embedded'];

        if (isset($response['errors'])) {
            $this->error_append($response['errors']);
            return false;
        }

        $lead_id = $response['items'][0]['id'];

        return $lead_id;
    }
}

?>