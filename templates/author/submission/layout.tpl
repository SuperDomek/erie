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
				<a href="#"><button class="button positive">{translate key="common.accept"}</button></a>
				<a href="#"><button class="button negative">{translate key="common.comment"}</button></a>
			</td>
		</tr>
	</tbody>
	</table>
	<p>{translate key="submission.layout.layoutDescription"}</p>
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
