<?php

//$msg .= "\nIf you have issues with the link above, please visit $homeUrl\n. Where you can login with the following credentials:\nAgent ID: $agent_id\nUsername:$username\nPassword:$password";

//http://www.aftermovecare.com/basic/main.php?msg=&report_heading=Agent_Information&task=agent_info&agent_id=$agent_id&s=$web_password&gbl_dps=$gbl_dps&constraint=epay

//$agent_id,$web_password,$gbl_dps

//todo build URL, get recipeint RESEND

require_once __DIR__ . '/../../models/ops/Agent.php';
require_once __DIR__ . '/../../Messenger.php';

$obj = new EpayResend();

class EpayResend{

  const ERRORDIR = './failedMsgs';
  const HOMEURL = 'http://www.aftermovecare.com';
  const URLBASE = 'http://www.aftermovecare.com/basic/main.php?msg=&report_heading=Agent_Information&task=agent_info&agent_id=';
  const SUBJECTBASE = 'All-American Moving Group E-Pay For ';
  const SUBJPATTERN = '/E-Pay\sFor\s([A-Z][0-9]{4}).*Shipment_\s([A-Z]{4}[0-9]{7})/i';
  const MSGFROM = 'Epay@allamericanmoving.com';
  const FROMNAME = 'Epay';
  const MSGCC = 'webadmin@allamericanmoving.com';
  const DEVMAIL = 'j.watson@allamericanmoving.com';

  protected $matches = array();
  protected $_parsed = array();
  protected $_exceptions = array();
  protected $_badAgents = array();

  public function __construct(){
    $this->_readErrors()->_cycleMatches();
  }

  protected function _readErrors(){
    $results = scandir(self::ERRORDIR);
    foreach($results as $result){
      if(preg_match(self::SUBJPATTERN,$result,$matches)){
        $this->_addToParsed($result);
        $this->matches[] = array($matches[1],$matches[2]);
      }elseif($result != '.' && $result != '..'){
        $this->_addToExceptions($result);
      }
    }
    return $this;
  }
  protected function _cycleMatches(){
    foreach($this->matches as $match){
      try{
        $web_password = $this->_getWebPassword($match[0]);
        $msgBody = $this->_buildMsgBody($match[0],$web_password,$match[1]);
        $recipients = $this->_getRecipient($match[0]);
        $subject = $this->_buildMsgSubject($match[0]);
        foreach($recipients as $recipient){
          $msgBody = $this->_appendMsgBody($msgBody,$match[0],$recipient);
          Messenger::send(self::DEVMAIL,self::MSGFROM,self::FROMNAME,self::FROM,array(self::FROM,self::MSGCC),array(),$subject,$msgBody);
        }
      }catch(\Exception $e){
        $this->_badAgents[] = $match[0];
      }
    }
    return $this;
  }
  protected function _addToParsed($fileName){
    $pathInfo = pathinfo($fileName);
    $this->_parsed[] = $pathInfo['dirname'] . '/' . $pathInfo['basename'];
    return $this;
  }
  protected function _addToExceptions($fileName){
    $pathInfo = pathinfo($fileName);
    $this->_exceptions[] = $pathInfo['dirname'] . '/' . $pathInfo['basename'];
    return $this;
  }
  protected function _getWebPassword($agent_id){
    try{
      $agent = new Agent($agent_id);
    }catch(\Exception $e){
      throw new \Exception($e->getMessage());
    }
    return $agent->web_password;
  }
  protected function _getRecipient($agent_id){
    try{
      $agent = new Agent($agent_id);
    }catch(\Exception $e){
      throw new \Exception($e->getMessage());
    }
    return $agent->getEpayRecipients();
  }
  protected function _buildWebPath($agent_id,$web_password,$gbl_dps){
    return self::URLBASE . $agent_id . "&s=" . $web_password . "&gbl_dps=" . $gbl_dps  . "&constraint=epay";
  }
  protected function _buildMsgBody($agent_id,$web_password,$gbl_dps){
    return "Your epay images are ready for review:<br>" . $this->_buildWebPath($agent_id,$web_password,$gbl_dps);
  }
  protected function _buildMsgSubject($agent_id){
    $agent = new Agent($agent_id);
    return self::SUBJECTBASE . $agent_id . " " . $agent->agent_name;
  }
  protected function _appendMsgBody($msgBody,$agent_id,$email){
    $credentials = WebUser::getCredentials($email);
    $msgBody .= "<br>If you have issues with the link above, please visit " . self::HOMEURL;
    $msgBody .= "<br>. Where you can login with the following credentials:<br>";
    $msgBody .= "Agent ID: " . $agent_id . "<br>";
    $msgBody .= "Username: " . $credentials['username'] . "<br>";
    $msgBody .= "Password: " . $credentials['password'];
    return $msgBody;
  }
}
