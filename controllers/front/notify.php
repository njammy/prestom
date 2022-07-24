<?php

    if($_GET['token']=='Okl78ERwxXDErtUIjh14iIpm795zedrfMP5erfdzdfr97e28e45efsxy45'){
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        if($decoded["status"]=="SUCCESS"){
            header(str_replace(" ", "", "Location:validation?status=11&notiftoken=".$decoded["notif_token"]));
        }
    } else {
        header($_SERVER['SERVER_PROTOCOL'].' 500 Bad Request');
        exit;
    }