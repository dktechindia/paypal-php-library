<?php
require_once('../../../autoload.php');
require_once('../../../includes/config.php');

$configArray = array(
    'ClientID' => $rest_client_id,
    'ClientSecret' => $rest_client_secret
);
$PayPal = new angelleye\PayPal\rest\billing\BillingAPI($configArray);

$planid = 'P-94C59925DN0148020MSKAUAA';                                  // Required. The ID of the billing plan to update. 

$state = 'ACTIVE';                                   // Allowed values: CREATED, ACTIVE, INACTIVE, and DELETED. Status of the billing plan.

$items = array(
    'op'      => 'replace',                           // Possible values: add, remove, replace, move, copy, test. The operation to perform.
    'path'    => '/',                           // A JSON pointer. References a location in the target document where the operation is performed.
);

$returnArray = $PayPal->update_plan($planid,$items,$state);
echo "<pre>";
print_r($returnArray);
