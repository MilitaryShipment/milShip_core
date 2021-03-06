<?php

require_once '/srv/www/htdocs/classes/db/db.php';

if(!isset($GLOBALS['db'])){
    $db = new DB();
}

interface RecordBehavior{
    public function create();
    public function update();
    public function setFields($updateObj);
}
interface ContactBehavior{
//    public function getNotifications();
//    public function getResponses();
//    public function call();
//    public function fax();
//    public function email();
//    public function verify();
}
interface ContractBehavior{
    public function send();
    public function revoke();
    public function comment($comment);
    public function reply($msg);
    public function unexpire();
    public function buildTemplate($web);
    public function buildPdf($signed);
}
interface MessageBehavior{
    public static function verify($contact);
    public static function send($msgArray,$host,$port,$username,$password);
//    public function call();
//    public function fax();
//    public function email();
}
interface TrafficResponseBehavior{
  const UNTOUCHEDTIME = '0:0';
  const UNTOUCHEDDATE = '0/1/1970';
  const MSGTYPE = 'internal-email';
  const MSGFROM = 'mobileSite@militaryshipment.com';
  const MSGCC = 'webadmin@allamericanmoving.com';
}
