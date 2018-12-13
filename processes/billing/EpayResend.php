<?php

//http://www.aftermovecare.com/basic/main.php?msg=&report_heading=Agent_Information&task=agent_info&agent_id=$agent_id&s=$web_password&gbl_dps=$gbl_dps&constraint=epay

//$agent_id,$web_password,$gbl_dps

require_once __DIR__ . '/../../models/ops/Agent.php';
//require_once __DIR__ . '';

$obj = new EpayResend();

class EpayResend{

  const ERRORDIR = './failedMsgs';
  const SUBJPATTERN = '/E-Pay\sFor\s([A-Z][0-9]{4}).*Shipment_\s([A-Z]{4}[0-9]{7})/i';

  protected $matches = array();
  protected $_parsed = array();
  protected $_exceptions = array();

  public function __construct(){
    $this->_readErrors();
    foreach($this->matches as $match){
      echo $this->_getWebPassword($match[0]);
      // print_r($match);
    }
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
  public function buildWebPath(){}
}
