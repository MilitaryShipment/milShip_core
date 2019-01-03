<?php

require_once __DIR__ . '/../../record.php';

class RecEmail extends Record{

    const DRIVER = 'mysql';
    const DB = 'daily';
    const TABLE = 'rec_email';
    const PRIMARYKEY = 'id';

    public $id;
    public $scanner_audit_id;
    public $gbl;
    public $order_number;
    public $member_name;
    public $doc_date;
    public $doc_type;
    public $doc_path;
    public $billed;
    public $delivered;
    public $delivered_warehouse;
    public $delivered_residence;
    public $delivered_warehouse_residence;
    public $hc10000;
    public $released;
    public $completed;
    public $date_completed;
    public $tid;
    public $rec_created;
    public $rec_modified;
    public $guid;
    public $created_by;
    public $create_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null)
    {
      parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
