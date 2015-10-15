<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

error_reporting(0);

require_once("libraries/nusoap/nusoap.php");

class PDFMaker_PDFMaker_Model extends Vtiger_Module_Model {

    private $version_type = "Free";
    private $license_key;
    private $version_no;
    private $basicModules;
    private $pageFormats;
    private $profilesActions;
    private $profilesPermissions;
    var $log;
    var $db;

    // constructor of PDFMaker class
    function __construct() {
        $this->log = LoggerManager::getLogger('account');
        $this->db = PearDatabase::getInstance();

        $this->version_no = PDFMaker_Version_Helper::$version;
       
        // array of modules that are allowed for basic version type
        $this->basicModules = array("20", "21", "22", "23");
        // array of action names used in profiles permissions
        $this->profilesActions = array("EDIT" => "EditView", // Create/Edit
            "DETAIL" => "DetailView", // View
            "DELETE" => "Delete", // Delete
            "EXPORT_RTF" => "Export", // Export to RTF
        );
        $this->profilesPermissions = array();

        $this->name = "PDFMaker";
        $this->id = getTabId("PDFMaker");
        
        $_SESSION['KCFINDER']['uploadURL'] = "test/upload"; 
        $_SESSION['KCFINDER']['uploadDir'] = "../test/upload";
    }

    //Getters and Setters
    public function GetVersionType() {
        return $this->version_type;
    }

    public function GetLicenseKey() {
        return $this->license_key;
    }

    public function GetPageFormats() {
        return $this->pageFormats;
    }

    public function GetBasicModules() {
        return $this->basicModules;
    }

    public function GetProfilesActions() {
        return $this->profilesActions;
    }

    //PUBLIC METHODS SECTION
    //ListView data
    public function GetListviewData() {
        global $current_user;

        $sql = "SELECT templateid, module FROM vtiger_pdfmaker GROUP BY module";
        $result = $this->db->pquery($sql, array());

        $return_data = Array();
        $num_rows = $this->db->num_rows($result);

        if (!$num_rows)
        {
            include_once("modules/PDFMaker/PDFMaker.php");
            $C_PDFMaker = new PDFMaker();
            $C_PDFMaker->executeSql();
            
            $result = $this->db->pquery($sql, array());
            $num_rows = $this->db->num_rows($result);
        }    
        
        
        for ($i = 0; $i < $num_rows; $i++) {
            $currModule = $this->db->query_result($result, $i, 'module');
            $templateid = $this->db->query_result($result, $i, 'templateid');
            $pdftemplatearray = array();
            $pdftemplatearray['templateid'] = $templateid;
            $pdftemplatearray['module'] = $pdftemplatearray['filename'] = "<a href=\"index.php?module=PDFMaker&view=Detail&templateid=" . $templateid . "\">" . vtranslate($currModule, $currModule) .  "</a>";
            if ($this->CheckPermissions("EDIT")) {
                $pdftemplatearray['edit'] = "<a href=\"index.php?module=PDFMaker&view=Edit&return_view=List&templateid=" . $templateid . "\">" . vtranslate("LBL_EDIT") . "</a>";
            }
            $return_data [] = $pdftemplatearray;
        }

        return $return_data;
    }

    //DetailView data
    public function GetDetailViewData($templateid) {
        $sql = "SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
			FROM vtiger_pdfmaker
				LEFT JOIN vtiger_pdfmaker_settings ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid
			WHERE vtiger_pdfmaker.templateid=?";

        $result = $this->db->pquery($sql, array($templateid));
        $pdftemplateResult = $this->db->fetch_array($result);

        $this->CheckTemplatePermissions($pdftemplateResult["module"], $templateid);

        $pdftemplateResult["templateid"] = $templateid;   // fix of empty templateid in case of NULL templateid in DB

        return $pdftemplateResult;
    }

    //EditView data
    public function GetEditViewData($templateid) {
        $sql = "SELECT vtiger_pdfmaker.*, vtiger_pdfmaker_settings.*
    			FROM vtiger_pdfmaker
    			LEFT JOIN vtiger_pdfmaker_settings ON vtiger_pdfmaker_settings.templateid = vtiger_pdfmaker.templateid
    			WHERE vtiger_pdfmaker.templateid=?";

        $result = $this->db->pquery($sql, array($templateid));
        return $this->db->fetch_array($result);
    }

