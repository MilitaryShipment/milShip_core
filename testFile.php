<?php

require_once __DIR__ . '/models/ops/Shipment.php';

$gbl = 'BGNC0469443';

$s = new Shipment($gbl);
print_r($s->getNotifications());
print_r($s->getDriver());
print_r($s->getOA());
