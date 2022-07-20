<?

	if (empty($_POST['data'])) exit;

	$arData = json_decode($_POST['data'], true);
	
	$arManagers = array();
	$arManagersOn = array();
	foreach ($arData as $id => $value) {
		$ar_juxtapose_sip = array(
			'id' 	=> $id,
			'sip'	=> $value['sip']
		);;
		$arManagers[] = $ar_juxtapose_sip;
		if ($value['distribution']) {
			$arManagersOn[] = $ar_juxtapose_sip;
		}
	}

	$dir = __DIR__ . "/managers.json";
	$current = file_get_contents($dir);
    $arDataFile = json_decode($current, true);
    $arDataFile['managers'] = $arManagersOn;
    $arDataFile['juxtapose_sip'] = $arManagers;
    file_put_contents($dir, json_encode($arDataFile));

?>