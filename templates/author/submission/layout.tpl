{**
 * layout.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the layout editing table.
 *
 * $Id$
 *}

{literal}
<script type="text/javascript">
<!--
// shows/hides comment box
function showCommentBox(sel) {
	var commentBlock = document.getElementById("layoutComment");
	var accButton = document.getElementById("layoutAccept");
	if(commentBlock.style.display == "none"){
		commentBlock.style.display = "table-row";
		accButton.disabled = "disabled";
	}
	else {
		commentBlock.style.display = "none";
		accButton.disabled = "";
	}
}
// -->
</script>
{/literal}

{assign var=layoutFile value=$submission->getLayoutFile()}
{assign var=layoutFileChecked value=$layoutFile->getChecked()}
<div id="layout">
<h3>{translate key="submission.layout.layoutFile"}</h3>

<div class="tbl-container">
	<table class="files">
	<thead>
		<tr>
			<td>{translate key="common.fileName"}</td>
			<td>{translate key="common.fileSize"}</td>
			<td>{translate key="common.fileType"}</td>
			<td>{translate key="common.dateUploaded"}</td>
			<td></td>
		</tr>
	</thead>
	<tbody>
	<form method="post" id="formLayout" action="{url op="saveLayoutResp" path=$submission->getPaperId()|to_array}">
		<tr>
			<td width="40%">
				<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$layoutFile->getFileId()}" class="file" >
					{icon name="page_text"}
				</a>
				<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$layoutFile->getFileId()}" class="file" >{$layoutFile->getFileName()|escape}</a>
			</td>
			{*<td><span style="color: #0b9e3f;">{translate key="submission.fileAccepted"}</span></td>*}
			<td width="10%">{$layoutFile->getNiceFileSize()}</td>
			<td width="20%">{$layoutFile->getFileType()|truncate:30}</td>
			<td width="10%">{$layoutFile->getDateModified()|date_format:$dateFormatShort}</td>
			<td width="20%">
				{if $layoutFileChecked == null}
				<button type="submit" form="formLayout" name="layoutAccept" class="button positive" id="layoutAccept" value="1">{translate key="common.accept"}</button>
				<button type="button" class="button negative" onclick="showCommentBox(this);">{translate key="common.comment"}</button>
				{elseif $layoutFileChecked == 1}
				<span style="color: #0b9e3f;">{translate key="submission.fileAccepted"}
				{elseif $layoutFileChecked == 0}
				<span style="color:#e85a09;">{translate key="submission.comments.comments"}
				{/if}
			</td>
		</tr>
		<!-- If comment entered then don't show buttons and show the comment text below -->
		{if $layoutFileChecked == null}
		<tr id="layoutComment" style="display:none;">
			<td colspan="4">
				<input type="hidden" name="paperId" value="{$submission->getPaperId()}"/>
				<input type="hidden" name="fileId" value="{$layoutFile->getFileId()}"/>
				<textarea id="layoutCommentText" name="layoutCommentText" class="textArea" rows="5">{$layoutComment}</textarea>	
			</td>
			<!-- Add javascript check for empty comment -->
			<td><button type="submit" form="formLayout" name="submit" value="1" class="button positive">{translate key="form.submit"}</button></td>
		</tr>
		{elseif $layoutFileChecked == 0}
		<tr id="layoutComment">
			<td colspan="5">
				<textarea id="layoutCommentText" name="layoutCommentText" class="textArea" rows="5" disabled="disabled">{$layoutComment}</textarea>
			</td>
		</tr>
		{/if}
	</form>
	</tbody>
	</table>
</div>

{*<table width="100%" class="info">
	<tr>
		<td width="40%" colspan="2">{translate key="submission.layout.galleyFormat"}</td>
		<td width="40%" colspan="2" class="heading">{translate key="common.file"}</td>
		<td align="right" class="heading">{translate key="submission.views"}</td>
	</tr>
	{foreach name=galleys from=$submission->getGalleys() item=galley}
	<tr>
		<td width="5%">{$smarty.foreach.galleys.iteration}.</td>
		<td width="35%">{$galley->getGalleyLabel()|escape}
		<td colspan="2"><a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$galley->getFileId()}" class="file">{$galley->getFileName()|escape}</a>&nbsp;&nbsp;{$galley->getDateModified()|date_format:$dateFormatShort}</td>
		<td align="right">{$galley->getViews()}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="5" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="5" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">{translate key="submission.supplementaryFiles"}</td>
		<td colspan="3" class="heading">{translate key="common.file"}</td>
	</tr>
	{foreach name=suppFiles from=$submission->getSuppFiles() item=suppFile}
	<tr>
		<td width="5%">{$smarty.foreach.suppFiles.iteration}.</td>
		<td width="35%">{$suppFile->getSuppFileTitle()|escape}</td>
		<td colspan="3"><a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$suppFile->getFileId()}" class="file">{$suppFile->getFileName()|escape}</a>&nbsp;&nbsp;{$suppFile->getDateModified()|date_format:$dateFormatShort}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="5" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}
	<tr>
		<td colspan="5" class="separator">&nbsp;</td>
	</tr>
</table>*}
</div>	
