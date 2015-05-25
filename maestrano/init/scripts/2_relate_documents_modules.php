<?php

chdir(ROOT_PATH);

include_once 'modules/ModComments/ModComments.php';

// Create related lists links between modules
createRelateList('EventManagement', 'Documents', 'Documents');
createRelateList('Campaigns', 'Documents', 'Documents');

// Add Comments module
ModComments::addWidgetTo('EventManagement');
ModComments::addWidgetTo('EventTicket');
