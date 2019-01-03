<?php

require_once __DIR__ . '/../../record.php';


class ScannerForm{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'ctl_scan_form';
    const PRIMARYKEY = 'id';

    public $id;
    public $scan_name;
    public $document_name;
    public $doc_description;
    public $unigroup_doc_type;
    public $is_unigroup_transdocs_authorized;
    public $is_web_authorized;
    public $security_level;
    public $record_number;
    public $unique_field;
    public $guid;
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
