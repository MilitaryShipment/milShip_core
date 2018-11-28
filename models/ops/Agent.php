<?php

require_once __DIR__ . '/../../record.php';
// require_once __DIR__ . '/../dc/missingItem.php';
// require_once __DIR__ . '/../dc/webImage.php';
// require_once __DIR__ . '/../greatPlains/greatPlains.php';
require_once __DIR__ . '/../billing/Vendor.php';
require_once __DIR__ . '/../comms/Notification.php';
require_once __DIR__ . '/../comms/MobileTrafficResponse.php';
require_once __DIR__ . '/../comms/contract/Contract.php';
require_once __DIR__ . '/../claims/Claim.php';
require_once __DIR__ . '/../claims/WebImageTmp.php';
require_once __DIR__ . '/../billing/EpayImage.php';
require_once __DIR__ . '/../billing/WebUser.php';

require_once __DIR__ . '/Driver.php';
require_once __DIR__ . '/Dispatcher.php';
require_once __DIR__ . '/Lumper.php';
require_once __DIR__ . '/Contact.php';
require_once __DIR__ . '/Shipment.php';
require_once __DIR__ . '/Cog.php';



class Agent extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
//    const TABLE = 'tbl_agents';
    const TABLE = 'tbl_agents_test';
    const PRIMARYKEY = 'agentid_number';

    public $agentid_number;
    public $agent_name;
    public $contact_title_1;
    public $contact_email_1;
    public $email_validation_status;
    public $email_validation_date;
    public $whos_auth;
    public $fax_number;
    public $mailing_address2;
    public $physical_address2;
    public $roster_comments1;
    public $roster_comments2;
    public $roster_comments3;
    public $apu_charge;
    public $mailing_address;
    public $mailing_city;
    public $mailing_state;
    public $mailing_zip;
    public $phone_number;
    public $alt_phone_number;
    public $contact;
    public $agent_type1;
    public $booking_agent_yes_no;
    public $agent_type2;
    public $vendorid_number;
    public $comp_schedule_over_500;
    public $physical_county;
    public $gbloc_area_1_also_put_in_b4;
    public $gbloc_area_2;
    public $gbloc_area_3;
    public $gbloc_area_4;
    public $physical_address;
    public $physical_city;
    public $physical_state;
    public $physical_zip;
    public $gbloc_code1_also_put_in_b4;
    public $gbloc_code2;
    public $gbloc_code3;
    public $gbloc_code4;
    public $base_name1;
    public $base_name2;
    public $base_name3;
    public $base_name4;
    public $jppso_agent_code;
    public $css_email_sent;
    public $carb_compliant;
    public $full_legal_name;
    public $field_54;
    public $scn_3_comments_91;
    public $scn_3_comments_92;
    public $scn_3_comments_93;
    public $scn_3_comments_94;
    public $scn_3_comments_95;
    public $scn_3_comments_96;
    public $scn_3_comments_97;
    public $scn_3_comments_98;
    public $scn_3_comments_99;
    public $scn_3_comments_100;
    public $scn_3_comments_101;
    public $scn_3_comments_102;
    public $scn_3_comments_103;
    public $scn_3_comments_104;
    public $scn_3_comments_105;
    public $scn_3_comments_106;
    public $scn_3_comments_107;
    public $scn_3_comments_108;
    public $comm_como_schedule_under_500;
    public $agent_affiliation;
    public $can_use_web_site;
    public $web_password;
    public $guest_web_password;
    public $dispatch_label;
    public $c2_dispatch_email_address;
    public $agency_manager_name;
    public $c2_agency_manager_email_address;
    public $contact_name3;
    public $c2_contact3_email_name;
    public $contact_name4;
    public $contact4_email_name;
    public $sit_last_assigned;
    public $sit_name_assigned;
    public $sit_sequence;
    public $primary_carrier_affiliation;
    public $gpsd;
    public $gpsm;
    public $gpss;
    public $gpsdir;
    public $gpsd2;
    public $gpsm2;
    public $gpss2;
    public $gpsdir2;
    public $dec_lat_90_x_90;
    public $dec_long_180_y_180;
    public $sales_id;
    public $base_latitude;
    public $base_longitude;
    public $grid_number;
    public $sit_not_avail_start_date;
    public $sit_not_avail_end_date;
    public $agent_gbloc_from_getagnt;
    public $agent_gbloc_area_from_getagnt;
    public $common_owner_groupid;
    public $radius_of_operation;
    public $can_accept_sit;
    public $date_webreg_e_mail_sent;
    public $region;
    public $is_military_approved;
    public $deadbeat_agent;
    public $start_of_agent_affiliation;
    public $end_of_agent_affiliation;
    public $remit_to_addr1;
    public $remit_to_addr2;
    public $remit_to_city;
    public $remit_to_county;
    public $remit_to_zip;
    public $use_mailing_address_instead_of_physical;
    public $average_delivery_to_scan_last_90_days;
    public $average_delivery_to_scan_overall;
    public $getagnt_base_name;
    public $gblc_gbla_1;
    public $gblc_gbla_2;
    public $gblc_gbla_3;
    public $gblc_gbla_4;
    public $gblc_gbla_getagnt;
    public $base_state_1;
    public $base_state_2;
    public $base_3_state;
    public $base_4_state;
    public $getagnt_base_state;
    public $contact_name_1;
    public $contact_name_2;
    public $contact_name_3;
    public $contact_name_4;
    public $contact_name_5;
    public $contact_name_6;
    public $contact_name_7;
    public $contact_name_8;
    public $contact_name_9;
    public $contact_name_10;
    public $contact_title_2;
    public $contact_title_3;
    public $contact_title_4;
    public $contact_title_5;
    public $contact_title_6;
    public $contact_title_8;
    public $contact_title_9;
    public $contact_title_10;
    public $contact_email_2;
    public $contact_email_3;
    public $contact_email_4;
    public $contact_email_5;
    public $contact_email_6;
    public $contact_email_7;
    public $contact_email_8;
    public $contact_email_9;
    public $contact_email_10;
    public $date_agreement_sent;
    public $date_recvd_from_agent;
    public $date_final_copy_returned_to_agent;
    public $agreement_printed;
    public $agreement_version;
    public $dps_booking_agent;
    public $field_260;
    public $roster_comments_4;
    public $roster_comments_6;
    public $field_266;
    public $contact_email_category_142;
    public $contact_email_category_144;
    public $contact_email_category_146;
    public $contact_email_category_1;
    public $contact_email_category_2;
    public $icc_mc_icc;
    public $dot;
    public $view_tonnage_list;
    public $fed_id_number;
    public $smith_waterman;
    public $termination_remark;
    public $guid;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $tonnage_blackList;
    public $termination_date;
    public $remit_to_state;
    public $roster_comments_5;
    public $status_id;



    public function __construct($agent_id = null)
    {
        parent::__construct(self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$agent_id);
    }
    public function getDrivers(){
        return Driver::get('agent_id',$this->agentid_number);
    }
    public function getDispatchers(){
        return Dispatcher::get('agent_id',$this->agentid_number);
    }
    public function getContacts(){
        return Contact::get('agent_id',$this->agentid_number);
    }
    public function getLumpers(){
        return Lumper::get('agent_id',$this->agentid_number);
    }
    public function getVendorData(){
        return new Vendor($this->vendorid_number);
    }
    public function getEpayImages($option){
        return EpayImage::get('agent_id',$this->agentid_number,$option);
    }
    public function getshipments($option){
        $keys = array('orig_agent_id','dest_agent_id','hauler_agent_id','hauler_carrier_id');
        $shipments = array();
        foreach($keys as $key){
            $shipments = array_merge($shipments,Shipment::get($key,$this->agentid_number,$option));
        }
        return $shipments;
    }
    public function getNotifications(){
        return Notification::get('agent_id',$this->agentid_number);
    }
    public function getResponses(){
        return MobileTrafficResponse::get('agent_id',$this->agentid_number);
    }
    public function getRemittance($gbl_dps){
        return EpayImage::remittance($this->agentid_number,$gbl_dps);
    }
    public function getPendingPpwk(){
        $keys = array('oa_id','hauler_id','da_id');
        $ppwk = array();
        foreach($keys as $key){
            $ppwk = array_merge($ppwk,DCMissingItem::get($key,$this->agentid_number,'active'));
        }
        return $ppwk;
    }
    public function getClaims($option){
        $ids = array('pack_id','haul1_id','haul2_id','hauler_carrier_id','stg1_id','other_id');
        $claims = array();
        foreach($ids as $id){
            $claims = array_merge($claims,\Amc\Claim::get($id,$this->agentid_number));
        }
        switch(strtolower($option)){
            case 'all':
                return $claims;
                break;
            case 'active':
                $count = count($claims);
                for($i = 0; $i < $count; $i++){
                    if(!$debts = $this->hasOutStandingPayment($claims[$i]->gbl_number)){unset($claims[$i]);}
                }
                break;
            case 'history':
                $count = count($claims);
                for($i = 0; $i < $count; $i++){
                    if($debts = $this->hasOutStandingPayment($claims[$i]->gbl_number)){unset($claims[$i]);}
                }
                break;
            default:
                throw new Exception('Invalid Claim Option');
        }
        return array_values($claims);
    }
    public function getWebClaims($option){
        switch ($option){
            case "all":
                $claims = $this->getClaims($option);
                break;
            case "active":
                $claims = $this->getClaims($option);
                break;
            case "history":
                $claims = $this->getClaims($option);
                break;
            default:
                throw new Exception('Invalid Web Claim Option');
        }
        $count = count($claims);
        for($i = 0; $i <= $count; $i++){
            $docs = $this->getClaimDocs($claims[$i]->gbl_number);
            if(!count($docs)){unset($claims[$i]);}
        }
        return array_values($claims);
    }
    public function getClaimDocs($gbl_dps){
        return \Amc\WebImageTmp::getDocs($this->agentid_number,$gbl_dps);
    }
    public function getBlackOuts($option){
        return Blackout::get('agent_id',$this->agentid_number,$option);
    }
    public function addBlackOut($fields){
        $b = new Blackout();
        $fields->blackout_start_date = date("m/d/Y H:i:s",$fields->blackout_start_date);
        $fields->blackout_end_date = date("m/d/Y H:i:s",$fields->blackout_end_date);
        $fields->created_by = $this->agentid_number;
        $fields->agent_id = $this->agentid_number;
        $fields->agent_name = $this->agent_name;
        $b->setFields($fields)->create();
        return $b;
    }
    public function hasOutStandingPayment($gbl_dps){
        $gp = new \GP\GreatPlains($this->vendorid_number);
        if(!$payment = $gp->hasOutStandingPayment($gbl_dps)){
            return false;
        }
        return $payment;
    }
    public function getWebUsers(){
        return WebUser::get('agent_number',$this->agentid_number);
    }
    public function getCog(){
        if(empty($this->common_owner_groupid) || is_null($this->common_owner_groupid)){
            return false;
        }
        return new Cog($this->common_owner_groupid);
    }
    public function getAchInfo(){
        $gp = new \GP\GreatPlains($this->vendorid_number);
        if(!$data = $gp->getAchInfo()){
            return false;
        }
        return $data;
    }
    public function getContracts(){
        return Contract::get('agent_id',$this->agentid_number);
    }
}
class Blackout extends Record{

