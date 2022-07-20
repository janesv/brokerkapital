<?php
require_once ('private.php');

function ItSetUpMailData($data)
{
    // ?
}

function ItSetUpMail($mail)
{
    $mail->setFrom('no-reply@b2bpolis.ru');
    $mail->CharSet = 'UTF-8';
}

function ItSetUpBody($data, $mail)
{
    if (empty($data))
        return false;

    $mail->addAddress('info@broker-kapital.ru');
    $mail->addAddress('dovjik_s@mail.ru');

    $mail->isHTML($data['isHTML']);
    
    if ($data['addressType'] == 'beforeOrder') {
        $mail->Subject = ('Заявка на страхования с сайта (СМС-верификация)');
    } else {
        $mail->Subject = 'Заявка на страхования с сайта';
    }
    
    $mail->Body = $data['message'];

    return true;
}

function ItSetUpAttachments($files, $mail)
{
    if (empty($files))
        return false;

    $mimeTypes = array(
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document", // docx
        "application/pdf", // pdf
        "application/msword", // doc
        "rtf;application/rtf", // rtf
        "rtf;application/x-rtf",
        "rtf;text/richtext",
        "application/vnd.oasis.opendocument.text", // odt
        "image/bmp", // bmp
        "image/png", // png
        "image/jpg", // jpg
        "image/jpeg", // jpeg
        "image/gif" // gif
    );

    $count = 0;
    foreach ($files as $file)
    {
        $name = 'file_' . $count;
        if (!empty($files[$name]['tmp_name']) && is_uploaded_file($files[$name]['tmp_name']))
        {
            if (!in_array($files[$name]['type'], $mimeTypes))
                continue;

            if (intval($files[$name]['size']) > ITFORM_ATTACH_SIZE)
                continue;

            $mail->AddAttachment($files[$name]['tmp_name'],
                                     $files[$name]['name']);
        }

        $count++;
    }

    return true;
}
