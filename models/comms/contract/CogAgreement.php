<?php

require_once __DIR__ . '/Contract.php';

class CogAgreement extends Contract{

    public function __construct($id = null)
    {
        parent::__construct($id);
    }
    public function buildTemplate($web = false){
        $template = "";
        return nl2br($template);
    }
}
