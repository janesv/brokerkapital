<?php
require_once 'src/init.php';
if (OPTIONS_REQUEST)
    exit(0);

require_once 'settings.php';
require_once 'src/mail.classes.php';

class FnCalledAuthSettings implements AuthSettings
{
    /**
     * @param $mail PHPMailer
     */
    function setUpAuth($mail)
    {
        if (function_exists('IsSetAuth'))
            ItSetAuth($mail);
        else
            $mail->isMail();
    }
}

/*get data for amoCRM*/
if (!empty($_POST['ar_data'])) {

    $_data = json_decode($_POST['ar_data'], true);
    unset($_POST['ar_data']);

    $all = $_data['all'];
    $car = $_data['auto'];
    $arData = array(
        'kasko' => array(
            'name' => $all['client_name'],
            'telephone' => $all['client_phone'],
            'reg_num' => $all['id'],
            'car_year' => $all['car_manufacturing_year'],
            'is_legal_entity' => ($all['is_legal_entity'] ? 427919 : 427917),
            'insurance_duration' => ($all['insurance_duration'] == "82" ? 653049 : ""),
            'calculation_at_date' => $all['calculation_at_date'],
            'car_mark' => $car['carMark']['title'],
            'car_model_group' => $car['carModelGroup']['title'],
            'car_model' => $car['carModel']['title'],
            'car_cost' => $all['car_cost'],
            'power_engine' => "",
            'mileage' => $all['mileage'],
            'driver' => ""
        )
    );

    if ($all['engine_power'] < 71) {
        $arData['kasko']['power_engine'] = 451865;
    }else if ($all['engine_power'] < 101) {
        $arData['kasko']['power_engine'] = 451867;
    }else if ($all['engine_power'] < 121) {
        $arData['kasko']['power_engine'] = 451869;
    }else if ($all['engine_power'] < 151) {
        $arData['kasko']['power_engine'] = 451871;
    }else{
        $arData['kasko']['power_engine'] = 451873;
    }

    $arDriver = array();
    foreach ($all['driver_set'] as $driver) {
        $arDriver[] = array(
            "floor_man" => ($driver['gender'] == "M" ? true : false),
            "floor_her" => ($driver['gender'] == "F" ? true : false),
            "married" => $driver['is_married'],
            "children" => $driver['has_children'],
            "age" => $driver['age'],
            "experience" => $driver['expirience']
        );
    }
    if (!empty($arDriver)) {
        $arData['kasko']['driver'] = json_encode($arDriver);
    }

    require $_SERVER["DOCUMENT_ROOT"] . "/integration/site/amo.php";
    Amo::init($arData);

}
/*end get data for amoCRM*/


$authSettings = new FnCalledAuthSettings();
$mailFactory = new MailFactory($authSettings);

#$json = json_decode(file_get_contents('php://input'));
#ItSetUpMailData($json);

$mail = $mailFactory->createMail();
ItSetUpMail($mail);
ItSetUpBody($_POST, $mail);
ItSetUpAttachments($_FILES, $mail);

echo ($mail->send()) ?  'sent' : 'error';

if (ITFORM_DEBUG)
{
    echo "<pre>";
    var_dump($mail);
    echo "</pre>";
}