<?php

require_once __DIR__ . '/../../models/billing/AchPayment.php';
require_once __DIR__ . '/../../models/billing/EpayImage.php';

class EpayHunt{

  protected $datePaid;
  protected $amountPaid;

  public function __construct($datePaid,$amountPaid){
    $this->datePaid = $datePaid;
    $this->amountPaid = $amountPaid;
    $this->hunt();
  }

  protected function _hunt(){
    $payments = AchPayment::get('amount',$this->amountPaid);
    foreach($payments as $payment){
      $images = EpayImage::get("vendor_id",$payment->vendor_id,"complete");
    }
  }

}
