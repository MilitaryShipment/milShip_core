<?php

require_once __DIR__ . "/../../record.php";

class Template extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'ctl_outbound_template';
    const PRIMARYKEY = 'id';

    public $id;
    public $msg_body;
    public $msg_type;
    public $msg_name;
    public $msg_subject;
    public $msg_from;
    public $msg_to;
    public $msg_cc;
    public $msg_bcc;
    public $list_order;
    public $company_id;
    public $developer_comment;
    public $rule;
    public $app_name;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
