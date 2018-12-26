<?php


require_once __DIR__ . '/models/rates/RateFactory.php';


$c = RateFactory::calculateLkar(2018,2);
print_r($c->errors);

exit;

$scac = RateFactory::buildScac("AAMG",2,2018);
print_r($scac);



exit;

require_once __DIR__ . '/processes/traffic/VanOperator.php';

$gbl_dps = 'FDNT0000000';

$input = new stdClass();
$input->delivery_date_eta_early_time = "0:0";
$input->delivery_date_eta_late_time = "0:0";
$input->delivery_eta_date = "11/14/2018";
$input->final_load_eta_date = "1/1/1970";
$input->gross_weight = 0;
$input->is_overflow = false;
$input->necessity_item_description = "";
$input->necessity_items_left = false;
$input->tare_weight = 0;

$v = new VanOperator($gbl_dps,$input);
