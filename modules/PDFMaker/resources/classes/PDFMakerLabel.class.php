<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

//Helper class that represents one label with all values for all languages
class PDFMakerLabel {

    private $ID;
    private $key;
    private $langValsArr;

    public function __construct($_id, $_key) {
        $this->ID = $_id;
        $this->key = $_key;
        $this->langValsArr = array();
    }

    public function SetLangValue($_langId, $_val) {
        $this->langValsArr[$_langId] = $_val;
    }

    public function GetLangValue($_langId) {
        return $this->langValsArr[$_langId];
    }

    public function GetFirstNonEmptyValue() {
        $result = $this->key;
        ksort($this->langValsArr);
        foreach ($this->langValsArr as $val) {
            if ($val != "") {
                $result = $val;
                break;
            }
        }

        return $result;
    }

    public function IsLangValSet($_langId) {
        return isset($this->langValsArr[$_langId]);
    }

    public function GetID() {
        return $this->ID;
    }

    public function GetKey() {
        return $this->key;
    }

    public function GetLangValsArr() {
        ksort($this->langValsArr);
        return $this->langValsArr;
    }

}
