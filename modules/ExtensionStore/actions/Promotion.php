<?php

/*
 * Copyright (C) www.vtiger.com. All rights reserved.
 * @license Proprietary
 */

class ExtensionStore_Promotion_Action extends Vtiger_Index_View{
    
    public function __construct() {
        parent::__construct();
        $this->exposeMethod('maxCreatedOn');
    }
    
    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }
    
    protected function maxCreatedOn(Vtiger_Request $request){
        $modelInstance = Settings_ExtensionStore_Extension_Model::getInstance();
        $promotions = $modelInstance->getMaxCreatedOn('Promotion', 'max', 'createdon');
        $response = new Vtiger_Response();
        if ($promotions['success'] != 'true') {
            $response->setError('', $promotions['error']);
        } else {
            $response->setResult($promotions['response']);
        }
        $response->emit();
    }
}