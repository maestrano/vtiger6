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
<div class="listViewEntriesDiv contents-bottomscroll">
    <div class="bottomscroll-div">
        <table border=0 cellspacing=0 cellpadding=5 width=100% class="table table-bordered listViewEntriesTable">
            <thead>
                <tr class="listViewHeaders">
                    <th width="2%" class="narrowWidthType">#</td>
                    <th width="78%" class="narrowWidthType">{vtranslate("LBL_MODULENAMES","PDFMaker")}</td>
                    <th width="20%" class="narrowWidthType">{vtranslate("LBL_ACTION","PDFMaker")}</td>
                </tr>
            </thead>
            <tbody>
            {foreach item=template name=mailmerge from=$PDFTEMPLATES}
                <tr class="listViewEntries" data-id="{$template.templateid}" data-recordurl="index.php?module=PDFMaker&view=Detail&templateid={$template.templateid}" id="PDFMaker_listView_row_{$template.templateid}">
                    <td class="narrowWidthType" valign=top>{$smarty.foreach.mailmerge.iteration}</td>
                    <td class="narrowWidthType" valign=top>{$template.module}</a></td>
                    <td class="narrowWidthType" valign=top nowrap>{$template.edit}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
<br>
<div align="center" class="small" style="color: rgb(153, 153, 153);">{vtranslate("PDF_MAKER","PDFMaker")} {$VERSION} {vtranslate("COPYRIGHT","PDFMaker")}</div>
{/strip}