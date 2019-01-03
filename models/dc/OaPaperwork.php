<?php

require_once __DIR__ . '/../../record.php';

class OaPaperwork extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'ref_oapaperwork';
    const PRIMARYKEY = 'id';

    public $id;
    public $guid;
    public $gbl_dps;
    public $scac;
    public $full_name;
    public $pickup_date;
    public $ogbloc;
    public $area;
    public $pickup_type;
    public $form_name_1;
    public $form_location_1;
    public $form_entered_date_1;
    public $form_name_2;
    public $form_location_2;
    public $form_entered_date_2;
    public $form_name_3;
    public $form_location_3;
    public $form_entered_date_3;
    public $form_name_4;
    public $form_location_4;
    public $form_entered_date_4;
    public $form_completed_date;
    public $haul_only;
    public $mayflower;
    public $record_number;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null)
    {
      parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
