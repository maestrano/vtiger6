{*<!--
/*********************************************************************************
* The content of this file is subject to the PDF Maker Free license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
<div class='editViewContainer'>
    <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
        <input type="hidden" name="module" value="PDFMaker">
        <input type="hidden" name="parenttab" value="{$PARENTTAB}">
        <input type="hidden" name="templateid" value="{$SAVETEMPLATEID}">
        <input type="hidden" name="action" value="SavePDFTemplate">
        <input type="hidden" name="redirect" value="true">
        <input type="hidden" name="return_module" value="{$smarty.request.return_module}">
        <input type="hidden" name="return_view" value="{$smarty.request.return_view}">
        <div class="contentHeader row-fluid">
            <span class="span8 font-x-x-large textOverflowEllipsis" title="{vtranslate('LBL_EDIT','PDFMaker')} &quot;{$MODULENAME}&quot;">{vtranslate('LBL_EDIT','PDFMaker')} &quot;{$MODULENAME}&quot;</span>
            <span class="pull-right">
                <button class="btn" type="submit" onclick="document.EditView.redirect.value = 'false'; if(PDFMaker_EditJs.savePDF()) this.form.submit();" ><strong>{vtranslate('LBL_APPLY','PDFMaker')}</strong></button>&nbsp;&nbsp;
                <button class="btn btn-success" type="submit" onclick="if(PDFMaker_EditJs.savePDF()) this.form.submit();"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                {if $smarty.request.return_view neq ''}
                    <a class="cancelLink" type="reset" onclick="window.location.href = 'index.php?module={if $smarty.request.return_module neq ''}{$smarty.request.return_module}{else}PDFMaker{/if}&view={$smarty.request.return_view}{if $smarty.request.templateid neq ""}&templateid={$smarty.request.templateid}{/if}';">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {else}
                    <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {/if}         			
            </span>
        </div>
       
       <div class="modal-body tabbable" style="padding:0px;">
            <ul class="nav nav-pills" style="margin-bottom:0px; padding-left:5px;">
                <li class="active" id="properties_tab" onclick="PDFMaker_EditJs.showHideTab('properties');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_PROPERTIES_TAB','PDFMaker')}</a></li>
                <li id="company_tab" onclick="PDFMaker_EditJs.showHideTab('company');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_OTHER_INFO','PDFMaker')}</a></li>
                <li id="labels_tab" onclick="PDFMaker_EditJs.showHideTab('labels');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_LABELS','PDFMaker')}</a></li>
                <li id="products_tab" onclick="PDFMaker_EditJs.showHideTab('products');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_ARTICLE','PDFMaker')}</a></li>
                <li id="headerfooter_tab" onclick="PDFMaker_EditJs.showHideTab('headerfooter');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_HEADER_TAB','PDFMaker')} / {vtranslate('LBL_FOOTER_TAB','PDFMaker')}</a></li>
                <li id="settings_tab" onclick="PDFMaker_EditJs.showHideTab('settings');"><a data-toggle="tab" href="javascript:void(0);">{vtranslate('LBL_SETTINGS_TAB','PDFMaker')}</a></li>
            </ul>
        </div>     
  
        {********************************************* PROPERTIES DIV*************************************************}
        <table class="table table-bordered blockContainer ">
            <tbody id="properties_div">
                {* pdf source module and its available fields *}
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{if $TEMPLATEID eq ""}<span class="redColor">*</span>{/if}{vtranslate('LBL_MODULENAMES','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <input type="hidden" name="modulename" id="modulename" class="classname" value="{$SELECTMODULE}">
                        <select name="modulefields" id="modulefields" class="classname">
                            {if $TEMPLATEID eq "" && $SELECTMODULE eq ""}
                                <option value="">{vtranslate('LBL_SELECT_MODULE_FIELD','PDFMaker')}</option>
                            {else}
                                {html_options  options=$SELECT_MODULE_FIELD}
                            {/if}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('modulefields');" >{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>      						
                </tr>    					
                {* related modules and its fields *}                					
                <tr id="body_variables">
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_RELATED_MODULES','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">

                        <select name="relatedmodulesorce" id="relatedmodulesorce" class="classname" onChange="PDFMaker_EditJs.change_relatedmodule(this, 'relatedmodulefields');">
                            <option value="none">{vtranslate('LBL_SELECT_MODULE','PDFMaker')}</option>
                            {html_options  options=$RELATED_MODULES}
                        </select>
                        &nbsp;&nbsp;

                        <select name="relatedmodulefields" id="relatedmodulefields" class="classname">
                            <option>{vtranslate('LBL_SELECT_MODULE_FIELD','PDFMaker')}</option>
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('relatedmodulefields');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>      						
                </tr>
            </tbody>
            
            {********************************************* Labels *************************************************}
            <tbody style="display:none;" id="labels_div">
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_GLOBAL_LANG','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="global_lang" id="global_lang" class="classname" style="width:80%">
                            {html_options  options=$GLOBAL_LANG_LABELS}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('global_lang');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>
                </tr>
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_MODULE_LANG','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="module_lang" id="module_lang" class="classname" style="width:80%">
                            {html_options  options=$MODULE_LANG_LABELS}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('module_lang');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>
                </tr>
            </tbody>
            
             {********************************************* Company and User information DIV *************************************************}
            <tbody style="display:none;" id="company_div">
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_COMPANY_USER_INFO','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="acc_info_type" id="acc_info_type" class="classname" onChange="PDFMaker_EditJs.change_acc_info(this)">
                            {html_options  options=$CUI_BLOCKS}
                        </select>
                        <div id="acc_info_div" style="display:inline;">
                            <select name="acc_info" id="acc_info" class="classname">
                                {html_options  options=$ACCOUNTINFORMATIONS}
                            </select>
                            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('acc_info');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                        </div>
                        <div id="user_info_div" style="display:none;">
                            <select name="user_info" id="user_info" class="classname">
                                {html_options  options=$USERINFORMATIONS}
                            </select>
                            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('user_info');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                        </div>
                        <div id="logged_user_info_div" style="display:none;">
                            <select name="logged_user_info" id="logged_user_info" class="classname">
                                {html_options  options=$LOGGEDUSERINFORMATION}
                            </select>
                            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('logged_user_info');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                        </div>
                    </td>
                </tr>
                {if $MULTICOMPANYINFORMATIONS neq ''}
                    <tr>
                        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{$LBL_MULTICOMPANY}:</label></td>
                        <td class="fieldValue" colspan="3">
                            <select name="multicomapny" id="multicomapny" class="classname">
                                {html_options  options=$MULTICOMPANYINFORMATIONS}
                            </select>
                            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('multicomapny');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('TERMS_AND_CONDITIONS','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="invterandcon" id="invterandcon" class="classname">
                            {html_options  options=$INVENTORYTERMSANDCONDITIONS}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('invterandcon');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>
                </tr>
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_CURRENT_DATE','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="dateval" id="dateval" class="classname">
                            {html_options  options=$DATE_VARS}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('dateval');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>
                </tr>
                {***** BARCODES *****}
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_BARCODES','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="barcodeval" id="barcodeval" class="classname">
                            <optgroup label="{vtranslate('LBL_BARCODES_TYPE1','PDFMaker')}">
                                <option value="EAN13">EAN13</option>
                                <option value="ISBN">ISBN</option>
                                <option value="ISSN">ISSN</option>
                            </optgroup>

                            <optgroup label="{vtranslate('LBL_BARCODES_TYPE2','PDFMaker')}">
                                <option value="UPCA">UPCA</option>
                                <option value="UPCE">UPCE</option>
                                <option value="EAN8">EAN8</option>
                            </optgroup>

                            <optgroup label="{vtranslate('LBL_BARCODES_TYPE3','PDFMaker')}">
                                <option value="EAN2">EAN2</option>
                                <option value="EAN5">EAN5</option>
                                <option value="EAN13P2">EAN13P2</option>
                                <option value="ISBNP2">ISBNP2</option>
                                <option value="ISSNP2">ISSNP2</option>
                                <option value="UPCAP2">UPCAP2</option>
                                <option value="UPCEP2">UPCEP2</option>
                                <option value="EAN8P2">EAN8P2</option>
                                <option value="EAN13P5">EAN13P5</option>
                                <option value="ISBNP5">ISBNP5</option>
                                <option value="ISSNP5">ISSNP5</option>
                                <option value="UPCAP5">UPCAP5</option>
                                <option value="UPCEP5">UPCEP5</option>
                                <option value="EAN8P5">EAN8P5</option>
                            </optgroup>

                            <optgroup label="{vtranslate('LBL_BARCODES_TYPE4','PDFMaker')}">     
                                <option value="IMB">IMB</option>
                                <option value="RM4SCC">RM4SCC</option>
                                <option value="KIX">KIX</option>
                                <option value="POSTNET">POSTNET</option>
                                <option value="PLANET">PLANET</option>
                            </optgroup>

                            <optgroup label="{vtranslate('LBL_BARCODES_TYPE5','PDFMaker')}">    
                                <option value="C128A">C128A</option>
                                <option value="C128B">C128B</option>
                                <option value="C128C">C128C</option>
                                <option value="EAN128C">EAN128C</option>
                                <option value="C39">C39</option>
                                <option value="C39+">C39+</option>
                                <option value="C39E">C39E</option>
                                <option value="C39E+">C39E+</option>
                                <option value="S25">S25</option>
                                <option value="S25+">S25+</option>
                                <option value="I25">I25</option>
                                <option value="I25+">I25+</option>
                                <option value="I25B">I25B</option>
                                <option value="I25B+">I25B+</option>
                                <option value="C93">C93</option>
                                <option value="MSI">MSI</option>
                                <option value="MSI+">MSI+</option>
                                <option value="CODABAR">CODABAR</option>
                                <option value="CODE11">CODE11</option>
                            </optgroup>

                            <optgroup label="{vtranslate('LBL_QRCODE','PDFMaker')}">
                                <option value="QR">QR</option>
                            </optgroup>
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('barcodeval');">{vtranslate('LBL_INSERT_BARCODE_TO_TEXT','PDFMaker')}</button>&nbsp;&nbsp;<a href="index.php?module=PDFMaker&view=IndexAjax&mode=showBarcodes" target="_new"><i class="icon-info-sign"></i></a>
                    </td>
                </tr>
                {************************************ Custom Functions *******************************************}
                {if $TYPE eq "professional"}
                    <tr>
                        <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('CUSTOM_FUNCTIONS','PDFMaker')}:</label></td>
                        <td class="fieldValue" colspan="3">
                            <select name="customfunction" id="customfunction" class="classname">
                                {html_options options=$CUSTOM_FUNCTIONS}
                            </select>
                            <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('customfunction');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                        </td>
                    </tr>
                {/if}
            </tbody>
            {********************************************* Header/Footer *************************************************}
            <tbody style="display:none;" id="headerfooter_div">
            {* pdf header variables*}
                <tr id="header_variables">
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_HEADER_FOOTER_VARIABLES','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="header_var" id="header_var" class="classname">
                            {html_options  options=$HEAD_FOOT_VARS selected=""}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('header_var');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>
                </tr>
            </tbody>
            
            {*********************************************Products bloc DIV*************************************************}
            <tbody style="display:none;" id="products_div">
                {* product bloc tpl which is the same as in main Properties tab*}
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_PRODUCT_BLOC_TPL','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="productbloctpl2" id="productbloctpl2" class="classname">
                            {html_options  options=$PRODUCT_BLOC_TPL}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('productbloctpl2');"/>{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>
                </tr>
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_ARTICLE','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="articelvar" id="articelvar" class="classname">
                            {html_options  options=$ARTICLE_STRINGS}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('articelvar');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>
                    </td>
                </tr>
                {* insert products & services fields into text *}
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_PRODUCTS_AVLBL','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="psfields" id="psfields" class="classname">
                            {html_options  options=$SELECT_PRODUCT_FIELD}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('psfields');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>            						
                    </td>
                </tr>
                {* products fields *}                                
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_PRODUCTS_FIELDS','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="productfields" id="productfields" class="classname">
                            {html_options  options=$PRODUCTS_FIELDS}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('productfields');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>            						
                    </td>
                </tr>
                {* services fields *}                                
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">*{vtranslate('LBL_SERVICES_FIELDS','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="servicesfields" id="servicesfields" class="classname">
                            {html_options  options=$SERVICES_FIELDS}
                        </select>
                        <button type="button" class="btn btn-success marginLeftZero" onclick="InsertIntoTemplate('servicesfields');">{vtranslate('LBL_INSERT_TO_TEXT','PDFMaker')}</button>            						
                    </td>
                </tr>
                <tr>
                    <td class="fieldLabel" colspan="4"><label class="muted marginRight10px"><small>{vtranslate('LBL_PRODUCT_FIELD_INFO','PDFMaker')}</small></label></td>
                </tr>
            </tbody>   
            
            {********************************************* Settings DIV *************************************************}
            <tbody style="display:none;" id="settings_div">
                {* pdf format settings *}
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_PDF_FORMAT','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <table style="padding:0px; margin:0px;" cellpadding="0" cellspacing="0">
                            <tr>                                       
                                <td><select name="pdf_format" id="pdf_format" class="classname" onchange="PDFMaker_EditJs.CustomFormat();">
                                        {html_options  options=$FORMATS selected=$SELECT_FORMAT}
                                    </select>
                                </td>
                                <td style="padding:0">
                                    <table class="table showInlineTable" id="custom_format_table" {if $SELECT_FORMAT neq 'Custom'}style="display:none"{/if}>
                                        <tr>
                                            <td align="right" nowrap>{vtranslate('LBL_WIDTH','PDFMaker')}</td>
                                            <td>
                                                <input type="text" name="pdf_format_width" id="pdf_format_width" class="detailedViewTextBox" value="{$CUSTOM_FORMAT.width}" style="width:50px">
                                            </td>
                                            <td align="right" nowrap>{vtranslate('LBL_HEIGHT','PDFMaker')}</td>
                                            <td>
                                                <input type="text" name="pdf_format_height" id="pdf_format_height" class="detailedViewTextBox" value="{$CUSTOM_FORMAT.height}" style="width:50px">
                                            </td>
                                        </tr>
                                    </table>
                                </td>                                   
                            </tr>
                        </table>

                    </td>
                </tr>
                {* pdf orientation settings *}
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_PDF_ORIENTATION','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <select name="pdf_orientation" id="pdf_orientation" class="classname">
                            {html_options  options=$ORIENTATIONS selected=$SELECT_ORIENTATION}
                        </select>
                    </td>
                </tr>
                {* ignored picklist values settings *}
                <tr>
                    <td class="fieldLabel" title="{vtranslate('LBL_IGNORE_PICKLIST_VALUES_DESC','PDFMaker')}"><label class="muted pull-right marginRight10px">{vtranslate('LBL_IGNORE_PICKLIST_VALUES','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3" title="{vtranslate('LBL_IGNORE_PICKLIST_VALUES_DESC','PDFMaker')}"><input type="text" name="ignore_picklist_values" value="{$IGNORE_PICKLIST_VALUES}" class="detailedViewTextBox"/></td>
                </tr>
                {* pdf margin settings *}
                {assign var=margin_input_width value='50px'}
                {assign var=margin_label_width value='50px'}
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_MARGINS','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <table>
                            <tr>
                                <td align="right" nowrap>{vtranslate('LBL_TOP','PDFMaker')}</td>
                                <td>
                                    <input type="text" name="margin_top" id="margin_top" class="detailedViewTextBox" value="{$MARGINS.top}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_top', false);">
                                </td>
                                <td align="right" nowrap>{vtranslate('LBL_BOTTOM','PDFMaker')}</td>
                                <td>
                                    <input type="text" name="margin_bottom" id="margin_bottom" class="detailedViewTextBox" value="{$MARGINS.bottom}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_bottom', false);">
                                </td>
                                <td align="right" nowrap>{vtranslate('LBL_LEFT','PDFMaker')}</td>
                                <td>
                                    <input type="text" name="margin_left"  id="margin_left" class="detailedViewTextBox" value="{$MARGINS.left}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_left', false);">
                                </td>
                                <td align="right" nowrap>{vtranslate('LBL_RIGHT','PDFMaker')}</td>
                                <td>
                                    <input type="text" name="margin_right" id="margin_right" class="detailedViewTextBox" value="{$MARGINS.right}" style="width:{$margin_input_width}" onKeyUp="PDFMaker_EditJs.ControlNumber('margin_right', false);">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                {* decimal settings *}    					
                <tr>
                    <td class="fieldLabel"><label class="muted pull-right marginRight10px">{vtranslate('LBL_DECIMALS','PDFMaker')}:</label></td>
                    <td class="fieldValue" colspan="3">
                        <table>
                            <tr>
                                <td align="right" nowrap>{vtranslate('LBL_DEC_POINT','PDFMaker')}</td>
                                <td><input type="text" maxlength="2" name="dec_point" class="detailedViewTextBox" value="{$DECIMALS.point}" style="width:{$margin_input_width}"/></td>

                                <td align="right" nowrap>{vtranslate('LBL_DEC_DECIMALS','PDFMaker')}</td>
                                <td><input type="text" maxlength="2" name="dec_decimals" class="detailedViewTextBox" value="{$DECIMALS.decimals}" style="width:{$margin_input_width}"/></td>

                                <td align="right" nowrap>{vtranslate('LBL_DEC_THOUSANDS','PDFMaker')}</td>
                                <td><input type="text" maxlength="2" name="dec_thousands"  class="detailedViewTextBox" value="{$DECIMALS.thousands}" style="width:{$margin_input_width}"/></td>                                       
                            </tr>
                        </table>
                    </td>
                </tr>    					
            </tbody>
        </table>

       {************************************** END OF TABS BLOCK *************************************}                         

        <div class="modal-body tabbable" style="padding:0px;">
            <ul class="nav nav-pills" style="margin-bottom:0px; padding-left:5px;">
                <li class="active" id="body_tab2" onclick="PDFMaker_EditJs.showHideTab3('body');"><a data-toggle="tab1" href="javascript:void(0);">{vtranslate('LBL_BODY','PDFMaker')}</a></li>
                <li id="header_tab2" onclick="PDFMaker_EditJs.showHideTab3('header');"><a data-toggle="tab1" href="javascript:void(0);">{vtranslate('LBL_HEADER_TAB','PDFMaker')}</a></li>
                <li id="footer_tab2" onclick="PDFMaker_EditJs.showHideTab3('footer');"><a data-toggle="tab1" href="javascript:void(0);">{vtranslate('LBL_FOOTER_TAB','PDFMaker')}</a></li>
            </ul>
        </div>
        {*literal}   
            <script type="text/javascript" src="modules/PDFMaker/include/ckeditor/ckeditor.js"></script>
        {/literal*} 

        {*********************************************BODY DIV*************************************************}
        <div style="display:block;" id="body_div2">
            <textarea name="body" id="body" style="width:90%;height:700px" class=small tabindex="5">{$BODY}</textarea>
        </div>

        <script type="text/javascript">
            {literal} jQuery(document).ready(function(){ CKEDITOR.replace('body', {height: '1000'}); }){/literal}                        
        </script>

        {*********************************************Header DIV*************************************************}
        <div style="display:none;" id="header_div2">
            <textarea name="header_body" id="header_body" style="width:90%;height:200px" class="small">{$HEADER}</textarea>
        </div>

        <script type="text/javascript">
            {literal} jQuery(document).ready(function(){ CKEDITOR.replace('header_body', {height: '1000'}); }) {/literal} 
        </script>

        {*********************************************Footer DIV*************************************************}
        <div style="display:none;" id="footer_div2">
            <textarea name="footer_body" id="footer_body" style="width:90%;height:200px" class="small">{$FOOTER}</textarea>
        </div>

        <script type="text/javascript">
            {literal} jQuery(document).ready(function(){ CKEDITOR.replace('footer_body', {height: '1000'}); }) {/literal} 
        </script>

        {*literal} <script type="text/javascript" src="modules/PDFMaker/fck_config.js"></script>{/literal*}

        <div class="contentHeader row-fluid">
            <span class="pull-right">
                <button class="btn" type="submit" onclick="document.EditView.redirect.value = 'false'; if(PDFMaker_EditJs.savePDF()) this.form.submit();" ><strong>{vtranslate('LBL_APPLY','PDFMaker')}</strong></button>&nbsp;&nbsp;
                <button class="btn btn-success" type="submit" onclick="if(PDFMaker_EditJs.savePDF()) this.form.submit();"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                {if $smarty.request.return_view neq ''}
                    <a class="cancelLink" type="reset" onclick="window.location.href = 'index.php?module={if $smarty.request.return_module neq ''}{$smarty.request.return_module}{else}PDFMaker{/if}&view={$smarty.request.return_view}{if $smarty.request.templateid neq ""}&templateid={$smarty.request.templateid}{/if}';">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {else}
                    <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                {/if}            			
            </span>
        </div>
    <div align="center" class="small" style="color: rgb(153, 153, 153);">{vtranslate('PDF_MAKER','PDFMaker')} {$VERSION} {vtranslate('COPYRIGHT','PDFMaker')}</div>
    </form>
</div>
<script type="text/javascript">

    var selectedTab = 'properties';
    var selectedTab2 = 'body';
    var module_blocks = new Array();
    {foreach item=moduleblocks key=blockname from=$MODULE_BLOCKS}
        module_blocks["{$blockname}"] = new Array({$moduleblocks});
    {/foreach}

    var module_fields = new Array();
    {foreach item=modulefields key=modulename from=$MODULE_FIELDS}
        module_fields["{$modulename}"] = new Array({$modulefields});
    {/foreach}

    var all_related_modules = new Array();
    {foreach item=related_modules key=relatedmodulename from=$ALL_RELATED_MODULES}
    //all_related_modules["{$relatedmodulename}"] = new Array(app.vtranslate('LBL_SELECT_MODULE'), 'none'{foreach item=module1 from=$related_modules}, app.vtranslate('{$module1.2}') + ' | {$module1.1}','{$module1.2} |{$module1.0}'{/foreach});
    all_related_modules["{$relatedmodulename}"] = new Array('{vtranslate("LBL_SELECT_MODULE")}','none'{foreach item=module1 from=$related_modules},'{vtranslate($module1.2)|escape} ({$module1.1})','{$module1.2}|{$module1.0}'{/foreach});
    {/foreach}

    var related_module_fields = new Array();
    {foreach item=relatedmodulefields key=relatedmodulename from=$RELATED_MODULE_FIELDS}
    related_module_fields["{$relatedmodulename}"] = new Array({$relatedmodulefields});
    {/foreach}

    function InsertIntoTemplate(element){ldelim}

    selectField = document.getElementById(element).value;
    if (selectedTab2 == "body")
    var oEditor = CKEDITOR.instances.body;
    else if (selectedTab2 == "header")
    var oEditor = CKEDITOR.instances.header_body;
    else if (selectedTab2 == "footer")
    var oEditor = CKEDITOR.instances.footer_body;
    if (element != 'header_var' && element != 'footer_var' && element != 'hmodulefields' && element != 'fmodulefields' && element != 'dateval'){ldelim}
    if (selectField != ''){ldelim}
        if (selectField == 'ORGANIZATION_STAMP_SIGNATURE')
        insert_value = '{$COMPANY_STAMP_SIGNATURE}';
        else if (selectField == 'COMPANY_LOGO')
        insert_value = '{$COMPANYLOGO}';
        else if (selectField == 'ORGANIZATION_HEADER_SIGNATURE')
        insert_value = '{$COMPANY_HEADER_SIGNATURE}';
        else if (selectField == 'VATBLOCK')
        insert_value = '{$VATBLOCK_TABLE}';
        else {ldelim}
            if (element == "articelvar")
            insert_value = '#' + selectField + '#';
            else if (element == "relatedmodulefields")
            insert_value = '$R_' + selectField + '$';
            else if (element == "productbloctpl" || element == "productbloctpl2")
            insert_value = selectField;
            else if (element == "global_lang")
            insert_value = '%G_' + selectField + '%';
            else if (element == "module_lang")
            insert_value = '%M_' + selectField + '%';
            else if (element == "barcodeval")
            insert_value = '[BARCODE|' + selectField + '=YOURCODE|BARCODE]';
            else
            insert_value = '$' + selectField + '$';
        {rdelim}
        oEditor.insertHtml(insert_value);
    {rdelim}

    {rdelim} else {ldelim}

    if (selectField != ''){ldelim}
    if (element == 'hmodulefields' || element == 'fmodulefields')
            oEditor.insertHtml('$' + selectField + '$');
            else
            oEditor.insertHtml(selectField);
    {rdelim}
    {rdelim}
    {rdelim}
</script>
