<?php

require_once __DIR__ . '/../../models/dc/RecEmail.php';
require_once __DIR__ . '/../../models/util/translateGbl.php';

/*
Old billing records use short gbl.
Usually it's not a big deal because they don't reference like an archive
Sometimes they need an old one and you have to update the file path to the long gbl
tjjw1
*/


class RecoverRecEmail{

  protected $_longGbl;
  protected $_shortGbl;

  public function __construct($longGbl){
    $this->_longGbl = $longGbl;
    $this->_shortGbl = $this->_getShortGbl($longGbl);
    echo $this->_shortGbl;
  }
  protected function _getShortGbl($longGbl){
    return dps2tops($longGbl);
  }
}