    //function for getting the list of available user's templates
    public function GetAvailableTemplates($currModule, $forListView = false) {
        global $current_user;
        
        $sql = "SELECT templateid, module FROM vtiger_pdfmaker
                INNER JOIN vtiger_pdfmaker_settings USING ( templateid )                
                WHERE module=?";

        $params = array($currModule);
 
        $result = $this->db->pquery($sql, $params);
        $return_array = array();
        while ($row = $this->db->fetchByAssoc($result)) {
            $templateid = $row["templateid"];
            if ($this->CheckTemplatePermissions($currModule, $templateid, false) == false)
                continue;

            $return_array[$row["templateid"]]["templatename"] = vtranslate($row["module"], $row["module"]);
        }
        return $return_array;
    }

    //function for getting the mPDF object that contains prepared HTML output
    //returns the name of output filename - the file can be generated by calling mPDF->Output(..) method
    public function GetPreparedMPDF(&$mpdf, $record, $module, $language) {
        require_once("modules/PDFMaker/resources/mpdf/mpdf.php");

        $focus = CRMEntity::getInstance($module);
        $TemplateContent = array();

        foreach ($focus->column_fields as $cf_key => $cf_value) {
            $focus->column_fields[$cf_key] = '';
        }
      
        $focus->retrieve_entity_info($record, $module);
        $focus->id = $record;

        $PDFContent = $this->GetPDFContentRef($module, $focus, $language);

        $Settings = $PDFContent->getSettings();
        $name = $PDFContent->getFilename();
        $pdf_content = $PDFContent->getContent();
        $header_html = $pdf_content["header"];
        $body_html = $pdf_content["body"];
        $footer_html = $pdf_content["footer"];

        if ($Settings["orientation"] == "landscape")
            $orientation = "L";
        else
            $orientation = "P";

        $format = $Settings["format"];  // variable $format used in mPDF constructor
        $formatPB = $format;            // variable $formatPB used in <pagebreak ... /> contruction
        if (strpos($format, ";") > 0) {
            $tmpArr = explode(";", $format);
            $format = array($tmpArr[0], $tmpArr[1]);
            $formatPB = $format[0] . "mm " . $format[1] . "mm";
        } elseif ($Settings["orientation"] == "landscape") {
            $format .= "-L";
            $formatPB .= "-L";
        }

        if (!is_object($mpdf)) {
            $mpdf = new mPDF('', $format, '', '', $Settings["margin_left"], $Settings["margin_right"], 0, 0, $Settings["margin_top"], $Settings["margin_bottom"], $orientation);
            $mpdf->SetAutoFont();
            $this->mpdf_preprocess($mpdf, $templateid, $PDFContent->bridge2mpdf);
            @$mpdf->SetHTMLHeader($header_html);
        } else {
            $this->mpdf_preprocess($mpdf, $templateid, $PDFContent->bridge2mpdf);
            @$mpdf->SetHTMLHeader($header_html);
            @$mpdf->WriteHTML('<pagebreak sheet-size="' . $formatPB . '" orientation="' . $orientation . '" margin-left="' . $Settings["margin_left"] . 'mm" margin-right="' . $Settings["margin_right"] . 'mm" margin-top="0mm" margin-bottom="0mm" margin-header="' . $Settings["margin_top"] . 'mm" margin-footer="' . $Settings["margin_bottom"] . 'mm" />');
        }
        @$mpdf->SetHTMLFooter($footer_html);
        @$mpdf->WriteHTML($body_html);
        $this->mpdf_postprocess($mpdf, $PDFContent->bridge2mpdf);
 
        //check in case of some error when $mpdf object is not set it is caused by lack of permissions - i.e. when workflow template is 'none'
        if (!is_object($mpdf)) {
            @$mpdf = new mPDF();
            @$mpdf->WriteHTML(vtranslate("LBL_PERMISSION", "PDFMaker"));
        }
        
        $name = str_replace(array(' ', '/', ','), array('-', '-', '-'), $name);
        return $name;
    }

    public function GetPDFContentRef($module, $focus, $language) {
        //require_once("modules/PDFMaker/InventoryPDF.php");
        return new PDFMaker_PDFContent_Model($module, $focus, $language);
    }

