<?php

require_once __DIR__ . '/../../record.php';

class DCMissingItem extends Record{

    const DRIVER = 'mysql';
    const DB = 'daily';
    const TABLE = 'dc_missing_items';
    const PRIMARYKEY = 'id';

    public $id;
    public $gbl;
    public $scac;
    public $pu_date;
    public $member_name;
    public $rank;
    public $missing_items;
    public $order_number;
    public $da_id;
    public $da;
    public $da_phone;
    public $da_email;
    public $oa_id;
    public $oa;
    public $oa_phone;
    public $oa_email;
    public $hauler_id;
    public $hauler;
    public $hauler_phone;
    public $hauler_email;
    public $hauler_agent_id;
    public $hauler_agent;
    public $hauler_agent_phone;
    public $uncanned_documents;
    public $delivered_warehouse;
    public $delivered_residence;
    public $completed;
    public $date_completed;
    public $tid;
    public $transdocs;
    public $hold_weight_tickets;
    public $demand_email_sent_today;
    public $pointer;
    public $canceled;
    public $locked;
    public $ignored;
    public $on_hold;
    public $haul_only;
    public $sent;
    public $date_sent;
    public $manually_sent_to_base;
    public $driverMsg;
    public $msg_sent_driver;
    public $msg_sent_driver_date;
    public $msg_sent_to;
    public $msg_sent;
    public $message;
    public $base_email_body;
    public $g11_origin_date;
    public $early_pickup;
    public $missing_weight;
    public $scanned_weight;
    public $updated_weight;
    public $validated_weight;
    public $updated_weight_datetime;
    public $validated_weight_datetime;
    public $rec_created;
    public $rec_modified;
    public $status_id;

    public function __construct($id = null)
    {
      parent::__construct(self::DRIVER,self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }
}
