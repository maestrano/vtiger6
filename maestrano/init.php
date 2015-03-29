<?php

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__FILE__) . '/../'); }
chdir(ROOT_PATH);

// vTiger libraries
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';
require_once 'modules/Users/Users.php';
require_once 'include/logging.php';
require_once 'modules/Users/models/Module.php';
require_once('include/database/PearDatabase.php');
require_once('modules/Accounts/Accounts.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Leads/Leads.php');
require_once('modules/Contacts/Contacts.php');
require_once('modules/Emails/Emails.php');
require_once('modules/Calendar/Activity.php');
require_once('modules/Documents/Documents.php');
require_once('modules/Potentials/Potentials.php');
require_once('modules/Users/Users.php');
require_once('modules/Products/Products.php');
require_once('modules/HelpDesk/HelpDesk.php');
require_once('modules/Vendors/Vendors.php');
require_once('include/utils/UserInfoUtil.php');
require_once('modules/CustomView/CustomView.php');
require_once 'modules/PickList/PickListUtils.php';
require_once('modules/Invoice/Invoice.php');
require_once('modules/Quotes/Quotes.php');
require_once('modules/PurchaseOrder/PurchaseOrder.php');
require_once('modules/SalesOrder/SalesOrder.php');

// Include Maestrano required libraries
require_once('vendor/maestrano/maestrano-php/lib/Maestrano.php');
require_once('maestrano/connec/init.php');

Maestrano::configure(ROOT_PATH . 'maestrano.json');
