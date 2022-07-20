<?php

	if (empty($_POST['date']['from']) || empty($_POST['date']['to'])) exit;

	$dates = strtotime(date("Y-m-01", strtotime($_POST['date']['from'])));

	$dir = __DIR__ . "/data.json";
	$current = file_get_contents($dir);
	$arSave = json_decode($current, true);
	
	$arFieldData = array();
	
	if (isset($_GET['get'])) {

		if (!isset($arSave[$dates])) {
			if ($dates == strtotime(date("Y-m-01"))) {
				$end = end($arSave);
				if ($end) {
					$arSave[$dates] = $arFieldData = $end;
					file_put_contents($dir, json_encode($arSave));
				}
			}
		} else {
			$arFieldData = $arSave[$dates];
		}

	} else if (!empty($_POST['add'])) {
		$arSave[$dates] = json_decode($_POST['add'], true);
		file_put_contents($dir, json_encode($arSave));
	}

	if (!isset($_POST['user_id'])) {
		echo json_encode($arFieldData);
	}

?>