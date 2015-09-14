<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Debugger {

    private static $c_request_par = "setdebug";
    private static $c_session_par = "PDFMakerDebugging";
    private static $instance;
    private $db;

    private function __construct() {
        $this->db = PearDatabase::GetInstance();
    }

    public static function GetInstance() {
        if (self::$instance == null)
            self::$instance = new Debugger();

        return self::$instance;
    }

    public function Init() {
        $this->handleRequest();
        $this->runDebug();
    }

    private function handleRequest() {
        if (isset($_REQUEST[self::$c_request_par])) {
            if ($_REQUEST[self::$c_request_par] == "true")
                $_SESSION[self::$c_session_par] = "true";
            elseif ($_REQUEST[self::$c_request_par] == "false")
                unset($_SESSION[self::$c_session_par]);
        }
    }

    private function runDebug() {
        if (isset($_SESSION[self::$c_session_par]) && $_SESSION[self::$c_session_par] == "true") {
            $this->performActions();
        }
    }

    private function performActions() {
        $this->db->setDebug(true);
        error_reporting(63);
    }

    public function GetDebugVal() {
        $val = false;
        if (isset($_SESSION[self::$c_session_par]) && $_SESSION[self::$c_session_par] == "true")
            $val = true;

        return $val;
    }

    public function SetDebugVal($i_val) {
        if ($i_val === true)
            $_SESSION[self::$c_session_par] = "true";
        else
            unset($_SESSION[self::$c_session_par]);
    }

}
