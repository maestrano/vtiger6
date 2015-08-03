#!/bin/bash

# The modules EventManagement and EventTickets are custom modules packaged with Maestrano Vtiger installation.
# Custom modules need to be packaged and installed when the application is setup the first time.
# This scripts packages up the modules defined in maestrano/modules/export_modules.php and adds the modules archives to the packages directory

php maestrano/modules/export_modules.php
mv test/vtlib/*.zip packages/vtiger/optional/
