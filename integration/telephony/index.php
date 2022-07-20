<?php

    if (empty($_POST["caller_number"])) exit;

    require_once __DIR__ . '/amo.php';

    $trunk          = trim($_POST["trunk"]);
    $caller_name    = trim($_POST["caller_name"]);
    $caller_number  = trim($_POST["caller_number"]);

    $call_name = $caller_number . " (" . $trunk . ( $caller_name != $caller_number ? " - " . $caller_name : "" ) . ")";
    $caller_number  = preg_replace("/[^0-9]/", '', $caller_number);

    # init amo
    $amo = new Amo();
    $sip = $amo->init(array(
        "name"      => "Звонок от " . $call_name,
        "phone"     => $caller_number
    ));

    if ($sip) {

        echo('

        -----------------------------------------------------------

        transfer: "' . $sip . '"

        -----------------------------------------------------------

        ');

    }

?>