<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
//require_once('modules/PDFMaker/PDFMaker.php');

class PDFMaker_GetPDFActions_View extends Vtiger_BasicAjax_View {

    public function process(Vtiger_Request $request) {

        //Debugger::GetInstance()->Init();
        $AvailableRelModules = array("Accounts","Contacts","Leads","Vendors");
        
        $current_user = $cu_model = Users_Record_Model::getCurrentUserModel();
        $currentLanguage = Vtiger_Language_Handler::getLanguage();
        
        $adb = PearDatabase::getInstance();

        $viewer = $this->getViewer($request);

        $PDFMaker = new PDFMaker_PDFMaker_Model();
        if ($PDFMaker->CheckPermissions("DETAIL") == false) {
            $output = '<table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
              <tr>
                <td class="dvtCellInfo" style="width:100%;border-top:1px solid #DEDEDE;text-align:center;">
                  <strong>' . vtranslate("LBL_PERMISSION") . '</strong>
                </td>
              </tr>              		
              </table>';
            die($output);
        }

        $record = $request->get('record');
        $module = getSalesEntityType($record);

        $viewer->assign('MODULE', $module);
        $viewer->assign('ID', $record);

        $relfocus = CRMEntity::getInstance($module);
        $relfocus->id = $record;
        $relfocus->retrieve_entity_info($relfocus->id, $module);
        
        $relmodule = "";
        $relmodule_selid = "";
        if (in_array($module, $AvailableRelModules)) { 
            $relmodule = $module;
            $relmodule_selid = $relfocus->id;    
        } else {    
            if (isset($relfocus->column_fields['account_id']) && $relfocus->column_fields['account_id'] != "" && $relfocus->column_fields['account_id'] != "0") {
                $relmodule = 'Accounts';
                $relmodule_selid = $relfocus->column_fields['account_id'];
            } 
            
            if ($relmodule == "" && isset($relfocus->column_fields['related_to']) && $relfocus->column_fields['related_to'] != "" && $relfocus->column_fields['related_to'] != "0") {
                
                $relmodule_selid = $relfocus->column_fields['related_to'];
                $relmodule = getSalesEntityType($relmodule_selid);
                if (!in_array($relmodule, $AvailableRelModules)) {
                    $relmodule = $relmodule_selid = "";
                }   
            } 
            
            if ($relmodule == "" && isset($relfocus->column_fields['parent_id']) && $relfocus->column_fields['parent_id'] != "" && $relfocus->column_fields['parent_id'] != "0") {
                
                $relmodule_selid = $relfocus->column_fields['parent_id'];
                $relmodule = getSalesEntityType($relmodule_selid);
                if (!in_array($relmodule, $AvailableRelModules)) {
                    $relmodule = $relmodule_selid = "";
                } 
            }  
            
            if ($relmodule == "" && isset($relfocus->column_fields['contact_id']) && $relfocus->column_fields['contact_id'] != "" && $relfocus->column_fields['contact_id'] != "0") {
                $relmodule = 'Contacts';
                $relmodule_selid = $relfocus->column_fields['contact_id'];
            } 
        }

        $viewer->assign('RELMODULE', $relmodule);
        $viewer->assign('RELMODULE_SELID', $relmodule_selid);

        require('user_privileges/user_privileges_' . $current_user->id . '.php');

        if (is_dir("modules/PDFMaker/resources/mpdf") && $PDFMaker->CheckPermissions("DETAIL")) {
            $viewer->assign("ENABLE_PDFMAKER", 'true');
        } else {
            $viewer->assign("ENABLE_PDFMAKER", "false");
        }

        if (is_dir("modules/EMAILMaker") && vtlib_isModuleActive('EMAILMaker')) {
            $module_tabid = getTabId($module);
            $res = $adb->pquery("SELECT * FROM vtiger_links WHERE tabid = ? AND linktype = ? AND linkurl LIKE ?", array($module_tabid, 'DETAILVIEWSIDEBARWIDGET', 'module=EMAILMaker&view=GetEMAILActions&record=%'));
            if ($adb->num_rows($res) > 0)
                $viewer->assign("ENABLE_EMAILMAKER", 'true');
        }
        $viewer->assign('PDFMAKER_MOD', return_module_language($currentLanguage, "PDFMaker"));

        $pdftemplateid = $PDFMaker->getPDFTemplateId($module);
        $viewer->assign('PDFTEMPLATEID', $pdftemplateid);
        
        if (!isset($_SESSION["template_languages"]) || $_SESSION["template_languages"] == "") {
            $temp_res = $adb->pquery("SELECT label, prefix FROM vtiger_language WHERE active = ?", array('1'));
            while ($temp_row = $adb->fetchByAssoc($temp_res)) {
                $template_languages[$temp_row["prefix"]] = $temp_row["label"];
            }
            $_SESSION["template_languages"] = $template_languages;
        }
        $viewer->assign('TEMPLATE_LANGUAGES', $_SESSION["template_languages"]);
        $viewer->assign('CURRENT_LANGUAGE', $currentLanguage);
        $viewer->assign('IS_ADMIN', is_admin($current_user));

        $viewer->view("GetPDFActions.tpl", 'PDFMaker');
    }
}    