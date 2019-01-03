<?php

require_once __DIR__ . '/../../record.php';

class DcNote extends Record{

    const DRIVER = 'mysql';
    const DB = 'daily';
    const TABLE = 'dc_notes';
    const PRIMARYKEY = 'id';

    public $id;
    public $guid;
    public $gbl;
    public $flag;
    public $response;
    public $tid;
    public $contact;
    public $note;
    public $followup_date;
    public $followup_time;
    public $email_body;
    public $email_recipients;
    public $sent;
    public $rec_created;
    public $rec_modified;

    public function __construct($id = null)
    {
      parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