    public function DieDuePermission() {
        global $current_user, $default_theme;
        if (isset($_SESSION['vtiger_authenticated_user_theme']) && $_SESSION['vtiger_authenticated_user_theme'] != '')
            $theme = $_SESSION['vtiger_authenticated_user_theme'];
        else {
            if (!empty($current_user->theme)) {
                $theme = $current_user->theme;
            } else {
                $theme = $default_theme;
            }
        }

        $output = "<link rel='stylesheet' type='text/css' href='themes/$theme/style.css'>";
        $output .= "<table border='0' cellpadding='5' cellspacing='0' width='100%' height='450px'><tr><td align='center'>";
        $output .= "<div style='border: 3px solid rgb(153, 153, 153); background-color: rgb(255, 255, 255); width: 55%; position: relative; z-index: 10000000;'>
      		<table border='0' cellpadding='5' cellspacing='0' width='98%'>
      		<tbody><tr>
      		<td rowspan='2' width='11%'><img src='" . vtiger_imageurl('denied.gif', $theme) . "' ></td>
      		<td style='border-bottom: 1px solid rgb(204, 204, 204);' nowrap='nowrap' width='70%'><span class='genHeaderSmall'>" . vtranslate("LBL_PERMISSION", "PDFMaker") . "</span></td>
      		</tr>
      		<tr>
      		<td class='small' align='right' nowrap='nowrap'>
      		<a href='javascript:window.history.back();'>" . vtranslate("LBL_GO_BACK") . "</a><br></td>
      		</tr>
      		</tbody></table>
      		</div>";
        $output .= "</td></tr></table>";
        die($output);
    }

    public function CheckTemplatePermissions($selected_module, $templateid, $die = true) {
        $result = true;
        if ($selected_module != "" && isPermitted($selected_module, '') != "yes") {
            $result = false;
        } 

        if ($die === true && $result === false) {
            $this->DieDuePermission();
        }

        return $result;
    }

    //Method for checking the permissions, whether the user has privilegies to perform specific action on PDF Maker.
    public function CheckPermissions($actionKey) {
        $current_user = Users_Record_Model::getCurrentUserModel();
        $profileid = getUserProfile($current_user->id);
        $result = false;

        if (isset($this->profilesActions[$actionKey])) {

            //$actionid = getActionid($this->profilesActions[$actionKey]);
            if (isPermitted('PDFMaker', $this->profilesActions[$actionKey]) == "yes") {
                $result = true;
            } 
        }

        return $result;
    }

    private function getSubRoleUserIds($roleid) {
        $subRoleUserIds = array();
        $subordinateUsers = getRoleAndSubordinateUsers($roleid);
        if (!empty($subordinateUsers) && count($subordinateUsers) > 0) {
            $currRoleUserIds = getRoleUserIds($roleid);
            $subRoleUserIds = array_diff($subordinateUsers, $currRoleUserIds);
        }

        return $subRoleUserIds;
    }

    private function mpdf_preprocess(&$mpdf, $templateid, $bridge = '') {
        if ($bridge != '' && is_array($bridge)) {
            $mpdf->PDFMakerRecord = $bridge["record"];
            $mpdf->PDFMakerTemplateid = $bridge["templateid"];

            if (isset($bridge["subtotalsArray"]))
                $mpdf->PDFMakerSubtotalsArray = $bridge["subtotalsArray"];
        }

        $this->mpdf_processing($mpdf, 'pre');
    }

    private function mpdf_postprocess(&$mpdf, $bridge = '') {
        $this->mpdf_processing($mpdf, 'post');
    }

    private function mpdf_processing(&$mpdf, $when) {
        $path = 'modules/PDFMaker/resources/mpdf_processing/';
        switch ($when) {
            case "pre":
                $filename = 'preprocessing.php';
                $functionname = 'pdfmaker_mpdf_preprocessing';
                break;
            case "post":
                $filename = 'postprocessing.php';
                $functionname = 'pdfmaker_mpdf_postprocessing';
                break;
        }
        if (is_file($path . $filename) && is_readable($path . $filename)) {
            require_once($path . $filename);
            $functionname($mpdf, $templateid);
        }
    }

    public function GetReleasesNotif() {
        $notif = "";
        return $notif;
    }