    const DRIVER = 'mssql';
    const DB = 'Sandbox';
    const TABLE = 'tbl_agents_blackouts';
    const PRIMARYKEY = 'id';

    public $id;
    public $guid;
    public $record_number;
    public $agents_id;
    public $agent_id;
    public $agent_name;
    public $orig_dest;
    public $blackout_start_date;
    public $blackout_end_date;
    public $type_blackout;
    public $comment;
    public $gbloc;
    public $area;
    public $base_name;
    public $created_by;
    public $created_date;
    public $updated_by;
    public $updated_date;
    public $status_id;

    public function __construct($id = null){
        parent::__construct(self::DRIVER,self::DB,self::TABLE,self::PRIMARYKEY,$id);
    }

    protected function _iterateRecordNumber(){
        $results = $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select("record_number")
            ->orderBy("id desc")
            ->take(1)
            ->get('value');
        $this->record_number = $results++;
        return $this;
    }
    public static function get($key,$value,$option){
        $data = array();
        $GLOBALS['db']
            ->suite(self::DRIVER)
            ->driver(self::DRIVER)
            ->database(self::DB)
            ->table(self::TABLE)
            ->select(self::PRIMARYKEY)
            ->where($key,"=",$value);
        switch (strtolower($option)){
            case 'all':
                $results = $GLOBALS['db']->get();
                break;
            case 'active':
                $results = $GLOBALS['db']
                    ->andWhere("cast(blackout_start_date as date)","<","GETDATE()")
                    ->andWhere("cast(blackout_end_date as date)",">","GETDATE()")
                    ->get();
                break;
            case 'history':
                $results = $GLOBALS['db']
                    ->andWhere("cast(blackout_start_date as date)","<","GETDATE()")
                    ->andWhere("cast(blackout_end_date as date)","<","GETDATE()")
                    ->get();
                break;
            case 'pending':
                $results = $GLOBALS['db']
                    ->andWhere("cast(blackout_start_date as date)",">","GETDATE()")
                    ->get();
                break;
            default:
                throw new Exception('Invalid Blackout Option');
        }
        while($row = mssql_fetch_assoc($results)){
            $data[] = new self($row[self::PRIMARYKEY]);
        }
        return $data;
    }
}
