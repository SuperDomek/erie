{**
 * management.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the author's submission management table.
 *
 * $Id$
 *}
<div id="submission">
<ul class="no-list">
<li>
	<header>{translate key="paper.authors"}</header>
	{$submission->getAuthorString(false)|escape}
</li>
<li>
	<header>{translate key="paper.title"}</header>
	{$submission->getLocalizedTitle()|strip_unsafe_html}
</li>

{assign var=sessionType value=$submission->getData('sessionType')}
{if isset($sessionTypes[$sessionType])}
	<li>
		<header>{translate key="paper.sessionType"}</header>
		{$sessionTypes[$sessionType]|escape}
	</li>
{/if}{* isset($submissionTypes[$submissionType]) *}

<li>
	<header>{translate key="submission.originalFile"}</header>
	
		{if $submissionFile}
			<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$submissionFile->getFileId():$submissionFile->getRevision()}" class="file">{$submissionFile->getFileName()|escape}</a>&nbsp;&nbsp;{$submissionFile->getDateModified()|date_format:$dateFormatShort}
		{else}
			{translate key="common.none"}
		{/if}
	
</li>
<!--
<li>
	<header>{translate key="paper.suppFilesAbbrev"}</header>
	
		{foreach name="suppFiles" from=$suppFiles item=suppFile}
			{if $mayEditPaper}
				{assign var="suppFileOp" value="editSuppFile"}
			{else}
				{assign var="suppFileOp" value="viewSuppFile"}
			{/if}
			<a href="{url op=$suppFileOp path=$submission->getPaperId()|to_array:$suppFile->getId()}" class="file">{$suppFile->getFileName()|escape}</a>&nbsp;&nbsp;{$suppFile->getDateModified()|date_format:$dateFormatShort}<br />
		{foreachelse}
			{translate key="common.none"}
		{/foreach}
	
	
		{if $mayEditPaper}
			<a href="{url op="addSuppFile" path=$submission->getPaperId()}" class="action">{translate key="submission.addSuppFile"}</a>
		{else}
			&nbsp;
		{/if}
	
</li>
-->
<li>
	<header>{translate key="submission.submitter"}</header>
	
		{assign var="submitter" value=$submission->getUser()}
		{assign var=emailString value=$submitter->getFullName()|concat:" <":$submitter->getEmail():">"}
		{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$currentUrl subject=$submission->getLocalizedTitle|strip_tags paperId=$submission->getPaperId()}
		{$submitter->getFullName()|escape} {icon name="mail" url=$url}
	
</li>
<li>
	<header>{translate key="common.dateSubmitted"}</header>
	{$submission->getDateSubmitted()|date_format:$datetimeFormatLong}
</li>
{if $submission->getCommentsToDirector()}
<li>
	<header>{translate key="paper.commentsToDirector"}</header>
	{$submission->getCommentsToDirector()|strip_unsafe_html|nl2br}
</li>
{/if}
<!--{if $publishedPaper}
<li>
	<header>{translate key="submission.abstractViews"}</header>
	{$publishedPaper->getViews()}
</li>
{/if}-->
</ul>
</div>
