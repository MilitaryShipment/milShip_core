<?php

require_once __DIR__ . '/../../record.php';

class WebUser extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_webusers';
    const PRIMARYKEY = 'id';

    public $id;
    public $user_login;
    public $user_password;
    public $agent_number;
    public $company_name;
    public $user_name;
    public $b1_user_e_mail_address_14;
    public $email_validation_status;
    public $email_validation_date;
    public $carrier_scac;
    public $allow_access_to_all_data;
    public $allow_access_to_shipment_rating_bol;
    public $allow_access_to_financials;
    public $allow_access_to_login_management;
    public $register_any_agentid_shipment;
    public $vendor_number;
    public $agent_number_string;
    public $b1_user_e_mail_address_15;
    public $b1_user_e_mail_address_16;
    public $b1_user_e_mail_address_17;
    public $b1_user_e_mail_address_18;
    public $agent_common_owner_id;
    public $need_to_update_info;
    public $page_to_update;
    public $password_days;
    public $password_expiration_date;
    public $password_set_date;
    public $continuation_page;
    public $allow_access_registrations;
    public $phone_number;
    public $extension;
    public $department_title;
    public $a1_gbloc_30;
    public $a1_gbloc_31;
    public $a1_gbloc_32;
    public $a1_gbloc_33;
    public $a1_gbloc_34;
    public $a1_gbloc_35;
    public $a1_gbloc_36;
    public $a1_gbloc_37;
    public $a1_gbloc_38;
    public $a1_gbloc_39;
    public $a2_scac_40;
    public $a2_scac_41;
    public $a2_scac_42;
    public $a2_scac_43;
    public $a2_scac_44;
    public $a2_scac_45;
    public $a2_scac_46;
    public $a2_scac_47;
    public $a2_scac_48;
    public $a2_scac_49;
    public $a2_scac_50;
    public $a2_scac_51;
    public $a2_scac_53;
    public $a2_scac_54;
    public $a2_scac_55;
    public $a2_scac_56;
    public $a2_scac_57;
    public $a2_scac_58;
    public $a2_scac_59;
    public $a1_scac;
    public $a2_scac_61;
    public $a2_scac_62;
    public $a2_scac_63;
    public $a2_scac_64;
    public $a2_scac_65;
    public $a2_scac_66;
    public $a2_scac_67;
    public $a2_scac_68;
    public $a2_scac_69;
    public $e_mail_registration_confirmation;
    public $last_login_date;
    public $last_login_time;
    public $e_mail_e_pay_payment_confirmation;
    public $e_pay_e_mail_sent_on;
    public $e_pay_customer_yn;
    public $a;
    public $aa;
    public $aaa;
    public $aaaa;
    public $qualifier_name;
    public $e_pay_counter;
    public $last_e_pay_used_date;
    public $rating_version;
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
