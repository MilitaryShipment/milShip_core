<?php

/*Van Operator is a response to a webpage.
 It accepts [information??] from a driver surrounding a shipment's ??_date
expects format: hour:min [18:22, 19:4] because date is assumed ??_date
expects a default hour:min of '0:0'
*/

require_once __DIR__ . '/../../Messenger.php';
require_once __DIR__ . '/../../models/comms/MobileTrafficResponse.php';
require_once __DIR__ . '/../../models/comms/Notification.php';
require_once __DIR__ . '/../../models/ops/Shipment.php';
require_once __DIR__ . '/../../models/ops/Driver.php';
require_once __DIR__ . '/TrafficResponse.php';

$v = new VanOperator();

class VanOperator extends TrafficResponse{

  const UNTOUCHED = '0:0';
  const PAGE = 'agentLoadETA';
  const MSGFILENAME = 'agentLoadResponse';
  const MSGTYPE = 'internal-email';
  const MSGTO = 'j.watson@allamericanmoving.com';
  const MSGFROM = 'mobileSite@militaryshipment.com';
  const MSGCC = 'webadmin@allamericanmoving.com';

  public $response;
  protected $msgSubject;
  protected $msgBody;

  public function __construct(){}
}
