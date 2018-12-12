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
    public static function send($to,$from,$fromName,$replyTo,$cc,$bcc,$subject,$body,$attachments);
//    public function call();
//    public function fax();
//    public function email();
}
interface TrafficResponseBehavior{
  const UNTOUCHEDTIME = '0:0';
  const MSGTYPE = 'internal-email';
  const MSGFROM = 'mobileSite@militaryshipment.com';
  const MSGCC = 'webadmin@allamericanmoving.com';
  protected function _buildResponse();
  protected function _buildNotification();
  protected function _buildMsgSubject();
  protected function _buildMsgBody();
  protected function _sendMsg();
}
