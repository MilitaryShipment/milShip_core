<?php

require_once __DIR__ . '/../../../models/dc/RecEmail.php';
require_once __DIR__ . '/../../../models/util/translateGbl.php';


class RecoverRecEmail{

  protected $_longGbl;
  protected $_shortGbl;

  public function __construct($longGbl){
    $this->$_longGbl = $longGbl;
    $this->$_shortGbl = $this->_getShortGbl($longGbl);
    echo $this->$_shortGbl;
  }
  protected function _getShortGbl($longGbl){
    return dps2tops($longGbl);
  }
}
