<?php


__DIR__ . '/../../abstraction.php';


abstract class TrafficResponse implements TrafficResponseBehavior{

  public function __construct(){}

  protected function _isTimeUntouched($timeStr){
    if($timeStr == self::UNTOUCHEDTIME){
      return true;
    }
    return false;
  }
  protected function _isDateUntouched($dateStr){
    if($dateStr == self::UNTOUCHEDDATE){
      return true;
    }
    return false;
  }
  protected function _buildDateStr($dateStr,$timeStr){
    $date = date("m/d/Y",strtotime($dateStr));
    $timeStr = $date . " " . $timeStr;
    return date("m/d/Y H:i:s",strtotime($timeStr));
  }
  protected function _buildTimeStr($timeStr){
    return date("H:i:s",strtotime($timeStr));
  }
}
