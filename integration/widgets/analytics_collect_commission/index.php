<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . "/Library.php";

    $arResponse = array(
        "status" => "error",
        "response" => array()
    );

	if (!empty($_POST["lead_ids"])) {

        $arElementId = $_POST["lead_ids"];

        $amo = new Amo();
        $leads = $amo->getLeadByIds($arElementId);
        if (!empty($leads)) {
            $arResponse["status"] = "ok";
            $arResponse["response"]["items"] = $amo->getInsuranceCompany($leads);
        }
    }

    echo json_encode($arResponse);

?>