<?php

require_once __DIR__ . '/../../record.php';

class DirectResponse extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'ctl_direct_responses';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl_dps;
    public $cog;
    public $scac;
    public $page;
    public $shipper;
    public $message_sent;
    public $message_received;
    public $fromName;
    public $driver_id;
    public $driver_name;
    public $driver_phone;
    public $mc;
    public $header;
    public $reviewed;
    public $failed;
    public $validation;
    public $subject;
    public $fromAddr;
    public $toAddr;
    public $messageDate;
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
