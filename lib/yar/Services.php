<?php

class Services {
    public function support($uid, $feedId)
    {
        return "uid = " . $uid . ", feedId = " . $feedId;
    }
}

$yar_server = new Yar_server(new Services());
$yar_server->handle();
