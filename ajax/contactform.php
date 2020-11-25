<?php
require_once __DIR__ . "/../classes/class.ContactForm.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $return = array();

    $form = new ContactForm($_POST);
    if ($form->validateInput() === true) {

        //$form->setRecipient('guy-smiley@example.com');
        $form->setRecipient('mez@aitg.com');
        $form->setDB('172.31.0.214', 'diuser', 'd1Ch@allenge', 'DI_ContactForm');

        $saved = $form->saveMessage();
        $sent = $form->sendMessage();

        if ($saved && $sent) {
            $return['success'] = 1;
        } else {
            $return['success'] = 0;
        }

    } else {

        $return['success'] = 0;

    }
    $return['saved'] = $form->messageSaved ? 1 : 0;
    $return['sent'] = $form->messageSent ? 1 : 0;
    $return['errors'] = $form->formErrors;

    header("Content-Type: application/json");
    echo json_encode($return);
    die();

} else {
    header("HTTP/1.0 405 Method Not Allowed");
}
