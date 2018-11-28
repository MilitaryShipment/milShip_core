<?php

require_once __DIR__ . '/../../record.php';

class Scac extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_company';
    const PRIMARYKEY = 'scac';

    public $id;
    public $guid;
    public $company_no;
    public $scac;
    public $agent_booking_number_409;
    public $company_name;
    public $name_on_heading_of_invoices;
    public $address_1;
    public $address_2;
    public $city;
    public $state;
    public $zip;
    public $phone;
    public $toll_phone;
    public $phone_fax;
    public $code;
    public $payee_code;
    public $federal_id_number;
    public $e_mail_domain;
    public $a0_bankaccountnumber;
    public $a0_backaccountnumberpulaskibank;
    public $reserved_for_future_bank_account_25;
    public $reserved_for_future_bank_account_26;
    public $color_file;
    public $field004;
    public $record_number;
    public $negative_survey_push;
    public $negative_survey_push_date;
    public $positive_survey_push;
    public $positive_survey_push_date;
    public $rush_survey_push;
    public $rush_survey_push_date;
    public $request_survey_push;
    public $request_survey_push_date;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($scac = null)
    {
        parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$scac);
    }
}
