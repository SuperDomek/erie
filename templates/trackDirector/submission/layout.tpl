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
{assign var=layoutFile value=$submission->getLayoutFile()}
<div id="layout">
<h3>{translate key="submission.layout"}</h3>

<p>{translate key="submission.layout.description"}</p>

<!--<p>
	{translate key="common.file"}:&nbsp;&nbsp;&nbsp;&nbsp;
	{if $layoutFile}
		<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$layoutFile->getFileId()}" class="file">{$layoutFile->getFileName()|escape}</a>&nbsp;&nbsp;{$layoutFile->getDateModified()|date_format:$dateFormatShort}
		{else}
		{translate key="common.none"}
	{/if}
</p>-->

{if $layoutFile}
{assign var=layoutFileChecked value=$layoutFile->getChecked()}
<div class="tbl-container">
	<table class="files">
	<thead>
		<tr>
			<td>{translate key="common.fileName"}</td>
			<td>{translate key="common.status"}</td>
			<td>{translate key="common.fileSize"}</td>
			<td>{translate key="common.fileType"}</td>
			<td>{translate key="common.dateUploaded"}</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td width="50%">
				<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$layoutFile->getFileId()}" class="file" >{icon name="page_text"}</a>
				<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$layoutFile->getFileId()}" class="file" >{$layoutFile->getFileName()|escape}</a>
			</td>
			<td width="10%">
			{if $layoutFileChecked == 1}
				<span style="color: #0b9e3f;">{translate key="submission.fileAccepted"}</span></td>
			{elseif $layoutFileChecked === null}
				<span style="color: #e85a09">{translate key="submission.filePending"}
			
			{elseif $layoutFileChecked == 0}
				<span style="color:#e85a09;">{translate key="submission.comments.comments"}
			{/if}
			<td width="10%">{$layoutFile->getNiceFileSize()}</td>
			<td width="20%">{$layoutFile->getFileType()|truncate:30}</td>
			<td width="10%">{$layoutFile->getDateModified()|date_format:$dateFormatShort}</td>
		</tr>
		{if $layoutFileChecked === 0}
			<td colspan="5">
				<textarea id="layoutCommentText" name="layoutCommentText" class="textArea" rows="5" disabled="disabled">{$layoutComment}</textarea>
			</td>
		{/if}
	</tbody>
	</table>
</div>
{/if}

{*<table width="100%" class="info">
	<tr>
		<td colspan="6" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">{translate key="submission.layout.galleyFormat"}</td>
		<td class="heading">{translate key="common.file"}</td>
		<td class="heading">{translate key="common.order"}</td>
		<td class="heading">{translate key="common.action"}</td>
		<td class="heading">{translate key="submission.views"}</td>
	</tr>
	{foreach name=galleys from=$submission->getGalleys() item=galley}
	<tr>
		<td width="2%">{$smarty.foreach.galleys.iteration}.</td>
		<td width="26%">{$galley->getGalleyLabel()|escape} &nbsp; <a href="{url op="proofGalley" path=$submission->getPaperId()|to_array:$galley->getId()}" class="action">{translate key="submission.layout.viewProof"}</td>
		<td><a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$galley->getFileId()}" class="file">{$galley->getFileName()|escape}</a>&nbsp;&nbsp;{$galley->getDateModified()|date_format:$dateFormatShort}</td>
		<td><a href="{url op="orderGalley" d=u paperId=$submission->getPaperId() galleyId=$galley->getId()}" class="plain">&uarr;</a> <a href="{url op="orderGalley" d=d paperId=$submission->getPaperId() galleyId=$galley->getId()}" class="plain">&darr;</a></td>
		<td>
			<a href="{url op="editGalley" path=$submission->getPaperId()|to_array:$galley->getId():$stage}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteGalley" path=$submission->getPaperId()|to_array:$galley->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="submission.layout.confirmDeleteGalley"}')" class="action">{translate key="common.delete"}</a>
		</td>
		<td>{$galley->getViews()|escape}</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="6" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}
	<!-- <tr>
		<td colspan="6" class="separator">&nbsp;</td>
	</tr>
	<tr>
		<td width="28%" colspan="2">{translate key="submission.supplementaryFiles"}</td>
		<td width="34%" class="heading">{translate key="common.file"}</td>
		<td width="16%" class="heading">{translate key="common.order"}</td>
		<td width="16%" colspan="2" class="heading">{translate key="common.action"}</td>
	</tr>
	{foreach name=suppFiles from=$submission->getSuppFiles() item=suppFile}
	<tr>
		<td width="2%">{$smarty.foreach.suppFiles.iteration}.</td>
		<td width="26%">{$suppFile->getSuppFileTitle()|escape}</td>
		<td><a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$suppFile->getFileId()}" class="file">{$suppFile->getFileName()|escape}</a>&nbsp;&nbsp;{$suppFile->getDateModified()|date_format:$dateFormatShort}</td>
		<td><a href="{url op="orderSuppFile" d=u paperId=$submission->getPaperId() suppFileId=$suppFile->getId()}" class="plain">&uarr;</a> <a href="{url op="orderSuppFile" d=d paperId=$submission->getPaperId() suppFileId=$suppFile->getId()}" class="plain">&darr;</a></td>
		<td colspan="2">
			<a href="{url op="editSuppFile" from="submissionReview" path=$submission->getPaperId()|to_array:$suppFile->getId()}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteSuppFile" from="submissionReview" path=$submission->getPaperId()|to_array:$suppFile->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="submission.layout.confirmDeleteSupplementaryFile"}')" class="action">{translate key="common.delete"}</a>
		</td>
	</tr>
	{foreachelse}
	<tr>
		<td colspan="6" class="nodata">{translate key="common.none"}</td>
	</tr>
	{/foreach}-->
	<tr>
		<td colspan="6" class="separator">&nbsp;</td>
	</tr>
</table>*}

<form method="post" action="{url op="uploadLayoutFile"}"  enctype="multipart/form-data">
	<input type="hidden" name="from" value="submissionReview" />
	<input type="hidden" name="paperId" value="{$submission->getPaperId()}" />
	<input type="hidden" name="stage" value="{$stage|escape}" />
	<input type="hidden" name="layoutFileType" value="layout" />
	{translate key="submission.uploadFileTo"}
		<!--<input type="radio" checked="checked" name="layoutFileType" id="layoutFileTypeGalley" value="galley" />
		<label for="layoutFileTypeGalley">{translate key="submission.galley"}</label>,
		<input type="radio" name="layoutFileType" id="layoutFileTypeSupp" value="supp" />
		<label for="layoutFileTypeSupp">{translate key="paper.suppFilesAbbrev"}</label>-->
	<input type="file" name="layoutFile" size="10" class="uploadField" />
	<input type="submit" value="{translate key="common.upload"}" class="button" />
</form>
</div>
