<?php

require_once __DIR__ . '/../../record.php';

class ScannerRuleHistory extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_scan_rules_history';
    const PRIMARYKEY = 'id';

    public $id;
    public $guid;
    public $rule_name;
    public $form_name;
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
    }
}
