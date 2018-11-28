<?php

require_once __DIR__ . '/../record.php';

class User extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_users';
    const PRIMARYKEY = 'id';

    public $id;
    public $username;
    public $passwd;
    public $employee_number;
    public $first_name;
    public $last_name;
    public $addr1;
    public $addr2;
    public $city;
    public $state;
    public $zip;
    public $department;
    public $title;
    public $super_tid;
    public $ext;
    public $security;
    public $weekend_warrior;
    public $personal_cell;
    public $rc_direct_phone;
    public $mobile_validation_status;
    public $mobile_validation_date;
    public $personal_email;
    public $personal_email_validation_status;
    public $personal_email_validation_date;
    public $email;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;
    public $rec_created;
    public $rec_last_modified;

    public function __construct($id = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
    public static function get($key,$value){
        $ids = array();
        $data = array();
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where($key,"=",$value)
            ->get();
        while($row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data;
    }
    public static function getUserByFullName($fullname){
        $ids = array();
        $data = array();
        $firstName = explode(' ',$fullname)[0];
        $lastName = explode(' ',$fullname)[1];
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("first_name","=",$firstName)
            ->andWhere("last_name","=",$lastName)
            ->get();
        while($row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data[0];
    }
    public static function getUserByTid($tid){
        $ids = array();
        $data = array();
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where("username","=",$tid)
            ->get();
        while($row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC)){
            $ids[] = $row[self::PRIMARYKEY];
        }
        foreach($ids as $id){
            $data[] = new self($id);
        }
        return $data[0];
    }
}