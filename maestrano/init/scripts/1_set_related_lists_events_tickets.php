<?php

createRelateList('EventManagement', 'Contacts', 'Contacts');
createRelateList('EventManagement', 'Leads', 'Leads');
createRelateList('EventTicket', 'Contacts', 'Contacts');
createRelateList('EventTicket', 'Leads', 'Leads');
createRelateList('Contacts', 'EventManagement', 'EventManagement');
createRelateList('Leads', 'EventManagement', 'EventManagement');
createRelateList('Contacts', 'EventTicket', 'EventTicket');
createRelateList('Leads', 'EventTicket', 'EventTicket');

function createRelateList($tabname, $targetModule, $relationLabel) {
  $moduleInstance = Vtiger_Module::getInstance($tabname);
  $targetModuleInstance = Vtiger_Module::getInstance($targetModule);
  $relationLabel = $relationLabel;
  $moduleInstance->unsetRelatedList($targetModuleInstance);
  $moduleInstance->setRelatedList($targetModuleInstance, $relationLabel, Array('SELECT'));
}