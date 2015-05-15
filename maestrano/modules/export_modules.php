<?php

  if (!defined('ROOT_PATH')) { define("ROOT_PATH", realpath(dirname(__FILE__) . '/../../')); }
  chdir(ROOT_PATH);

  // Export Event and Tickets modules
  require_once('include/utils/utils.php');
  $package = new Vtiger_Package();
  $package->export(Vtiger_Module::getInstance('EventManagement'),'',"EventManagement.zip",false);
  $package->export(Vtiger_Module::getInstance('EventTicket'),'',"EventTicket.zip",false);

?>
