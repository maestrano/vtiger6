<?php

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__FILE__) . '/../'); }

// Include Maestrano required libraries
chdir(ROOT_PATH);
require_once('vendor/maestrano/maestrano-php/lib/Maestrano.php');

Maestrano::configure(ROOT_PATH . 'maestrano.json');
