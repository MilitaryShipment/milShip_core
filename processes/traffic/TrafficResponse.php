<?php


__DIR__ . '/../../abstraction.php';


abstract class TrafficResponse implements TrafficResponseBehavior{

  public function __construct(){}

  protected function _isUntouched($timeStr){
    if($timeStr == self::UNTOUCHED){
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
}
