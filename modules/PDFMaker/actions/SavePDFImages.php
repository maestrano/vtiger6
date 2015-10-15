<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_SavePDFImages_Action extends Vtiger_Action_Controller {

    public function checkPermission(Vtiger_Request $request) {
        
    }

    public function process(Vtiger_Request $request) {
        $moduleName = $request->getModule();
        $crmid = $request->get('record');

        $adb = PearDatabase::getInstance();

        $sql1 = "DELETE FROM vtiger_pdfmaker_images WHERE crmid=?";
        $adb->pquery($sql1, array($crmid));

        $sql2 = "INSERT INTO vtiger_pdfmaker_images (crmid, productid, sequence, attachmentid, width, height) VALUES (?, ?, ?, ?, ?, ?)";
        
        $R_Data = $request->getAll();

        foreach ($R_Data as $key => $value) {
            if (strpos($key, "img_") !== false) {
                list($bin, $productid, $sequence) = explode("_", $key);
                if ($value != "no_image") {
                    $width = $R_Data["width_" . $productid . "_" . $sequence];
                    $height = $R_Data["height_" . $productid . "_" . $sequence];
                    if (!is_numeric($width) || $width > 999)
                        $width = 0;
                    if (!is_numeric($height) || $height > 999)
                        $height = 0;
                } else {
                    $value = 0;
                    $width = 0;
                    $height = 0;
                }
                
                $adb->pquery($sql2, array($crmid, $productid, $sequence, $value, $width, $height));
            }        
        }
    }  
}   