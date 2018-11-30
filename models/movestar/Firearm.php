<?php namespace MoveStar;

require_once __DIR__ . '/../../record.php';

class Firearm extends \Record{

    const DRIVER = 'mssql';
    const DB = 'ezshare';
    const TABLE = 'ref_firearms';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl_dps;
    public $make;
    public $model;
    public $serial;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$gbl_dps);
    }
    public static function recordExists($make,$model,$serial,$gbl_dps){
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("make = '$make'")
            ->andWhere("model = '$model'")
            ->andWhere("serial = '$serial'")
            ->andWhere("gbl_dps = '$gbl_dps'")
            ->get();
        if(!mssql_num_rows($results)){
            return false;
        }
        return true;
    }
}
