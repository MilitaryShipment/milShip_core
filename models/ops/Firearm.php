<?php

require_once __DIR__ . '/../../record.php';

class Firearm extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'ctl_mobile_firearms';
    const PRIMARYKEY = 'id';

    public $id;
    public $guid;
    public $record_number;
    public $gbl_dps;
    public $page;
    public $members_name;
    public $mc;
    public $make;
    public $model;
    public $serial;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
    public static function getAll($gbl_dps){
        $data = array();
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("gbl_dps","=","'" . $gbl_dps . "'")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $data[] = new self($row[self::PRIMARYKEY]);
        }
        return $data;
    }
}
