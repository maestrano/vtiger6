<?php
/*+**********************************************************************************
 * The content of this scripts sets the database up and install all the vTiger modules
 * ------
 * It assumes that the config.inc.php has been populated with the right parameters
 * and that the database has already been created
 * ------
 * Author: Arnaud Lachaume <arnaud.lachaume@maestrano.com>
 ************************************************************************************/

global $php_max_execution_time;
set_time_limit($php_max_execution_time);

echo "Setting the application up\n";


// Getting the required vTiger libraries
echo "Loading the libraries\n";
require_once('libraries/adodb/adodb.inc.php');
require_once('include/utils/CommonUtils.php');
require_once('includes/Loader.php');
require_once('modules/Install/models/ConfigFileUtils.php');
require_once('modules/Install/models/InitSchema.php');
require_once('modules/Install/models/Utils.php');
require_once('includes/Loader.php');
require_once('includes/runtime/Controller.php');
require_once('includes/runtime/BaseModel.php');
require_once('includes/runtime/Globals.php');
require_once('modules/Migration/views/Index.php');
require_once('vtigerversion.php');

include_once 'vtlib/Vtiger/Module.php';
include_once 'includes/main/WebUI.php';
require_once 'modules/Users/Users.php';
require_once 'modules/Users/models/Module.php';

// Load the setup config file
echo "Loading the vTiger setup configuration\n";
require_once('config.php');

// Check the database connection
echo "Check database connection\n";
$dbCheckResult = Install_Utils_Model::checkDbConnection($dbconfig['db_type'], $dbconfig['db_hostname'], $dbconfig['db_username'], $dbconfig['db_password'], $dbconfig['db_name'], false, true, null, null);

$next = $dbCheckResult['flag'];
$error_msg = $dbCheckResult['error_msg'];
$error_msg_info = $dbCheckResult['error_msg_info'];
$db_utf8_support = $dbCheckResult['db_utf8_support'];

if ($next == true) {
  // Migrate database to latest version
  echo "Migrating vTiger database\n";

  $migrationModuleModel = Migration_Module_Model::getInstance();

  $getAllowedMigrationVersions = $migrationModuleModel->getAllowedMigrationVersions();
  $getDBVersion = str_replace(array('.', ' '),'', $migrationModuleModel->getDBVersion());
  $getLatestSourceVersion = str_replace(array('.', ' '),'', $migrationModuleModel->getLatestSourceVersion());
  $migrateVersions = array();
  foreach($getAllowedMigrationVersions as $getAllowedMigrationVersion) {
    foreach($getAllowedMigrationVersion as $version => $label) {
      if(strcasecmp($version, $getDBVersion) == 0 || $reach == 1) {
        $reach = 1;
        $migrateVersions[] = $version;
      }
    }
  }
  $migrateVersions[] = $getLatestSourceVersion;

  $patchCount  = count($migrateVersions);

  define('VTIGER_UPGRADE', true);

  for($i=0; $i<$patchCount; $i++){
    $filename =  "modules/Migration/schema/".$migrateVersions[$i]."_to_".$migrateVersions[$i+1].".php";
    if(is_file($filename)) {
      if(!defined('INSTALLATION_MODE')) {
        echo "Database migration - ".$migrateVersions[$i]." ==> ".$migrateVersions[$i+1]." Database changes -- Starts.";
      }
      $_i_statesaved = $i;
      include($filename);
      $i = $_i_statesaved;
      if(!defined('INSTALLATION_MODE')) {
        echo "Database migration - ".$migrateVersions[$i]." ==> ".$migrateVersions[$i+1]." Database changes -- Ends.";
      }
    } else if(isset($migrateVersions[$patchCount+1])){
      echo "There is no Database Changes from ".$migrateVersions[$i]." ==> ".$migrateVersions[$i+1];
    }
  }

  //update vtiger version in db
  $migrationModuleModel->updateVtigerVersion();
  // To carry out all the necessary actions after migration
  $migrationModuleModel->postMigrateActivities();

  echo "Application has been upgraded\n";

  exit(0);
} else {
  // Something is wrong with the database
  echo "The database is not setup properly\n";
  echo "$error_msg\n";
  echo "$error_msg_info\n";

  echo "Application is NOT upgraded\n";

  exit(1);
}
echo "SCRIPT END\n";
?>