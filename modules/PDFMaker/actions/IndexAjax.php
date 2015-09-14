<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

error_reporting(0);

class PDFMaker_IndexAjax_Action extends Settings_Vtiger_Basic_Action {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('downloadMPDF');
        $this->exposeMethod('savePDFBreakline');
    }

    function process(Vtiger_Request $request){
        $mode = $request->get('mode');
        if(!empty($mode)) {
                $this->invokeExposedMethod($mode, $request);
                return;
        }        
        $type = $request->get('type');
    }
            
    public function downloadMPDF(Vtiger_Request $request){
        $error == "";
        $srcZip = "http://www.crm4you.sk/PDFMaker/src/mpdf.zip";
        $trgZip = "modules/PDFMaker/resources/mpdf.zip";
        if (copy($srcZip, $trgZip)) {
            require_once('vtlib/thirdparty/dUnzip2.inc.php');
            $unzip = new dUnzip2($trgZip);
            $unzip->unzipAll(getcwd() . "/modules/PDFMaker/resources/");
            if ($unzip)
                $unzip->close();

            if (!is_dir("modules/PDFMaker/resources/mpdf")) {
                $error = vtranslate("UNZIP_ERROR", 'PDFMaker');
                $viewer->assign("STEP", "error");
                $viewer->assign("ERROR_TBL", $errTbl);
            } 
    
        } else {
            $error = vtranslate("DOWNLOAD_ERROR", 'PDFMaker');
        }

        if ($error == "") {
             $result = array('success' => true, 'message' => '');
        } else {
             $result = array('success' => false, 'message' => $error);
        }
        
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    } 
    
    public function savePDFBreakline(Vtiger_Request $request){
        $crmid = $request->get("record");

        $adb = PearDatabase::getInstance();
        
        $sql1 = "DELETE FROM vtiger_pdfmaker_breakline WHERE crmid=?";
        $adb->pquery($sql1, array($crmid));

        $breaklines = rtrim($request->get("breaklines"), "|");

        if ($breaklines != "") {
            $show_header = $request->get("show_header");
            $show_subtotal = $request->get("show_subtotal");
             
            $sql2 = "INSERT INTO vtiger_pdfmaker_breakline (crmid, productid, sequence, show_header, show_subtotal) VALUES (?,?,?,?,?)";

            $show_header_val = $show_subtotal_val = "0";
            if ($show_header == "true")
                $show_header_val = "1";
            if ($show_subtotal == "true")
                $show_subtotal_val = "1";

            $products = explode("|", $breaklines);
            for ($i = 0; $i < count($products); $i++) {
                list($productid, $sequence) = explode("_", $products[$i], 2);
                $adb->pquery($sql2,array($crmid, $productid, $sequence, $show_header_val, $show_subtotal_val));
            }
        }
        
        $response = new Vtiger_Response();
        $response->setResult(array('success' => true));
        $response->emit();
    }
}