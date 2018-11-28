<?php

require_once __DIR__ . '/../../record.php';

class Contact extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_agents_contact';
    const PRIMARYKEY = 'id';

    public $id;
    public $agent_id;
    public $department;
    public $contact_name;
    public $title;
    public $email_address;
    public $email_validation_status;
    public $email_validation_date;
    public $phone;
    public $fax;
    public $primary_contact;
    public $comments;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;
    public $first_name;
    public $last_name;


    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
    public static function get($key,$value){
        $data = array();
        $ids = array();
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where($key,"=","'" . $value . "'")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
}
