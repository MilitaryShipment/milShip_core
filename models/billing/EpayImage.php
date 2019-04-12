<?php

require_once __DIR__ . '/../../record.php';

class EpayImage extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_epay_images';
    const PRIMARYKEY = 'id';

    public $id;
    public $rec_email_id;
    public $gbl;
    public $gbl_dps;
    public $registration_number;
    public $agent_id;
    public $vendor_id;
    public $vendor_type;
    public $common_owner_group;
    public $agent_name;
    public $address_1;
    public $address_2;
    public $city;
    public $state;
    public $zipcode;
    public $phone;
    public $phone_1;
    public $scac_code;
    public $invoice_type;
    public $invoice_total;
    public $shipper_name;
    public $incoming_image_name;
    public $checked;
    public $checked_date;
    public $comments;
    public $comments_2;
    public $comments_3;
    public $final_image_name;
    public $verified;
    public $verified_date;
    public $path_to_gbl_image;
    public $path_to_web_image;
    public $date_entered_cd;
    public $completed;
    public $web_enabled;
    public $mailed_check;
    public $pointer;
    public $finalized;
    public $notes;
    public $is_image_processed_directory;
    public $is_image_web_enabled_directory;
    public $is_image_gov_doc_directory;
    public $is_image_processed;
    public $is_image_web_enabled;
    public $is_image_gov_doc;
    public $is_image_checked;
    public $missing_images;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;
    public $dollar_amount;
    public $cog_id;

    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
        if(is_null($this->final_image_name)){
            $this->_buildFinalImageName();
        }
    }
    protected function _buildFinalImageName(){
        $pathInfo = pathinfo($this->path_to_web_image);
        $this->final_image_name = $pathInfo['basename'];
        return $this;
    }
    public static function get($key,$value,$option){
        $data = array();
        $ids = array();
        $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where($key,"=","'" . $value . "'");
        switch (strtolower($option)){
            case 'all':
                $results = $GLOBALS['db']->get();
                break;
            case 'active':
                $results = $GLOBALS['db']->andWhere("completed","=",0)->get();
                break;
            case 'complete':
                $results = $GLOBALS['db']->andWhere("completed","=",1)->get();
                break;
            default:
                throw new Exception('Invalid Epay Image Option');
        }
        while($row = mssql_fetch_assoc($results)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
    public static function search($key,$value,$dateField = null,$maxDate = null){
      $data = array();
      $GLOBALS['db']
          ->suite(self::DRIVER)
          ->driver(self::DRIVER)
          ->database(self::DB)
          ->table(self::TABLE)
          ->select(self::PRIMARYKEY)
          ->where($key,"like","'%" . $value . "%'");
      if(!is_null($maxDate)){
        $GLOBALS['db']->andWhere("cast(" . $dateField . " as date)",">=","cast('" . $maxDate . "' as date)");
      }
      $results = $GLOBALS['db']->orderBy("created_date desc")->get();
      while($row = mssql_fetch_assoc($results)){
        $data[] = new self($row[self::PRIMARYKEY]);
      }
      return $data;
    }
    public static function remittance($agentId,$gbl_dps){
        $data = array();
        $ids = array();
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("gbl_dps","like","%$gbl_dps%")
            ->andWhere("agent_id","=","'$agentId'")
            ->andWhere("web_enabled","=",1)
            ->andWhere("status_id","=",1)
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
