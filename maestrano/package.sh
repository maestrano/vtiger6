#!/bin/bash

# Package the Maesrtrano custom modules
php maestrano/modules/export_modules.php
mv test/vtlib/*.zip packages/vtiger/optional/
