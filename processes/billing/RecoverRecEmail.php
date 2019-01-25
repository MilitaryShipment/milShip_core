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
  protected $_records = array();

  public function __construct($longGbl){
    $this->_longGbl = $longGbl;
    $this->_shortGbl = $this->_getShortGbl($longGbl);
    $this->_build()->_recover();
  }
  protected function _getShortGbl($longGbl){
    return dps2tops($longGbl,true);
  }
  protected function _build(){
    $results = $GLOBALS['db']
        ->suite(RecEmail::DRIVER)
        ->driver(RecEmail::DRIVER)
        ->database(RecEmail::DB)
        ->table(RecEmail::TABLE)
        ->select(RecEmail::PRIMARYKEY)
        ->where("gbl","=","'" . $this->_longGbl . "'")
        ->get();
    while($row = mysql_fetch_assoc($results)){
      $this->_records[] = new RecEmail($row[RecEmail::PRIMARYKEY]);
    }
    return $this;
  }
  protected function _recover(){
    $pattern = "/" . $this->_shortGbl . "/";
    foreach($this->_records as $record){
      $record->doc_path = preg_replace($pattern,$this->_longGbl,$record->doc_path);
      $record->update();
    }
    return $this;
  }
}
