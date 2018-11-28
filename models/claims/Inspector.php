<?php namespace Amc;

require_once __DIR__ . '/../../record.php';

class Inspector extends Record{

    const DRIVER = 'mssql';
    const DB = 'AfterMoveCare';
    const TABLE = 'tbl_inspectors';
    const PRIMARYKEY = 'id';

    public $id;
    public $record_number;
    public $inspector_name;
    public $contact;
    public $address;
    public $mail;
    public $city;
    public $state;
    public $zip;
    public $phone;
    public $fax;
    public $map;
    public $county;
    public $code;
    public $vendor;
    public $latitude;
    public $longitude;
    public $email_address;
    public $login_id;
    public $login_password;
    public $user_mgmt;
    public $change_claims;
    public $radius_of_operation_in_miles;
    public $cppc_member_id;
    public $comment_1;
    public $comment_2;
    public $comment_3;
    public $comment_4;
    public $comment_5;
    public $comment_6;
    public $field_4;
    public $field_29;
    public $zip3s;
    public $physical_zip;
    public $type_inspector;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;
    public $guid;
    public $main_hdr_id;

    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
