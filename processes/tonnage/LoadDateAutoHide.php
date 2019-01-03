<?php

require_once __DIR__ . '/../../models/ops/tonnage/TonnageList.php';

class LoadDateAutoHide{

  const LIMITSTR = "+3 weeks";

  public function __construct(){
    $list = new TonnageList();
    foreach($list->shipments as $shipment){
      if(strtotime($shipment->pickup) > strtotime(self::LIMITSTR)){
        echo $shipment->gbl_dps . " -> " . $shipment->pickup . "\n";
        $shipment->status_id = 2;
        $shipment->update();
      }
    }
  }
}
