<?php

require_once __DIR__ . '/models/ops/Shipment.php';
require_once __DIR__ . '/models/ops/Agent.php';

$agent = new Agent('I8975');

$users = $agent->getWebUsers();
$claims = $agent->getClaims('all');

foreach($users as $user){
  echo $user->user_login . "\n";
}
foreach($claims as $claim){
  echo $claim->gbl_number . "\n";
  $docs = $agent->getClaimDocs($claim->gbl_number);
  echo "Num Docs: " count($docs) . "\n";
}
