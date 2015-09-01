<?php

/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

require_once('include/utils/utils.php');

class PDFMaker_imagesSelect_View extends Vtiger_BasicAjax_View {

    public function process(Vtiger_Request $request) {

        global $adb;

        $denied_img = vimage_path("denied.gif");
        $language = $_SESSION['authenticated_user_language'];
        $pdf_strings = return_module_language($language, "PDFMaker");

        $id = $request->get("return_id");
        $setype = getSalesEntityType($id);
        if ($setype != "Products") {
            $sql = "SELECT CASE WHEN vtiger_products.productid != '' THEN vtiger_products.productname ELSE vtiger_service.servicename END AS productname,
            vtiger_inventoryproductrel.productid, vtiger_inventoryproductrel.sequence_no, vtiger_attachments.attachmentsid, name, path
          FROM vtiger_inventoryproductrel
          LEFT JOIN vtiger_seattachmentsrel
            ON vtiger_seattachmentsrel.crmid=vtiger_inventoryproductrel.productid
          LEFT JOIN vtiger_attachments
            ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
          LEFT JOIN vtiger_products
            ON vtiger_products.productid=vtiger_inventoryproductrel.productid
          LEFT JOIN vtiger_service
            ON vtiger_service.serviceid=vtiger_inventoryproductrel.productid
          WHERE vtiger_inventoryproductrel.id=? ORDER BY vtiger_inventoryproductrel.sequence_no";
        } else {
            $sql = "SELECT vtiger_products.productname, vtiger_products.productid, '1' AS sequence_no,
            vtiger_attachments.attachmentsid, name, path
          FROM vtiger_products
          LEFT JOIN vtiger_seattachmentsrel
            ON vtiger_seattachmentsrel.crmid=vtiger_products.productid
          LEFT JOIN vtiger_attachments
            ON vtiger_attachments.attachmentsid=vtiger_seattachmentsrel.attachmentsid
          WHERE vtiger_products.productid=? ORDER BY vtiger_attachments.attachmentsid";
        }

        $res = $adb->pquery($sql, array($id));
        $products = array();
        while ($row = $adb->fetchByAssoc($res)) {
            $products[$row["productid"] . "#_#" . $row["productname"] . "#_#" . $row["sequence_no"]][$row["attachmentsid"]]["path"] = $row["path"];
            $products[$row["productid"] . "#_#" . $row["productname"] . "#_#" . $row["sequence_no"]][$row["attachmentsid"]]["name"] = $row["name"];
        }

        $saved_sql = "SELECT productid, sequence, attachmentid, width, height FROM vtiger_pdfmaker_images WHERE crmid=?";
        $saved_res = $adb->pquery($saved_sql, array($id));
        $saved_products = array();
        $saved_wh = array();
        $bac_products = array();

        while ($saved_row = $adb->fetchByAssoc($saved_res)) {
            $saved_products[$saved_row["productid"] . "_" . $saved_row["sequence"]] = $saved_row["attachmentid"];
            $saved_wh[$saved_row["productid"] . "_" . $saved_row["sequence"]]["width"] = ($saved_row["width"] > 0 ? $saved_row["width"] : "");
            $saved_wh[$saved_row["productid"] . "_" . $saved_row["sequence"]]["height"] = ($saved_row["height"] > 0 ? $saved_row["height"] : "");
        }

        $imgHTML = "";
        foreach ($products as $productnameid => $data) {
            list($productid, $productname, $seq) = explode("#_#", $productnameid, 3);
            $prodImg = "";
            $i = 0;
            $noCheck = ' checked="checked" ';
            $width = "100";
            $height = "";
            foreach ($data as $attid => $images) {
                if ($attid != "") {
                    if ($i == 3)
                        $prodImg.="</tr><tr>";
                    $checked = "";
                    if (isset($saved_products[$productid . "_" . $seq])) {
                        if ($saved_products[$productid . "_" . $seq] == $attid) {
                            $checked = ' checked="checked" ';
                            $noCheck = "";
                            $width = $saved_wh[$productid . "_" . $seq]["width"];
                            $height = $saved_wh[$productid . "_" . $seq]["height"];
                        }
                    } elseif (!isset($bac_products[$productid . "_" . $seq])) { //$bac_products array is used for default selection of first image  in case no explicit selection has been made
                        $bac_products[$productid . "_" . $seq] = $attid;
                        $checked = ' checked="checked" ';
                        $noCheck = "";
                        $width = "100";
                        $height = "";
                    }
                    $prodImg.='<td valign="middle"><input type="radio" name="img_' . $productid . '_' . $seq . '" value="' . $attid . '"' . $checked . '/>
		                 <img align="absmiddle" src="' . $images["path"] . $attid . '_' . $images["name"] . '" alt="' . $images["name"] . '" title="' . $images["name"] . '" style="max-width:50px;max-height:50px;">
		                 </td>';
                    $i++;
                }
            }

            $imgHTML.='<tr><td class="detailedViewHeader" style="padding-top:5px;padding-bottom:5px;"><b>' . $productname . '</b>';
            if ($i > 0) {
                $imgHTML.='&nbsp;&nbsp;&nbsp;<input type="text" maxlength="3" name="width_' . $productid . '_' . $seq . '" value="' . $width . '" class="small" style="width:40px">&nbsp;x&nbsp;
		              <input type="text" maxlength="3" name="height_' . $productid . '_' . $seq . '" value="' . $height . '" class="small" style="width:40px">';
            }
            $imgHTML.='</td></tr>
		             <tr><td class="dvtCellInfo">';
            $imgHTML.='<table cellpadding="0" cellspacing="1"><tr><td><input type="radio" name="img_' . $productid . '_' . $seq . '" value="no_image"' . $noCheck . '/>';
            $imgHTML.='<img src="'.$denied_img.'" width="30" align="absmiddle" title="' . $pdf_strings["LBL_NO_IMAGE"] . '" alt="' . $pdf_strings["LBL_NO_IMAGE"] . '"/></td>';
            $imgHTML.=$prodImg . "</tr></table>
		            </td></tr>";
        }

        echo '
		<div xmlns="http://www.w3.org/1999/xhtml" style="min-width: 350px;" class="modelContainer" id="PDFMakerProductsImageContainer">
		<div class="modal-header">
			<button title="' . getTranslatedString('LBL_CLOSE') . '" data-dismiss="modal" class="close">x</button>
			<h3>' . $pdf_strings["LBL_PRODUCT_IMAGE"] . '</h3>
		</div>
		
		<form name="PDFImagesForm" method="post" action="index.php" class="form-horizontal contentsBackground" onsubmit="PDFMakerCommon.saveproductimages(' . $id . ');return false;">
		<table border=0 cellspacing=0 cellpadding=5 width=100% align=center>
		    <tr><td class="small">
		        <div style="max-height:700px; overflow:auto;">
		        <table border=0 cellspacing=0 cellpadding=5 width=100% align=center bgcolor=white>
		            ' . $imgHTML . '
		        </table>
		        </div>
		    </td></tr>
		</table>
		
		<div class="modal-footer">
			<div class=" pull-right cancelLinkContainer"><a data-dismiss="modal" type="reset" class="cancelLink">' . getTranslatedString('LBL_CANCEL') . '</a></div>
			<button name="saveButton" type="submit" class="btn btn-success"><strong>' . getTranslatedString('LBL_SAVE') . '</strong></button>
		</div>
		</form>
		
		</div>
		';
    }   
}    