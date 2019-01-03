<?php

require_once __DIR__ . '/../../models/ops/tonnage/TonnageList.php';

class LoadDateAutoHide{

  const LIMITSTR = "+2 weeks";

  public function __construct(){
    $list = new TonnageList();
    foreach($list->shipments as $shipment){
      if(strtotime($shipment->pickup) > strtotime(self::LIMITSTR)){
        echo $shipment->pickup . "\n";
      }
    }
  }
}
