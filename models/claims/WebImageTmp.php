<?php namespace Amc;

require_once __DIR__ . '/../../record.php';

class WebImageTmp extends \Record{

    const DRIVER = 'mysql';
    const DB = 'structure';
    const TABLE = 'web_images_tmp';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl_dps;
    public $claim_number;
    public $agent_id;
    public $pack_id;
    public $haul1_id;
    public $hauler_carrier_id;
    public $haul2_id;
    public $stg1_id;
    public $other_id;
    public $mco_id;
    public $type;
    public $tape_transaction_number;
    public $form_name;
    public $target_location;
    public $agent_comments;
    public $comments;
    public $is_web_enabled;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
    public static function getDocs($agentId,$gbl_dps){
        $ids = array();
        $data = array();
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("(agent_id","like","'%$agentId%'")
            ->orWhere("pack_id","=","'" . $agentId . "'")
            ->orWhere("haul1_id","=","'" . $agentId . "'")
            ->orWhere("hauler_carrier_id","=","'" . $agentId . "'")
            ->orWhere("haul2_id","=","'" . $agentId . "'")
            ->orWhere("stg1_id","=","'" . $agentId . "'")
            ->orWhere("other_id","=","'$agentId')")
            ->andWhere("gbl_dps","=","'" . $gbl_dps . "'")
            ->andWhere("is_web_enabled","=",1)
            ->andWhere("status_id","=",1)
            ->get();
        while($row = mysql_fetch_assoc($results)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
}
