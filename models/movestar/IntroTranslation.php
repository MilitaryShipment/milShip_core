<?php namespace MoveStar;

date_default_timezone_set("America/Chicago");

require_once __DIR__ . '/OutboundShipment.php';
require_once __DIR__ . '/Firearm.php';
require_once __DIR__ . '/ExtraPickup.php';
require_once __DIR__ . '/../ops/Firearm.php';


/*$input = array(
    "gbl_dps"=>"FDNT0000000",
    "page"=>'DestAddress',
    "address"=>"456 R Street",
    "city"=>"Little Rock",
    "state"=>"Ak",
    "zip"=>"66661"
);
try{
    $i = new IntroTranslation($input);
}catch(\Exception $e){
    echo $e->getMessage() . "\n";
}*/


class IntroTranslation{

    const DEBUG = false;
    const SHIPKEY = 'gbl_dps';
    const PAGEKEY = 'page';
    const ORIGPAGE = 'OrigAddress';
    const INPUTLOG = '/srv/www/htdocs/m/log/Introparams.txt';
    const TRUE = 'Y';
    const FALSE = 'N';

    protected $shipment;
    protected $input = array();
    protected $targetBools = array(
        "text_opt_out",
        "extra_pickup",
        "orig_military_housing",
        "military_housing",
        "origin_tractor",
        "gun_yn"
    );
    protected $targets = array(
        "gbl_dps",
        "text_opt_out"=>"is_text_opt_out",
        "extra_pickup"=>"hasExtraPickup",
        "orig_military_housing"=>"orig_military_housing",
        "military_housing"=>"dest_military_housing",
        "origin_tractor"=>"origTractorAccess",
        "orig_authorized_individual"=>"releasing_agent_name",
        "orig_authorized_individual_phone"=>"releasing_agent_phone",
        "dest_authorized_individual"=>"receiving_agent_name",
        "dest_authorized_individual_phone"=>"receiving_agent_phone",
        "gun_yn"=>"is_firearm"
    );
    protected $targetLists = array(
        "Oversize"
    );
    protected $firearmKeys = array(
        "make",
        "model",
        "serial"
    );
    protected $extraPickUpKeys = array(
        "shipment_type",
        "sq_footage",
        "xtra_pickup_address",
        "xtra_pickup_city",
        "xtra_pickup_state",
        "xtra_pickup_zip"
    );
    protected $addressKeys = array(
        "address",
        "city",
        "state",
        "zip"
    );
    protected $addressPages = array(
        "OrigAddress",
        "DestAddress"
    );


    public function __construct($params){
        $this->input = $params;
        $this->_logInput()
            ->_buildShipment()
            ->_parseBools()
            ->_parseFirearms()
            ->_checkForNewPickup()
            ->_checkForAddresses();
        $this->shipment->update();
    }
    protected function _logInput(){
        $logStr = date('m/d/Y H:i:s') . "\n" . print_r($this->input, true);
        $appendage = self::DEBUG ? 0 : 8;
        file_put_contents(self::INPUTLOG, $logStr,$appendage) || die(print_r(error_get_last()));
        return $this;
    }
    protected function _buildShipment(){
        if(!isset($this->input[self::SHIPKEY])){
            throw new \Exception('Unable to build. No Shipment Specified.');
        }
        try{
            $this->shipment = new OutBoundShipment($this->input[self::SHIPKEY]);
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
        return $this;
    }
    protected function _parseBools(){
        foreach($this->input as $key => $value){
            if(!in_array($key,$this->targetBools)){
                continue;
            }else{
                $field = $this->targets[$key];
            }
            if($this->_isTrue($value)){
                $this->shipment->$field = self::TRUE;
            }else{
                $this->shipment->$field = self::FALSE;
            }
        }
        return $this;
    }
    protected function _parseFirearms(){
        if($this->_isTrue($this->shipment->is_firearm)){
            $this->_verifyExistingFireArms()
                ->_checkForNewFirearm();
        }
        return $this;
    }
    protected function _verifyExistingFireArms(){
        $knownFirearms = \Firearm::getAll($this->shipment->gbl_dps);
        foreach($knownFirearms as $knownFirearm){
            if(!Firearm::recordExists($knownFirearm->make,$knownFirearm->model,$knownFirearm->serial,$this->shipment->gbl_dps)){
                $f = new Firearm();
                $f->gbl_dps = $this->shipment->gbl_dps;
                $f->make = $knownFirearm->make;
                $f->model = $knownFirearm->model;
                $f->serial = $knownFirearm->serial;
                $f->create();
            }
        }
        return $this;
    }
    protected function _checkForNewFirearm(){
        foreach($this->firearmKeys as $key){
            if(!isset($this->input[$key])){
                return $this;
            }
        }
        $f = new Firearm();
        $f->gbl_dps = $this->shipment->gbl_dps;
        $f->make = $this->input[$this->firearmKeys[0]];
        $f->model = $this->input[$this->firearmKeys[1]];
        $f->serial = $this->input[$this->firearmKeys[2]];
        $f->create();
        return $this;
    }
    protected function _checkForNewPickup(){
        foreach($this->extraPickUpKeys as $key){
            if(!isset($this->input[$key])){
                return $this;
            }
        }
        $pickup = new ExtraPickup();
        $pickup->gbl_dps = $this->shipment->gbl_dps;
        $pickup->location_type = $this->input[$this->extraPickUpKeys[0]];
        $pickup->sq_footage = $this->input[$this->extraPickUpKeys[1]];
        $pickup->address = $this->input[$this->extraPickUpKeys[2]];
        $pickup->city = $this->input[$this->extraPickUpKeys[3]];
        $pickup->state = $this->input[$this->extraPickUpKeys[4]];
        $pickup->zip = $this->input[$this->extraPickUpKeys[5]];
        $pickup->create();
        return $this;
    }
    protected function _checkForAddresses(){
        if(!isset($this->input[self::PAGEKEY]) || !in_array($this->input[self::PAGEKEY],$this->addressPages) || !$this->_verifyAddressKeys()){
            return $this;
        }elseif($this->input[self::PAGEKEY] == self::ORIGPAGE){
            $this->shipment->orig_address = $this->input[$this->addressKeys[0]];
            $this->shipment->orig_city = $this->input[$this->addressKeys[1]];
            $this->shipment->orig_state = $this->input[$this->addressKeys[2]];
            $this->shipment->orig_zip = $this->input[$this->addressKeys[3]];
        }else{
            $this->shipment->dest_address = $this->input[$this->addressKeys[0]];
            $this->shipment->dest_city = $this->input[$this->addressKeys[1]];
            $this->shipment->dest_state = $this->input[$this->addressKeys[2]];
            $this->shipment->dest_zip = $this->input[$this->addressKeys[3]];
        }
        return $this;
    }
    protected function _verifyAddressKeys(){
        foreach($this->addressKeys as $key){
            if(!isset($this->input[$key])){
                return false;
            }
        }
        return true;
    }
    protected function _isTrue($value){
        return (strtolower($value) == "y" || strtolower($value) == "yes" || $value == 1 || strtolower($value) == 'completed');
    }
}
