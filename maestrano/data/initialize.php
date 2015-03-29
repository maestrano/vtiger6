<?php

require_once '../init.php';

// Set default user for entities creation
global $current_user;
if(is_null($current_user)) { $current_user = array(); }
if(!isset($current_user->id)) {
  $current_user->id = "1";
}

if(!Maestrano::param('connec.enabled')) { return false; }

$filepath = 'maestrano/var/_data_sequence';
$status = false;

if (file_exists($filepath)) {
  // Last update timestamp
  $timestamp = trim(file_get_contents($filepath));
  $current_timestamp = round(microtime(true) * 1000);
  if (empty($timestamp)) { $timestamp = 0; } 

  // Fetch updates
  $client = new Maestrano_Connec_Client();
  $msg = $client->get("updates/$timestamp?\$filter[entity]=Organization");
  $code = $msg['code'];
  $body = $msg['body'];

  if($code != 200) {
    error_log("Cannot fetch connec updates code=$code, body=$body");
  } else {
    error_log("Receive updates body=$body");
    $result = json_decode($body, true);

    // Dynamically find mappers and map entities
    foreach(BaseMapper::getMappers() as $mapperClass) {
      $mapper = new $mapperClass();
      $mapper->persistAll($result[$mapper->getConnecResourceName()]);
    }
  }

  $status = true;
}

if ($status) {
  file_put_contents($filepath, $current_timestamp);
}

?>