    public function GetProductBlockFields() {
        global $current_user;
        $result = array();

        //Product block
        $Article_Strings = array("" => vtranslate("LBL_PLS_SELECT", "PDFMaker"),
            "PRODUCTBLOC_START" => vtranslate("LBL_ARTICLE_START", "PDFMaker"),
            "PRODUCTBLOC_END" => vtranslate("LBL_ARTICLE_END", "PDFMaker")
        );
        $result["ARTICLE_STRINGS"] = $Article_Strings;

        //Common fields for product and services
        $Product_Fields = array("PS_CRMID" => vtranslate("LBL_RECORD_ID", "PDFMaker"),
            "PS_NO" => vtranslate("LBL_PS_NO", "PDFMaker"),
            "PRODUCTPOSITION" => vtranslate("LBL_PRODUCT_POSITION", "PDFMaker"),
            "CURRENCYNAME" => vtranslate("LBL_CURRENCY_NAME", "PDFMaker"),
            "CURRENCYCODE" => vtranslate("LBL_CURRENCY_CODE", "PDFMaker"),
            "CURRENCYSYMBOL" => vtranslate("LBL_CURRENCY_SYMBOL", "PDFMaker"),
            "PRODUCTNAME" => vtranslate("LBL_VARIABLE_PRODUCTNAME", "PDFMaker"),
            "PRODUCTTITLE" => vtranslate("LBL_VARIABLE_PRODUCTTITLE", "PDFMaker"),
            //"PRODUCTDESCRIPTION" => vtranslate("LBL_VARIABLE_PRODUCTDESCRIPTION", "PDFMaker"),
            "PRODUCTEDITDESCRIPTION" => vtranslate("LBL_VARIABLE_PRODUCTEDITDESCRIPTION", "PDFMaker")
        );

        if ($this->db->num_rows($this->db->pquery("SELECT tabid FROM vtiger_tab WHERE name=?",array('Pdfsettings'))) > 0)
            $Product_Fields["CRMNOWPRODUCTDESCRIPTION"] = vtranslate("LBL_CRMNOW_DESCRIPTION", "PDFMaker");

        $Product_Fields["PRODUCTQUANTITY"] = vtranslate("LBL_VARIABLE_QUANTITY", "PDFMaker");
        $Product_Fields["PRODUCTUSAGEUNIT"] = vtranslate("LBL_VARIABLE_USAGEUNIT", "PDFMaker");
        $Product_Fields["PRODUCTLISTPRICE"] = vtranslate("LBL_VARIABLE_LISTPRICE", "PDFMaker");
        $Product_Fields["PRODUCTTOTAL"] = vtranslate("LBL_PRODUCT_TOTAL", "PDFMaker");
        $Product_Fields["PRODUCTDISCOUNT"] = vtranslate("LBL_VARIABLE_DISCOUNT", "PDFMaker");
        $Product_Fields["PRODUCTDISCOUNTPERCENT"] = vtranslate("LBL_VARIABLE_DISCOUNT_PERCENT", "PDFMaker");
        $Product_Fields["PRODUCTSTOTALAFTERDISCOUNT"] = vtranslate("LBL_VARIABLE_PRODUCTTOTALAFTERDISCOUNT", "PDFMaker");
        $Product_Fields["PRODUCTVATPERCENT"] = vtranslate("LBL_PRODUCT_VAT_PERCENT", "PDFMaker");
        $Product_Fields["PRODUCTVATSUM"] = vtranslate("LBL_PRODUCT_VAT_SUM", "PDFMaker");
        $Product_Fields["PRODUCTTOTALSUM"] = vtranslate("LBL_PRODUCT_TOTAL_VAT", "PDFMaker");
        $result["SELECT_PRODUCT_FIELD"] = $Product_Fields;

        //Available fields for products
        $prod_fields = array();
        $serv_fields = array();

        $in = '0';
        if (vtlib_isModuleActive('Products'))
            $in = getTabId('Products');
        if (vtlib_isModuleActive('Services')) {
            if ($in == '0')
                $in = getTabId('Services');
            else
                $in .= ', ' . getTabId('Services');
        }
        $sql = "SELECT  t.tabid, t.name,
                        b.blockid, b.blocklabel,
                        f.fieldname, f.fieldlabel
                FROM vtiger_tab AS t
                INNER JOIN vtiger_blocks AS b USING(tabid)
                INNER JOIN vtiger_field AS f ON b.blockid = f.block
                WHERE t.tabid IN (" . $in . ")
                    AND (f.displaytype != 3 OR f.uitype = 55)
                ORDER BY t.name ASC, b.sequence ASC, f.sequence ASC, f.fieldid ASC";
        $res = $this->db->pquery($sql,array());
        while ($row = $this->db->fetchByAssoc($res)) {
            $module = $row["name"];
            $fieldname = $row["fieldname"];
            if (getFieldVisibilityPermission($module, $current_user->id, $fieldname) != '0')
                continue;

            $trans_field_nam = strtoupper($module) . "_" . strtoupper($fieldname);
            switch ($module) {
                case "Products":
                    $trans_block_lbl = vtranslate($row["blocklabel"], 'Products');
                    $trans_field_lbl = vtranslate($row["fieldlabel"], 'Products');
                    $prod_fields[$trans_block_lbl][$trans_field_nam] = $trans_field_lbl;
                    break;

                case "Services":
                    $trans_block_lbl = vtranslate($row["blocklabel"], 'Services');
                    $trans_field_lbl = vtranslate($row["fieldlabel"], 'Services');
                    $serv_fields[$trans_block_lbl][$trans_field_nam] = $trans_field_lbl;
                    break;

                default:
                    continue;
            }
        }
        $result["PRODUCTS_FIELDS"] = $prod_fields;
        $result["SERVICES_FIELDS"] = $serv_fields;

        return $result;
    }
    
