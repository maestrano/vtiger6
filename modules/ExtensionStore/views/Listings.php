<?php

/*
 * Copyright (C) www.vtiger.com. All rights reserved.
 * @license Proprietary
 */

class ExtensionStore_Listings_View extends Vtiger_Index_View{
    
    public function __construct() {
        parent::__construct();
        $this->exposeMethod('getPromotions');
    }
    
    public function process(Vtiger_Request $request) {
        $mode = $request->getMode();
        if (!empty($mode)) {
            $this->invokeExposedMethod($mode, $request);
            return;
        }
    }
    
    /**
     * Function to get news listings by passing type as News
     */
    protected function getPromotions(Vtiger_Request $request) {
        $modelInstance = Settings_ExtensionStore_Extension_Model::getInstance();
        $promotions = $modelInstance->getListings('','Promotion');
        $qualifiedModuleName = $request->getModule(false);

        $viewer = $this->getViewer($request);
        $viewer->assign('PROMOTIONS', $promotions);
        $viewer->assign('QUALIFIED_MODULE', $qualifiedModuleName);
        $viewer->view('Promotions.tpl', $qualifiedModuleName);
    }
}