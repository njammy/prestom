<?php

    if($_GET['token']==Configuration::get('personnal_token')){
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        if($decoded["status"]=="SUCCESS"){
            header(str_replace(" ", "", "Location:validation?status=111&notiftoken=".$decoded["notif_token"]));
        }
    } else {
        header($_SERVER['SERVER_PROTOCOL'].' 500 Bad Request');
        exit;
    }