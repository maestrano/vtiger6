<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_SavePDFTemplate_Action extends Vtiger_Action_Controller {

    public function checkPermission(Vtiger_Request $request) {        
    }

    public function process(Vtiger_Request $request) {
        PDFMaker_Debugger_Model::GetInstance()->Init();
        $adb = PearDatabase::getInstance();
        $cu_model = Users_Record_Model::getCurrentUserModel();
        $PDFMaker = new PDFMaker_PDFMaker_Model();

        $S_Data = $request->getAll();

        $modulename = $request->get('modulename');
        $templateid = $request->get('templateid');
        $filename = $modulename;
        $description = "";
        $body = $S_Data['body'];
        
        $pdf_format = $request->get('pdf_format');
        $pdf_orientation = $request->get('pdf_orientation');

        if (!$templateid)
        {    
            $result = $adb->pquery("SELECT templateid FROM vtiger_pdfmaker WHERE module =?", array($modulename));
            $templateid = $adb->query_result($result,0,"templateid");
        }

        if (isset($templateid) && $templateid != '') {
            $sql = "update vtiger_pdfmaker set filename =?, description =?, body =? where module =?";
            $params = array($filename, $description, $body, $modulename);
            $adb->pquery($sql, $params);

            $sql2 = "DELETE FROM vtiger_pdfmaker_settings WHERE templateid =?";
            $params2 = array($templateid);
            $adb->pquery($sql2, $params2);
        } else {
            $templateid = $adb->getUniqueID('vtiger_pdfmaker');
            $sql3 = "insert into vtiger_pdfmaker (filename,module,description,body,deleted,templateid) values (?,?,?,?,?,?)";
            $params3 = array($filename, $modulename, $description, $body, 0, $templateid);
            $adb->pquery($sql3, $params3);
        }

        $margin_top = $request->get('margin_top');
        if ($margin_top < 0) $margin_top = 0;

        $margin_bottom = $request->get('margin_bottom');
        if ($margin_bottom < 0) $margin_bottom = 0;    
            
        $margin_left = $request->get('margin_left');
        if ($margin_left < 0) $margin_left = 0; 
        
        $margin_right = $request->get('margin_right');
        if ($margin_right < 0) $margin_right = 0;         
 
        $dec_point = $request->get('dec_point');
        $dec_decimals = $request->get('dec_decimals');
        $dec_thousands = $request->get('dec_thousands');
        
        if ($dec_thousands == " ") $dec_thousands = "sp";

        $header = $S_Data['header_body'];
        $footer = $S_Data['footer_body'];

        $encoding = $request->get('encoding');
        if ($encoding == "") $encoding = "auto";

        if ($pdf_format == "Custom") {
            $pdf_cf_width = $request->get('pdf_format_width');
            $pdf_cf_height = $request->get('pdf_format_height');
            $pdf_format = $pdf_cf_width . ";" . $pdf_cf_height;
        }

        $sql4 = "INSERT INTO vtiger_pdfmaker_settings (templateid, margin_top, margin_bottom, margin_left, margin_right, format, orientation, 
                                               decimals, decimal_point, thousands_separator, header, footer, encoding, file_name, is_portal,
                                               is_listview, owner, sharingtype, disp_header, disp_footer)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $params4 = array($templateid, $margin_top, $margin_bottom, $margin_left, $margin_right, $pdf_format, $pdf_orientation,
            $dec_decimals, $dec_point, $dec_thousands, $header, $footer, $encoding, "", 0, 0, "", "", "3", "7");
        $adb->pquery($sql4, $params4);

//ignored picklist values
        $adb->pquery("DELETE FROM vtiger_pdfmaker_ignorepicklistvalues", array());
        
        $ignore_picklist_values =  $request->get('ignore_picklist_values');
        $pvvalues = explode(",", $ignore_picklist_values);
        foreach ($pvvalues as $value)
            $adb->pquery("INSERT INTO vtiger_pdfmaker_ignorepicklistvalues( value ) VALUES ( ? )", array(trim($value)));
// end ignored picklist values
//unset the former default template because only one template can be default per user x module
        
        $redirect = $request->get('redirect');
        if ($redirect == "false") {
            $redirect_url = "index.php?module=PDFMaker&view=Edit&parenttab=Tools&applied=true&templateid=".$templateid;
            
            $return_module = $request->get('return_module');
            $return_view = $request->get('return_view');
            
            if ($return_module != "") $redirect_url .= "&return_module=".$return_module;
            if ($return_view != "") $redirect_url .= "&return_view=".$return_view;
            
            header("Location:".$redirect_url);
        } else {
            header("Location:index.php?module=PDFMaker&view=Detail&parenttab=Tools&templateid=" . $templateid);
        }
    }   
}