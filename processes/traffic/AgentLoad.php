<?php

/*Agent load is a response to a webpage.
 It accepts times from a driver surrounding a shipments $pack_date
expects format: hour:min [18:22, 19:4] because date is assumed $pack_date
expects a default hour:min of '0:0'
*/

require_once __DIR__ . '/../../Messenger.php';
require_once __DIR__ . '/../../models/comms/MobileTrafficResponse.php';
require_once __DIR__ . '/../../models/comms/Notification.php';
require_once __DIR__ . '/../../models/ops/Shipment.php';
require_once __DIR__ . '/../../models/ops/Driver.php';


class AgentLoad{

  const UNTOUCHED = '0:0';
  const PAGE = 'agentLoadETA';
  const MSGFILENAME = 'agentLoadResponse';
  const MSGTYPE = 'internal-email';
  const MSGTO = 'j.watson@allamericanmoving.com';
  const MSGFROM = 'mobileSite@militaryshipment.com';
  const MSGCC = 'webadmin@allamericanmoving.com';

  public $response;
  protected $shipment;
  protected $timeInputs = array();
  protected $msgBody;
  protected $msgSubject;

  public function __construct($gbl_dps,$atAgentEarly,$atAgentLate,$loadEtaEarly,$loadEtaLate){
    $this->timeInputs = array(
      "at_agent_eta_early"=>$atAgentEarly,
      "at_agent_eta_late"=>$atAgentLate,
      "load_eta_early_time"=>$loadEtaEarly,
      "load_eta_late_time"=>$loadEtaLate
    );
    try{
      $this->shipment = new Shipment($gbl_dps);
      $this->shipment->load_eta_early_time = $this->_isUntouched($this->timeInputs['load_eta_early_time']) ? $this->shipement->load_eta_early_time : $this->_buildTimeStr($this->timeInputs['load_eta_early_time']);
      $this->shipment->load_eta_late_time = $this->_isUntouched($this->timeInputs['load_eta_late_time']) ? $this->shipment->load_eta_late_time : $this->_buildTimeStr($this->timeInputs['load_eta_late_time']);
      $this->shipment->update();
      $this->_buildMsgSubject()
            ->_buildMsgBody()
            ->_buildResponse()
            ->_sendMsg()
            ->_buildNotification();
    }catch(\Exception $e){
      throw new \Exception($e->getMessage());
    }
  }
  protected function _isUntouched($timeStr){
    if($timeStr == self::UNTOUCHED){
      return true;
    }
    return false;
  }
  protected function _isAtAgentBeforeLoad(){
    if(!$this->_isUntouched($this->timeInputs['at_agent_eta_early']) || !$this->_isUntouched($this->timeInputs['at_agent_eta_late'])){
      return true;
    }
    return false;
  }
  protected function _buildDateStr($timeStr){
    $date = date("m/d/Y",strtotime($this->shipment->pack_date));
    $timeStr = $date . " " . $timeStr;
    return date("m/d/Y H:i:s",strtotime($timeStr));
  }
  protected function _buildTimeStr($timeStr){
    return date("H:i:s",strtotime($timeStr));
  }
  protected function _buildMsgSubject(){
    $this->msgSubject = "ATTN: AGENT LOAD REPLY FROM " . $this->shipment->driver_id . " ON SHIPMENT " . $this->shipment->gbl_dps;
    return $this;
  }
  protected function _buildMsgBody(){
    $driver = $this->shipment->getDriver();
    $this->msgBody = $driver->first_name . " " . $driver->last_name . " has responded to an AGENT_LOAD_ETA traffic text with the following results:\n";
    $this->msgBody .= "Are you planning to go to the origin agent before load?\n";
    if($this->_isAtAgentBeforeLoad()){
      $this->msgBody .= "\tYes\n";
    }else{
      $this->msgBody .= "\tNo\n";
    }
    $this->msgBody .= "<ul>";
    foreach($this->timeInputs as $key=>$value){
      if(!$this->_isUntouched($value)){
        $this->msgBody .= "<li>" . $key . " = " . $value . "</li>";
      }
    }
    $this->msgBody .= "</ul>";
    return $this;
  }
  protected function _sendMsg(){
    try{
      Messenger::send(self::MSGTO,self::MSGFROM,self::MSGFROM,self::MSGCC,self::MSGCC,'',$this->msgSubject,nl2br($this->msgBody));
    }catch(\Exception $e){
      throw new \Exception($e->getMessage());
    }
    return $this;
  }
  protected function _buildResponse(){
    $driver = $this->shipment->getDriver();
    $response = new MobileTrafficResponse();
    $response->gbl_dps = $this->shipment->gbl_dps;
    $response->scac = $this->shipment->scac;
    $response->page = self::PAGE;
    $response->members_name = $this->shipment->full_name;
    $response->rank = $this->shipment->rank;
    $response->mc = $this->shipment->mc_tid;
    $response->orig_agent_id = $this->shipment->orig_agent_id;
    $response->dest_agent_id = $this->shipment->dest_agent_id;
    $response->driver_id = $this->shipment->driver_id;
    $response->driver_name = $driver->first_name . " " . $driver->last_name;
    $response->driver_phone = $driver->phone_number;
    $response->driver_mobile = $driver->mobile;
    $response->load_eta_early_time = $this->_isUntouched($this->timeInputs['load_eta_early_time']) ? null : $this->_buildDateStr($this->timeInputs['load_eta_early_time']);
    $response->load_eta_late_time = $this->_isUntouched($this->timeInputs['load_eta_late_time']) ? null : $this->_buildDateStr($this->timeInputs['load_eta_late_time']);
    $response->at_orig_agent_before_load = $this->_isAtAgentBeforeLoad() ? 1 : 0;
    $response->at_agent_eta_early = $this->_isUntouched($this->timeInputs['at_agent_eta_early']) ? null : $this->_buildDateStr($this->timeInputs['at_agent_eta_early']);
    $response->at_agent_eta_late = $this->_isUntouched($this->timeInputs['at_agent_eta_late']) ? null : $this->_buildDateStr($this->timeInputs['at_agent_eta_late']);
    $response->created_by = $this->shipment->driver_id;
    $response->status_id = 1;
    $response->create();
    $this->response = $response;
    return $this;
  }
  protected function _buildNotification(){
    $driver = $this->shipment->getDriver();
    $note = new Notification();
    $note->gbl_dps = $this->shipment->gbl_dps;
    $note->message_filename = self::MSGFILENAME;
    $note->scac = $this->shipment->scac;
    $note->message_type = self::MSGTYPE;
    $note->message_to = self::MSGTO;
    $note->message_from = self::MSGFROM;
    $note->message_sent_by = __FILE__;
    $note->message_sent_date = date('m/d/Y H:i:s');
    $note->message_code = 1;
    $note->message_status_code = 'SENT';
    $note->message = $this->msgBody;
    $note->move_coordinator = $this->shipment->mc_tid;
    $note->registration_id = $this->shipment->registration_number;
    $note->driver_id = $this->shipment->driver_id;
    $note->driver_name = $driver->first_name . " " . $driver->last_name;
    $note->subject = $this->msgSubject;
    $note->created_by = __FILE__;
    $note->status_id = 1;
    $note->create();
    return $this;
  }
}
