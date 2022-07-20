<?php

	if (empty($_POST["users"])) exit;

	$current = file_get_contents("managers.json");
    $arSelectManager = json_decode($current, true);
    $arSelectManager["managers"] = $_POST["users"];

    $current = json_encode($arSelectManager);
    file_put_contents("managers.json", $current);

?>