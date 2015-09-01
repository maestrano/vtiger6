<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_GetRelatedBlockColumns_Action extends Vtiger_Action_Controller {

    public function checkPermission(Vtiger_Request $request) {        
    }

    public function process(Vtiger_Request $request) {

        $current_user = Users_Record_Model::getCurrentUserModel();
        $RelatedBlock = new PDFMaker_RelatedBlock_Model();

        $sec_module = $request->get('secmodule');    
        $pri_module = $request->get('primodule'); 
        $mode = $request->get('mode'); 
        
        $module_list = $RelatedBlock->getModuleList($sec_module);

        $content = "";
        if ($mode == "stdcriteria") {
            $options = $RelatedBlock->getStdCriteriaByModule($sec_module, $module_list, $current_user);
            if (count($options) > 0) {
                foreach ($options AS $value => $label) {
                    $content .= "<option value='" . $value . "'>" . $label . "</option>";
                }
            }
        } else {
            foreach ($module_list AS $blockid => $optgroup) {
                $options = $RelatedBlock->getColumnsListbyBlock($sec_module, $blockid, $pri_module, $current_user);

                if (count($options) > 0) {
                    $content .= "<optgroup label='" . $optgroup . "'>";

                    foreach ($options AS $value => $label) {
                        $content .= "<option value='" . $value . "'>" . $label . "</option>";
                    }
                    $content .= "</optgroup>";
                }
            }
        }

        $response = new Vtiger_Response();
        $response->setResult($content);
        $response->emit();
    }
}