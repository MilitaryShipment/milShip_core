<?php

require_once __DIR__ . '/../../models/ops/tonnage/TonnageList.php';
require_once __DIR__ . '/../../models/ops/tonnage/TonnageRef.php';
require_once __DIR__ . '/../../models/ops/tonnage/NearbyShipments.php';


class UpdateTonnageRef{

  public function __construct(){
    $this->_truncate()
          ->_build();
  }

  protected function _truncate(){
    $GLOBALS['db']
    ->suite(TonnageRef::DRIVER)
    ->driver(TonnageRef::DRIVER)
    ->database(TonnageRef::DATABASE)
    ->table(TonnageRef::TABLE)
    ->truncate();
    return $this;
  }
  protected function _build(){
    $list = new TonnageList();
    foreach($list->shipments as $shipment){
      $otwIds = array();
      $nearOriginIds = array();
      $nearDestIds = array();
      $nearby = new NearbyShipments($shipment);
      $otw = $nearby->onTheWay();
      $nearOrigin = $nearby->nearOrigin();
      $nearDest = $nearby->nearDestination();
      foreach($nearDest as $near){
        $nearDestIds[] = $near->id;
      }
      foreach($nearOrigin as $near){
        $nearOriginIds[] = $near->id;
      }
      foreach($otw as $near){
        $otwIds[] = $near->id;
      }
      $ref = new TonnageRef();
      $ref->tonnageId = $shipment->id;
      $ref->near_origin = implode(',',$nearOriginIds);
      $ref->near_destination = implode(',',$nearDestIds);
      $ref->on_the_way = implode(',',$otwIds);
      $ref->create();
    }
    return $this;
  }
}
