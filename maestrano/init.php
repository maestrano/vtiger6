<?php

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__FILE__) . '/../'); }

// Include Maestrano required libraries
chdir(ROOT_PATH);
require_once 'vendor/autoload.php';
Maestrano::configure(ROOT_PATH . 'maestrano.json');

// vTiger libraries

include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';

require_once 'modules/Users/Users.php';
require_once 'include/logging.php';
require_once 'modules/Users/models/Module.php';

// chdir(ROOT_PATH . '/maestrano');
require_once 'maestrano/app/sso/MnoSsoUser.php';
require_once 'maestrano/connec/init.php';
