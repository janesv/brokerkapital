<?php

class Amo {

    private $apikeySMS = "DAEB306B-2BFA-45A1-8D92-140A1E4B595E";
    private $smsru;

    function __construct() {
        require_once __DIR__ . "/../auth_amo.php";
        $this->smsru = new SMSRU($this->apikeySMS);
    }

    private function error_append($text) {
        file_put_contents(__DIR__ . "/error.log",
            var_export($text, 1)."\n".date("d-m-Y H:i:s")."\n---\n\n",
        FILE_APPEND);
    }

    public function init($data) {

        $lead_id = "";
        $phone = $data["phone"];
        if (iconv_strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        $response = OctoLab\amoCRM\dao\Contact::get(array("query" => $phone));
        $response = json_decode($response->getBody(), true);

        if (!empty($response["_embedded"]["items"])) {

            $contact = $response["_embedded"]["items"][0];
            
            $data["contacts_id"] = $contact["id"];
            $data["user_id"] = $contact["responsible_user_id"];
            $data["name"] = "Повторное обращение: " . $data["name"];

            if (!empty($contact["leads"])) {
                $response = OctoLab\amoCRM\dao\Lead::get(array("id" => $contact["leads"]["id"]));
                $response = json_decode($response->getBody(), true);
                if (!empty($response["_embedded"]["items"])) {
                    foreach ($response["_embedded"]["items"] as $leads) {
                        if ($leads["status_id"] != 142 && $leads["status_id"] != 143) {
                            $lead_id = $leads["id"];
                        }
                    }
                }
            }

            if (!empty($lead_id)) {
                $this->addNote(array(
                    "lead_id" => $lead_id,
                    "text"    => "Повторное обращение"
                ));
            }
            
        } else {
            $data["user_id"] = $this->distribution();
            $data["contacts_id"] = $this->addContact($data);
            $this->sendSMS($data["phone"], $data["name"]);
        }

        if (empty($lead_id)) {
            $lead_id = $this->addLead($data);
        }

        return $lead_id;
    }

    private function distribution() {

        $dir = __DIR__ . "/distribution/managers.json";
        $current = file_get_contents($dir);
        if (!$current) return false;
        
        $arSelectManager = json_decode($current, true);
        $count = $arSelectManager["selection"];
        $_count = count($arSelectManager["managers"]);

        if ($_count == 0) return false;

        if ($count >= $_count) $count = 0;

        $managerId = $arSelectManager["managers"][$count];

        $arSelectManager["selection"] = ++$count;
        $current = json_encode($arSelectManager);
        file_put_contents($dir, $current);

        return $managerId;
    }

    private function sendSMS($phone, $name) {
        $header = "Спасибо за вашу заявку!";
        if (!empty($name)) {
            $header = $name . ", спасибо за вашу заявку!";
        }
        $data = new stdClass();
        $data->to = $phone;
        $data->text = $header . " В ближайшее время менеджер с Вами свяжется и ответит на все ваши вопросы. С уважением, страховое агентство \"КАПИТАЛ\" тел.: 300-33-12, г. Воронеж, ул. Донбасская, д. 44, офис 8 (3 этаж).";
        $sms = $this->smsru->send_one($data);
        if ($sms->status != "OK") {
            $this->error_append("sms.ru: Код ошибки: $sms->status_code.\nТекст ошибки: $sms->status_text.\nтелефон $phone");
        }
    }

    private function addContact($data) {

        $raw_response = OctoLab\amoCRM\dao\Contact::set(array(
            "add" => array(
                array(
                    "name" => $data["name"],
                    "responsible_user_id" => $data["user_id"],
                    "custom_fields" => array(
                        array(
                            "id" => 163591,
                            "values" => array(
                                array(
                                    "value" => $data["phone"],
                                    "enum" => "WORK"
                                )
                            )
                        ),
                        array(
                            "id" => 163593,
                            "values" => array(
                                array(
                                    "value" => $data["email"],
                                    "enum" => "WORK"
                                )
                            )
                        )
                    )
                )
            )));

        $response = json_decode($raw_response->getBody(), true);
        $response = $response["_embedded"];

        if (isset($response["errors"])) {
            $this->error_append($response["errors"]);
            return false;
        }

        $contact_id = $response["items"][0]["id"];

        return $contact_id;
    }

    private function addLead($data) {
        
        $raw_response = OctoLab\amoCRM\dao\Lead::set(array(
                        "add" => array(
                            array(
                                "name" => $data["name"],
                                "contacts_id" => $data["contacts_id"],
                                "pipeline_id" => 758578,
                                "responsible_user_id" => $data["user_id"],
                                "custom_fields" => array(
                                    array(
                                        "id" => 182869,
                                        "values" => array(
                                            array(
                                                "value" => 381065
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 376693,
                                        "values" => array(
                                            $data["service_type"]
                                        )
                                    ),
                                    array(
                                        "id" => 216551,
                                        "values" => array(
                                            array(
                                                "value" => $data["type_auto"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 216607,
                                        "values" => array(
                                            array(
                                                "value" => $data["power_engine"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 216577,
                                        "values" => array(
                                            array(
                                                "value" => $data["region"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 216579,
                                        "values" => array(
                                            array(
                                                "value" => $data["period_use_year"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 216829,
                                        "values" => array(
                                            array(
                                                "value" => $data["country"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 216833,
                                        "values" => array(
                                            array(
                                                "value" => $data["from_date"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 216835,
                                        "values" => array(
                                            array(
                                                "value" => $data["to_date"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 216841,
                                        "values" => array(
                                            array(
                                                "value" => $data["count_people"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 362495,
                                        "values" => array(
                                            array(
                                                "value" => $data["age_1"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 374331,
                                        "values" => array(
                                            array(
                                                "value" => $data["age_2"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 374335,
                                        "values" => array(
                                            array(
                                                "value" => $data["age_3"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 374337,
                                        "values" => array(
                                            array(
                                                "value" => $data["age_4"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 374339,
                                        "values" => array(
                                            array(
                                                "value" => $data["age_5"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 374345,
                                        "values" => array(
                                            array(
                                                "value" => $data["age_6"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 182877,
                                        "values" => array(
                                            array(
                                                "value" => $data["reg_num"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 182879,
                                        "values" => array(
                                            array(
                                                "value" => $data["car_year"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 205147,
                                        "values" => array(
                                            array(
                                                "value" => $data["car_mark"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 205185,
                                        "values" => array(
                                            array(
                                                "value" => $data["car_cost"]
                                            )
                                        )
                                    ),
                                    array(
                                        "id" => 277581,
                                        "values" => array(
                                            array(
                                                "value" => $data["driver_list"]
                                            )
                                        )
                                    )
                                )
                            )
                        )));

        $response = json_decode($raw_response->getBody(), true);
        $response = $response["_embedded"];
        console.log("RESPONSE: ", $response);

        if (isset($response["errors"])) {
            $this->error_append($response["errors"]);
            return false;
        }

        $lead_id = $response["items"][0]["id"];

        return $lead_id;
    }

    public function addNote($data) {
        $raw_response = OctoLab\amoCRM\dao\Note::set(array(
                        "add" => array(
                            array(
                                "element_id" => $data["lead_id"],
                                "element_type" => 2,
                                "note_type" => 4,
                                "text" => $data["text"]
                            )
                        )
                    ));

        $response = json_decode($raw_response->getBody(), true);
        $response = $response["_embedded"];

        if (isset($response["errors"])) {
            $this->error_append($response["errors"]);
            return false;
        }
    }
}

?>