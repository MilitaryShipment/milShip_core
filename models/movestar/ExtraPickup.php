<?php namespace MoveStar;

require_once __DIR__ . '/../../record.php';

class ExtraPickup extends \Record{

    const DRIVER = 'mssql';
    const DB = 'ezshare';
    const TABLE = 'ref_extra_pickups';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl_dps;
    public $location_type;
    public $address;
    public $city;
    public $state;
    public $zip;
    public $sq_footage;
    public $status_id;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
