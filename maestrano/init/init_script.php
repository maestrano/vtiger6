<?php

// Run scripts located under maestrano/init/scripts
// Scripts are run only once on application initialize

error_log("IN RUN SCRIPTS");
// Run init scripts
$init_script_file = 'maestrano/var/_init_scripts';
$init_script_content = file_get_contents($init_script_file);
error_log("INIT SCRIPT CONTENT " . json_encode($init_script_content));
$script_dirs = 'maestrano/init/scripts';
$script_files = array_diff(scandir($script_dirs), array('..', '.'));
error_log("INIT SCRIPT FILES " . json_encode($script_files));
// Iterate over already loaded scripts
foreach ($script_files as $script_file) {
error_log("PROCESSING SCRIPT " . json_encode($script_file));
  $contained = strpos($init_script_content, $script_file);
  if($contained !== 0) {
    // Run script file
    require_once($script_dirs . "/" . $script_file);
    file_put_contents($init_script_file, $script_file . "\n", FILE_APPEND);
  }
}