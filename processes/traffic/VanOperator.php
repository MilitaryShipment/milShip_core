<?php

//todo build relevant values in _buildResponse()

/*Van Operator is a response to a webpage.
 It accepts [information??] from a driver surrounding a shipment's ??_date
expects format: hour:min [18:22, 19:4] because date is assumed ??_date
expects a default hour:min of '0:0'
*/

/*SAMPLE INPUT
{
delivery_date_eta_early_time: "0:0"
delivery_date_eta_late_time: "0:0"
delivery_eta_date: "11/14/2018"
final_load_eta_date: "0/1/1970"
gross_weight: 0
is_overflow: false
necessity_item_description: ""
necessity_items_left: false
tare_weight: 0
}

*/

require_once __DIR__ . '/../../Messenger.php';
require_once __DIR__ . '/../../models/comms/MobileTrafficResponse.php';
require_once __DIR__ . '/../../models/comms/Notification.php';
require_once __DIR__ . '/../../models/ops/Shipment.php';
require_once __DIR__ . '/../../models/ops/Driver.php';
require_once __DIR__ . '/TrafficResponse.php';

class VanOperator extends TrafficResponse{

  const PAGE = 'vanOperator';
  const MSGFILENAME = 'vanOperatorResponse';
  const MSGTO = 'j.watson@allamericanmoving.com';

  public $response;
  protected $shipment;
  protected $msgSubject;
  protected $msgBody;
  protected $input;
  protected $inputKeys = array(
    "delivery_date_eta_early_time", //MobileTrafficResponse true name
    "delivery_date_eta_late_time", //MobileTrafficResponse true name
    "delivery_eta_date", //true name
    "final_load_eta_date", //true name
    "gross_weight", //true name
    "tare_weight", //true name
    "is_overflow", //MobileTrafficResponse true name
    "necessity_items_left", //MobileTrafficResponse true name
    "necessity_item_description" //MobileTrafficResponse true name
  );

  public function __construct($gbl_dps,$inputObj){
    $this->input = $inputObj;
    if(!$this->_verifyInput()){
      throw new \Exception('Invalid Input');
    }
    try{
      $this->shipment = new Shipment($gbl_dps);
      $this->shipment->delivery_eta_date = $this->_isDateUntouched($this->input->delivery_eta_date) ? $this->input->delivery_eta_date : date('m/d/Y',strtotime($this->input->delivery_eta_date));
      $this->shipment->final_load_eta_date = $this->_isDateUntouched($this->input->final_load_eta_date) ? $this->input->final_load_eta_date : date('m/d/Y',strtotime($this->input->final_load_eta_date));
      $this->shipment->gross_weight = ($this->input->gross_weight == 0) ? $this->shipment->gross_weight : $this->input->gross_weight;
      $this->shipment->tare_weight = ($this->input->tare_weight == 0) ? $this->shipment->tare_weight : $this->input->tare_weight;
      $this->shipment->update();
      $this->_buildResponse()
          ->_buildNotification()
          ->_buildMsgSubject()
          ->_buildMsgBody()
          ->_sendMsg();
    }catch(\Exception $e){
      throw new \Exception($e->getMessage());
    }
  }

  protected function _verifyInput(){
    $targetKeys = count($this->inputKeys);
    $validCount = 0;
    foreach($this->input as $key=>$value){
      if(!in_array($key,$this->inputKeys)){
        $errorStr = "Invalid Input: " . $key;
        throw new \Exception($errorStr);
      }else{
        $validCount++;
      }
    }
    if($validCount != $targetKeys){
      return false;
    }
    return true;
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
  protected function _buildMsgSubject(){
    $this->msgSubject = "ATTN: VAN OPERATOR REPLY FROM " . $this->shipment->driver_id . " ON SHIPMENT " . $this->shipment->gbl_dps;
    return $this;
  }
  protected function _buildMsgBody(){
    $driver = $this->shipment->getDriver();
    $this->msgBody = $driver->first_name . " " . $driver->last_name . " has responded to an AGENT_LOAD_ETA traffic text with the following results:\n";
    $this->msgBody .= "Can you provide an ETA for delivery?\n\n";
    if($this->_isDateUntouched($this->input->delivery_eta_date) && !$this->_isDateUntouched($this->input->final_load_eta_date)){
      $this->msgBody .= "No.\n";
      $this->msgBody .= "Estimated date when a delivery ETA can be provided?\n";
      $this->msgBody .= $this->input->final_load_eta_date . "\n";
    }else{
      $this->msgBody .= "Yes.\n";
      $this->msgBody .= "What is your ETA for delivery?\n";
      $this->msgBody .= $this->input->delivery_eta_date . "\n";
      $this->msgBody .= "What is your earliest arrival time?\n";
      $this->msgBody .= $this->input->delivery_date_eta_early_time . "\n";
      $this->msgBody .= "What is your lastest arrival time?\n";
      $this->msgBody .= $this->input->delivery_date_eta_late_time . "\n";
    }
    $this->msgBody .= "Gross Weight?\n";
    $this->msgBody .= $this->input->gross_weight . "\n";
    $this->msgBody .= "Tare Weight?\n";
    $this->msgBody .= $this->input->tare_weight . "\n";
    $this->msgBody .= "Is there an overflow?\n";
    $this->msgBody .= $this->input->is_overflow ? "Yes\n": "No\n";
    $this->msgBody .= "Did you leave any necessity items?\n";
    $this->msgBody .= $this->input->necessity_items_left ? "Yes\n" : "No\n";
    $this->msgBody .= (strlen($this->input->necessity_item_description)) ? $this->input->necessity_item_description . "\n" : "";
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
}
