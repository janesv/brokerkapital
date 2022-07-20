<?php

	if (isset($_SERVER["HTTP_ORIGIN"])) {
		header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
	}

	if (empty($_POST["phone"])) exit;

	require_once __DIR__ . "/../LibSmsru/sms.ru.php";
	require_once __DIR__ . "/amo.php";

	$params = array();

	$params["name"] = ( !empty($_POST["name"]) ? trim($_POST["name"]) : "Имя не указано" );
	$params["phone"] = preg_replace("/[^0-9]/", "", $_POST["phone"]);
	$params["email"] = ( isset($_POST["email"]) ? trim($_POST["email"]) : "" );

	$params["service_type"] = "";
	$params["power_engine"] = "";
	$params["type_auto"] = "";
	$params["driver_list"] = "";

	$params["region"] = ( isset($_POST["region"]) ? trim($_POST["region"]) : "" );
	$params["period_use_year"] = ( isset($_POST["period_use_year"]) ? trim($_POST["period_use_year"]) : "" );
	$params["count_people"] = ( isset($_POST["count_people"]) ? trim($_POST["count_people"]) : "" );
	$params["age_1"] = ( isset($_POST["age_1"]) ? trim($_POST["age_1"]) : "" );
	$params["age_2"] = ( isset($_POST["age_2"]) ? trim($_POST["age_2"]) : "" );
	$params["age_3"] = ( isset($_POST["age_3"]) ? trim($_POST["age_3"]) : "" );
	$params["age_4"] = ( isset($_POST["age_4"]) ? trim($_POST["age_4"]) : "" );
	$params["age_5"] = ( isset($_POST["age_5"]) ? trim($_POST["age_5"]) : "" );
	$params["age_6"] = ( isset($_POST["age_6"]) ? trim($_POST["age_6"]) : "" );
	$params["reg_num"] = ( isset($_POST["reg_num"]) ? trim($_POST["reg_num"]) : "" );
	$params["car_year"] = ( isset($_POST["car_year"]) ? trim($_POST["car_year"]) : "" );
	$params["car_mark"] = ( isset($_POST["car_mark"]) ? trim($_POST["car_mark"]) : "" );
	$params["car_cost"] = ( isset($_POST["car_cost"]) ? trim($_POST["car_cost"]) : "" );
	$params["country"] = ( isset($_POST["country"]) ? trim($_POST["country"]) : "" );
	$params["from_date"] = ( isset($_POST["from_date"]) ? trim($_POST["from_date"]) : "" );
	$params["to_date"] = ( isset($_POST["to_date"]) ? trim($_POST["to_date"]) : "" );

	if (isset($_POST["form_name"])) {
		$form_name = trim($_POST["form_name"]);
		switch ($form_name) {
			case "kasko":
				$params["service_type"] = 772963;
				break;
			case "osago":
				$params["service_type"] = 772959;
				break;
			case "liability-insurance":
				if (isset($_POST["variant_insurance"])) {
					$variant_insurance = trim($_POST["variant_insurance"]);
					if ($variant_insurance == "Страхование профессиональной ответственности") {
						$params["service_type"] = 772983;
					} else if ($variant_insurance == "Страхование ответственности перед соседями") {
						$params["service_type"] = 772985;
					} else if ($variant_insurance == "Страхование ответственности ОПО") {
						$params["service_type"] = 772987;
					} else if ($variant_insurance == "Страхование ответственности застройщиков перед дольщиками") {
						$params["service_type"] = 772989;
					}
				}
				break;
			case "property-insurance":
				if (isset($_POST["object"])) {
					$object = trim($_POST["object"]);
					if ($object == "Квартира") {
						$params["service_type"] = 772969;
					} else if ($object == "Дом") {
						$params["service_type"] = 772971;
					} else if ($object == "Ипотека") {
						$params["service_type"] = 772973;
					} else if ($object == "Коммерческая недвижимость") {
						$params["service_type"] = 772975;
					}
				}
				break;
			case "tourism-insurance":
				$params["service_type"] = 772977;
				break;
		}
	}

	if (isset($_POST["power_engine"])) {
		$power_engine = $_POST["power_engine"];
		if (is_numeric($power_engine)) {
			if ($power_engine < 70) {
	            $params["power_engine"] = 451865;
	        } else if ($power_engine > 70 && $power_engine <= 100) {
	            $params["power_engine"] = 451867;
	        } else if ($power_engine > 100 && $power_engine <= 120) {
	            $params["power_engine"] = 451869;
	        } else if ($power_engine > 120 && $power_engine <= 150) {
	            $params["power_engine"] = 451871;
	        } else if ($power_engine > 150) {
	            $params["power_engine"] = 451873;
	        }
		} else {
			switch ($power_engine) {
				case "свыше 50 до 70 включительно":
	                $params["power_engine"] = 451865;
	                break;
	            case "свыше 70 до 100 включительно":
	                $params["power_engine"] = 451867;
	                break;
	            case "свыше 100 до 120 включительно":
	                $params["power_engine"] = 451869;
	                break;
	            case "свыше 120 до 150 включительно":
	                $params["power_engine"] = 451871;
	                break;
	            case "свыше 150":
	                $params["power_engine"] = 451873;
	                break;
			}
		}
	}

	if (isset($_POST["type_auto"])) {
		$type_auto = $_POST["type_auto"];
		switch ($type_auto) {
			case "Легковые автомобили":
                $params["type_auto"] = 451695;
                break;
            case "Легковые автомобили, используемые в качестве такси":
                $params["type_auto"] = 451697;
                break;
            case "Автобусы с числом пассажирских мест до 16 включительно":
                $params["type_auto"] = 451707;
                break;
            case "Автобусы с числом пассажирских мест более 16":
                $params["type_auto"] = 451709;
                break;
            case "Грузовые автомобили с разрешенной максимальной массой 16 тонн и менее":
                $params["type_auto"] = 451701;
                break;
            case "Грузовые автомобили с разрешенной максимальной массой более 16 тонн":
                $params["type_auto"] = 451703;
                break;
            case "Мотоциклы, мопеды и легкие квадрициклы":
                $params["type_auto"] = 451705;
                break;
            case "Троллейбусы":
                $params["type_auto"] = 451713;
                break;
            case "Трамваи":
                $params["type_auto"] = 451715;
                break;
            case "Тракторы, самоходные дорожно-строит., иные машины, кроме ТС, не имеющих колесных движителей":
                $params["type_auto"] = 451717;
                break;
		}
	}

	if (isset($_POST["age"]) && is_array($_POST["age"])) {
		$ages = $_POST["age"];
		$count = 1;
		foreach ($ages as $age) {
			$params["age_" . $count] = $age;
			$count++;
		}
		$count_people_enums = array(770591, 770593, 770595, 770597, 770599, 770601);
		$count_people = count($ages) - 1;
		if (isset($count_people_enums[$count_people])) {
			$params["count_people"] = $count_people_enums[$count_people];
		}
	}

	$arDriver = array();
	for ($i=0; $i < 6; $i++) { 
		if (isset($_POST["age_$i"]) && isset($_POST["driver_experience_$i"])) {
			$age = preg_replace("/[^0-9-]/", "", $_POST["age_$i"]);
			$experience = preg_replace("/[^0-9-]/", "", $_POST["driver_experience_$i"]);
			$age = explode("-", $age)[0];
			$experience = explode("-", $experience)[0];
			$gender = ( isset($_POST["gender_$i"]) ? $_POST["gender_$i"] : "" );
			$arDriver[] = array(
	            "floor_man" => ($gender == "мужчина" ? true : false),
	            "floor_her" => ($gender == "женщина" ? true : false),
	            "married" => false,
	            "children" => false,
	            "age" => $age,
	            "experience" => $experience
	        );
		}
	}

	if (!empty($arDriver)) {
        $params["driver_list"] = json_encode($arDriver);
    }

	$amo = new Amo();
	$lead_id = $amo->init($params);

	if (!empty($lead_id)) {
		if (!empty($_POST["msg"])) {
			$amo->addNote(array(
				"lead_id" => $lead_id,
				"text"	  => trim($_POST["msg"])
			));
		}
		echo json_encode(array("status" => "ok"));
	}

?>