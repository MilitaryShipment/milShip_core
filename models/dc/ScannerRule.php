<?php

require_once __DIR__ . '/../../record.php';


class ScannerRule extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_scan_rules';
    const PRIMARYKEY = 'id';
    const DOMAIN = '@allamericanmoving.com';

    public $id;
    public $guid;
    public $rule_name;
    public $form_name;
    public $gbl_dps;
    public $action;
    public $recipients;
    public $message;
    public $expiration_date;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;



    public function __construct($id = null)
    {
      parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
      if(!empty($this->recipients)){
        $this->_parseRecipients();
      }
    }
    public static function hasRule($formName){
        $results = $GLOBALS['db']
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("form_name = '$formName'")
            ->get('value');
        if(empty($results) || is_null($results)){
            return false;
        }
        return $results;
    }
    private function _parseRecipients($toArray = true){
        if($toArray){
            $str = $this->recipients;
            $this->recipients = array();
            $pieces = explode(";",$str);
            foreach($pieces as $piece){
                $this->recipients[] = $piece .= self::DOMAIN;
            }
        }else{
            $str = '';
            foreach($this->recipients as $recipient){
                $pieces = explode('@',$recipient);
                $str .= $pieces[0] . ";";
            }
        }
        return $this;
    }
    public function create(){
        $this->_parseRecipients(false);
        $data = get_object_vars($this);
        $upData = array();
        foreach($data as $key=>$value){
            if(!empty($value) && !is_null($value)){
                $upData[$key] = $value;
            }
        }
        $upData['created_date'] = date('m/d/Y H:i:s');
        $upData['updated_date'] = date('m/d/Y H:i:s');
        $upData['status_id'] = 1;
        $upData['guid'] = 'NEWID()';
        $results = $GLOBALS['db']
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->data($upData)
            ->insert()
            ->put();
        $this->_getId()
            ->_build();
        return $this;
    }
    public function update(){
        $this->_parseRecipients(false);
        $data = get_object_vars($this);
        $data['updated_date'] = date('m/d/Y H:i:s');
        unset($data['id']);
        unset($data['created_date']);
        unset($data['created_by']);
        $results = $GLOBALS['db']
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->data($data)
            ->update()
            ->where(self::PRIMARYKEY,"=","'" . $this->id . "'")
            ->put();
        return $this;
    }
}
