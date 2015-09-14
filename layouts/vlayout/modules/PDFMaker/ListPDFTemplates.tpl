{*<!--
/*********************************************************************************
* The content of this file is subject to the PDF Maker Free license.
* ("License"); You may not use this file except in compliance with the License
* The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
* Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
<script type="text/javascript" src="layouts/vlayout/modules/PDFMaker/resources/PDFMakerActions.js"></script>
<script type="text/javascript" src="layouts/vlayout/modules/PDFMaker/resources/PDFMaker.js"></script>
<div class="contentsDiv marginLeftZero">
    <div class="listViewPageDiv">
        {include file='ListPDFHeader.tpl'|@vtemplate_path:'PDFMaker'}
        <div class="listViewContentDiv" id="listViewContents">
            {include file='ListPDFTemplatesContents.tpl'|@vtemplate_path:'PDFMaker'}
        </div>
    </div>
</div>
{/strip}