    /**
     * Function to get the Quick Links for the module
     * @param <Array> $linkParams
     * @return <Array> List of Vtiger_Link_Model instances
     */
    public function getSideBarLinks($linkParams) {
       
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        
        $type = "SIDEBARLINK"; 
        $quickLinks = array();
      
        $linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        $quickLinks[] = array(
                'linktype' => 'SIDEBARLINK',
                'linklabel' => 'LBL_RECORDS_LIST',
                'linkurl' => $this->getListViewUrl(),
                'linkicon' => '',
        );   
          
        foreach ($quickLinks as $quickLink) {
            $links[$type][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
        }
                 
        return $links;
    }
    
    function generate_cool_uri($name) {
        $Search = array("$", "€", "&", "%", ")", "(", ".", " - ", "/", " ", ",", "ľ", "š", "č", "ť", "ž", "ý", "á", "í", "é", "ó", "ö", "ů", "ú", "ü", "ä", "ň", "ď", "ô", "ŕ", "Ľ", "Š", "Č", "Ť", "Ž", "Ý", "Á", "Í", "É", "Ó", "Ú", "Ď", "\"", "°", "ß");
        $Replace = array("", "", "", "", "", "", "-", "-", "-", "-", "-", "l", "s", "c", "t", "z", "y", "a", "i", "e", "o", "o", "u", "u", "u", "a", "n", "d", "o", "r", "l", "s", "c", "t", "z", "y", "a", "i", "e", "o", "u", "d", "", "", "ss");
        $return = str_replace($Search, $Replace, $name);
        // echo $return;
        return $return;
    }
    
    public function GetAllModules() {

        $Modulenames = Array();

        $sql = "SELECT tabid, name FROM vtiger_tab WHERE isentitytype=1 AND presence=0 AND tabid NOT IN (?,?) ORDER BY name ASC";
        $result = $this->db->pquery($sql,array('10', '28'));
        while ($row = $this->db->fetchByAssoc($result)) {
            if (file_exists("modules/" . $row['name'])) {

                if (isPermitted($row['name'], '') != "yes")
                    continue;

                $Modulenames[$row['name']] = vtranslate($row['name']);
                $ModuleIDS[$row['name']] = $row['tabid'];
            }
        }

        return array($Modulenames, $ModuleIDS);
    }
    
    public function getPDFTemplateId($formodule) {
        $sql = "SELECT templateid FROM vtiger_pdfmaker WHERE module=?";
        $result = $this->db->pquery($sql, array($formodule));
        return $this->db->query_result($result, 0, "templateid");
    }
    
    public function GetAvailableSettings() {
        $menu_array = array();

        $menu_array["PDFMakerUninstall"]["location"] = "index.php?module=PDFMaker&view=Uninstall";
        $menu_array["PDFMakerUninstall"]["desc"] = "LBL_UNINSTALL_DESC";
        $menu_array["PDFMakerUninstall"]["label"] = "LBL_UNINSTALL";
        
        return $menu_array;
    }
    
    public function getListViewLinks($linkParams) {
        
        $currentUserModel = Users_Record_Model::getCurrentUserModel();
        
        $linkTypes = array('LISTVIEWMASSACTION','LISTVIEWSETTING');
        $links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

        if($currentUserModel->isAdminUser()) {

            $SettingsLinks = $this->GetAvailableSettings();

            foreach($SettingsLinks as $stype => $sdata) {

                $s_parr = array(
                'linktype' => 'LISTVIEWSETTING',
                'linklabel' => $sdata["label"],
                'linkurl' => $sdata["location"],
                'linkicon' => ''); 

                $links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($s_parr);
            }
        }
        
        return $links;
    }
}  