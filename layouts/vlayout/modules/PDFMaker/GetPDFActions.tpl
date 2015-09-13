{*
/*********************************************************************************
 * The content of this file is subject to the PDF Maker Free license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
*}
{if $ENABLE_EMAILMAKER neq 'true'}
    {assign var="EMAIL_FUNCTION" value="sendPDFmail"}
{else}
    {assign var="EMAIL_FUNCTION" value="sendEMAILMakerPDFmail"}
{/if}

{if $ENABLE_PDFMAKER eq 'true'}
<div class="row-fluid">
	<div class="span10">
            <ul class="nav nav-list">
		<input type="hidden" name="template_id" id="template_id" value="{$PDFTEMPLATEID}"/>
                {if $TEMPLATE_LANGUAGES|@sizeof > 1}
			<li>   	
			    <select name="template_language" id="template_language" class="detailedViewTextBox" style="width:130%;" size="1">
					{html_options  options=$TEMPLATE_LANGUAGES selected=$CURRENT_LANGUAGE}
			    </select>
			</li>
		{else}
			{foreach from="$TEMPLATE_LANGUAGES" item="lang" key="lang_key"}
		    	<input type="hidden" name="template_language" id="template_language" value="{$lang_key}"/>
			{/foreach}
		{/if}
                {* Export to PDF *}
		<li>
		    <a href="javascript:;" onclick="if((navigator.userAgent.match(/iPad/i)!= null)||(navigator.userAgent.match(/iPhone/i)!= null)||(navigator.userAgent.match(/iPod/i)!= null)) window.open('index.php?module=PDFMaker&relmodule={$MODULE}&action=CreatePDFFromTemplate&record={$ID}&language='+document.getElementById('template_language').value); else document.location.href='index.php?module=PDFMaker&relmodule={$MODULE}&action=CreatePDFFromTemplate&record={$ID}&language='+document.getElementById('template_language').value;" class="webMnu" style="padding-left:10px;"><img src="layouts/vlayout/modules/PDFMaker/images/actionGeneratePDF.gif" hspace="5" align="absmiddle" border="0" style="border-radius:3px;" /> {vtranslate('LBL_EXPORT','PDFMaker')}</a>
		</li>
                <li>
                    <a href="javascript:PDFMakerCommon.showPDFBreakline('{$ID}');" class="webMnu" style="padding-left:10px;"><img src="layouts/vlayout/modules/PDFMaker/images/PDF_bl.png" hspace="5" align="absmiddle" border="0" style="border-radius:3px;" />{vtranslate('LBL_PRODUCT_BREAKLINE','PDFMaker')}</a>                
                    <div id="PDFBreaklineDiv" style="display:none; width:350px; position:absolute;" class="layerPopup"></div>                
                </li>
                <li>
                    <a href="javascript:PDFMakerCommon.showproductimages('{$ID}');" class="webMnu" style="padding-left:10px;"><img src="layouts/vlayout/modules/PDFMaker/images/PDF_img.png" hspace="5" align="absmiddle" border="0" style="border-radius:3px;" /> {vtranslate('LBL_PRODUCT_IMAGE', 'PDFMaker')}</a>                
                </li>
            </ul>
	</div>
	<br clear="all"/>
 	<div id="alert_doc_title" style="display:none;">{$PDFMAKER_MOD.ALERT_DOC_TITLE}</div>
</div>
{else}
<div class="row-fluid">
	<div class="span10">
		<ul class="nav nav-list">
			<li><a href="index.php?module=PDFMaker&view=List">{vtranslate('LBL_PLEASE_FINISH_INSTALLATION', 'PDFMaker')}</a></li>
		</ul>
	</div>
</div>
{/if}
