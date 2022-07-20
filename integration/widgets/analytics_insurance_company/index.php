<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . "/Library.php";

    $arResponse = array(
        "status" => "error",
        "response" => array()
    );

    $amo = new Amo();

	if (!empty($_POST["lead_ids"]) && isset($_POST["received_payment"])) {

        $receivedPayment = trim($_POST["received_payment"]);
        $arElementId = $_POST["lead_ids"];

        $leads = $amo->getLeadByIds($arElementId);
        if (!empty($leads)) {
            $arResponse["status"] = "ok";
            $arResponse["response"]["items"] = $amo->getInsuranceCompany($leads, $receivedPayment);
        }
    }

    echo json_encode($arResponse);

?>