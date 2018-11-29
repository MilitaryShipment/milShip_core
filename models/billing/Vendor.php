<?php

require_once __DIR__ . '/../../record.php';
require_once __DIR__ . '/EpayImage.php';
// require_once __DIR__ . '/../greatPlains/greatPlains.php';

class Vendor extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_vendors';
    const EPAYIMAGES = 'tbl_epay_images';
    const PRIMARYKEY = 'vendor_number';

    public $id;
    public $agency_number;
    public $vendor_number;
    public $claims_email_address;
    public $claims_name;
    public $claims_address1;
    public $claims_city;
    public $claims_state;
    public $claims_zip;
    public $claims_phone;
    public $claims_fax;
    public $claims_address2;
    public $customer_number;
    public $vendor_name;
    public $mc;
    public $scac_code;
    public $a_r_year_to_date_sales;
    public $a_p_year_to_date_purchases;
    public $balance_from_a_r;
    public $balance_from_a_p;
    public $a_r_primary_gl_acct_code;
    public $a_p_primary_gl_acct_code;
    public $address;
    public $city;
    public $state;
    public $zip;
    public $phone;
    public $fax;
    public $contact_1_name;
    public $contact_1_phone;
    public $contact_2_name;
    public $contact_2_phone;
    public $last_update_of_a_r_balance;
    public $last_update_of_a_p_balance;
    public $old_vendor_number;
    public $relation_code;
    public $address2;
    public $fed_id_number;
    public $extension_a_p;
    public $extension_a_r;
    public $vendor_email_address;
    public $record_number;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($vendor_number = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$vendor_number);
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
    public function getEpayImages(){
        $images = array();
        $results = $GLOBALS['db']
			->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(EpayImage::TABLE)
            ->select(EpayImage::PRIMARYKEY)
            ->where("vendor_id = '$this->vendor_number'")
            ->get();
        while($row = mssql_fetch_assoc($results)){
            $images[] = new EpayImage($row[EpayImage::PRIMARYKEY]);
        }
        return $images;
    }
    public function getAchInfo(){
        $gp = new \GP\GreatPlains($this->vendor_number);
        if(!$data = $gp->getAchInfo()){
            return false;
        }
        return $data;
    }
}
