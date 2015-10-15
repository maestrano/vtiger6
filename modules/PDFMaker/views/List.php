<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_List_View extends Vtiger_Index_View {

    protected $listViewLinks = false;
    
    public function __construct() {
        parent::__construct();
        $this->exposeMethod('getList');
    }

    public function preProcess(Vtiger_Request $request, $display = true) {
        $viewer = $this->getViewer($request);
        $moduleName = $request->getModule();
        $viewer->assign('QUALIFIED_MODULE', $moduleName);
        Vtiger_Basic_View::preProcess($request, false);
        $viewer = $this->getViewer($request);

        $moduleName = $request->getModule();
        if (!empty($moduleName)) {
            $moduleModel = new PDFMaker_PDFMaker_Model('PDFMaker');
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $userPrivilegesModel = Users_Privileges_Model::getInstanceById($currentUser->getId());
            $permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
            $viewer->assign('MODULE', $moduleName);

            if (!$permission) {
                $viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
                $viewer->view('OperationNotPermitted.tpl', $moduleName);
                exit;
            }

            $linkParams = array('MODULE' => $moduleName, 'ACTION' => $request->get('view'));
            $linkModels = $moduleModel->getSideBarLinks($linkParams);

            $viewer->assign('QUICK_LINKS', $linkModels);
        }
        
        $viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
        $viewer->assign('CURRENT_VIEW', $request->get('view'));
        if ($display) {
            $this->preProcessDisplay($request);
        }
    }
    
    public function postProcess(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $viewer->view('IndexPostProcess.tpl');

        parent::postProcess($request);
    }

    public function process(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
        $adb = PearDatabase::getInstance();

        if (is_dir("modules/PDFMaker/resources/mpdf")) {
            $this->invokeExposedMethod('getList', $request);
        } else {
            
            $mb_string_exists = function_exists("mb_get_info");
            if ($mb_string_exists === false) {
                $viewer->assign("MB_STRING_EXISTS", 'false');
            } else {
                $viewer->assign("MB_STRING_EXISTS", 'true');
            }
         
            $step = 1;
            $current_step = 1;
            $total_steps = 2;
            
            $viewer->assign("STEP", $step);
            $viewer->assign("CURRENT_STEP", $current_step);
            $viewer->assign("TOTAL_STEPS", $total_steps);
            
            $viewer->view('Install.tpl', 'PDFMaker');
        }
    }

    public function getList(Vtiger_Request $request) {

        PDFMaker_Debugger_Model::GetInstance()->Init();

        $PDFMaker = new PDFMaker_PDFMaker_Model();
        
        if ($PDFMaker->CheckPermissions("DETAIL") == false)
            $PDFMaker->DieDuePermission();

        $viewer = $this->getViewer($request);
        $orderby = "templateid";
        $dir = "asc";

        if (isset($_REQUEST["dir"]) && $_REQUEST["dir"] == "desc")
            $dir = "desc";

        if (isset($_REQUEST["orderby"])) {
            switch ($_REQUEST["orderby"]) {
                case "name":
                    $orderby = "filename";
                    break;

                case "module":
                    $orderby = "module";
                    break;

                case "description":
                    $orderby = "description";
                    break;

                case "order":
                    $orderby = "order";
                    break;
                default:
                    $orderby = $_REQUEST["orderby"];
                    break;
            }
        }

        $version_type = $PDFMaker->GetVersionType();
        $license_key = $PDFMaker->GetLicenseKey();

        $viewer->assign("VERSION_TYPE", $version_type);
        $viewer->assign("VERSION", ucfirst($version_type) . " " . PDFMaker_Version_Helper::$version);
    
        if ($PDFMaker->CheckPermissions("EDIT")) {
            $viewer->assign("EDIT", "permitted");
        }

        $notif = $PDFMaker->GetReleasesNotif();
        $viewer->assign("RELEASE_NOTIF", $notif);

        $php_version = phpversion();
        $notif = false;
        $max_in_vars = ini_get("max_input_vars");
        if ($max_in_vars <= 1000 && $php_version >= "5.3.9")
            $notif = true;

        $test = ini_set("memory_limit", "256M");
        $memory_limit = ini_get("memory_limit");
        if (substr($memory_limit, 0, -1) <= 128)
            $notif = true;

        $max_exec_time = ini_get("max_execution_time");
        if ($max_exec_time <= 60)
            $notif = true;

        if (extension_loaded('suhosin')) {
            $request_max_vars = ini_get("suhosin.request.max_vars");
            $post_max_vars = ini_get("suhosin.post.max_vars");
            if ($request_max_vars <= 1000)
                $notif = true;
            if ($post_max_vars <= 1000)
                $notif = true;
        }

        $viewer->assign("MOD", $mod_strings);
        $viewer->assign("APP", $app_strings);
        $viewer->assign("THEME", $theme);
        $viewer->assign("PARENTTAB", getParentTab());
        $viewer->assign("IMAGE_PATH", $image_path);

        $return_data = $PDFMaker->GetListviewData($orderby, $dir);
        $viewer->assign("PDFTEMPLATES", $return_data);
        $category = getParentTab();
        $viewer->assign("CATEGORY", $category);

        if (is_admin($current_user)) {
            $viewer->assign('IS_ADMIN', '1');
        }
        
        $linkModels = $PDFMaker->getListViewLinks($linkParams);
        $viewer->assign('LISTVIEW_LINKS', $linkModels);
        
        $tpl = "ListPDFTemplates";
        if ($request->get('ajax') == "true")
            $tpl .= "Contents";

        $viewer->view($tpl.".tpl", 'PDFMaker');
    }
    
    function getHeaderScripts(Vtiger_Request $request) {
        $headerScriptInstances = parent::getHeaderScripts($request);
        $moduleName = $request->getModule();

        $jsFileNames = array(
            "layouts.vlayout.modules.PDFMaker.resources.FreeLicense",
            "layouts.vlayout.modules.PDFMaker.resources.List"
        );
        $jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
        $headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
        return $headerScriptInstances;
    }   
}