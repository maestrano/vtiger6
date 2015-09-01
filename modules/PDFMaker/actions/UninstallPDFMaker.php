<?php

/*********************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ******************************************************************************* */

class PDFMaker_UninstallPDFMaker_Action extends Settings_Vtiger_Basic_Action {

    function __construct() {
        parent::__construct();
    }

    function process(Vtiger_Request $request) {

        $Vtiger_Utils_Log = true;
        include_once('vtlib/Vtiger/Module.php');
        $adb = PearDatabase::getInstance();
        $module = Vtiger_Module::getInstance('PDFMaker');
        if ($module) {
            $module->delete();
            @shell_exec('rm -r modules/PDFMaker');
            @shell_exec('rm -r layouts/vlayout/modules/PDFMaker');
            @shell_exec('rm -f languages/cz_cz/PDFMaker.php');
            @shell_exec('rm -f languages/de_de/PDFMaker.php');
            @shell_exec('rm -f languages/en_gb/PDFMaker.php');
            @shell_exec('rm -f languages/en_us/PDFMaker.php');
            @shell_exec('rm -f languages/es_es/PDFMaker.php');
            @shell_exec('rm -f languages/es_mx/PDFMaker.php');
            @shell_exec('rm -f languages/fr_fr/PDFMaker.php');
            @shell_exec('rm -f languages/hi_hi/PDFMaker.php');
            @shell_exec('rm -f languages/hu_hu/PDFMaker.php');
            @shell_exec('rm -f languages/it_it/PDFMaker.php');
            @shell_exec('rm -f languages/nl_nl/PDFMaker.php');
            @shell_exec('rm -f languages/pl_pl/PDFMaker.php');
            @shell_exec('rm -f languages/pt_br/PDFMaker.php');
            @shell_exec('rm -f languages/ro_ro/PDFMaker.php');
            @shell_exec('rm -f languages/ru_ru/PDFMaker.php');
            @shell_exec('rm -f languages/sk_sk/PDFMaker.php');
            @shell_exec('rm -f languages/tr_tr/PDFMaker.php');

            $adb->pquery("DROP TABLE IF EXISTS vtiger_pdfmaker", array());
            $adb->pquery("DROP TABLE IF EXISTS vtiger_pdfmaker_seq", array());
            $adb->pquery("DROP TABLE IF EXISTS vtiger_pdfmaker_settings", array());
            $adb->pquery("DROP TABLE IF EXISTS vtiger_pdfmaker_breakline", array());
            $adb->pquery("DROP TABLE IF EXISTS vtiger_pdfmaker_images", array());
            $adb->pquery("DROP TABLE IF EXISTS vtiger_pdfmaker_releases", array());
            $adb->pquery("DROP TABLE IF EXISTS vtiger_pdfmaker_productbloc_tpl", array());
            $adb->pquery("DROP TABLE IF EXISTS vtiger_pdfmaker_ignorepicklistvalues", array());

            $result = array('success' => true);
        } else {
            $result = array('success' => false);
        }

        $result = array('success' => true);
        
        ob_clean();
        $response = new Vtiger_Response();
        $response->setResult($result);
        $response->emit();
    }
}
