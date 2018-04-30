{**
 * management.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the submission management table.
 *
 * $Id$
 *}
<div id="submission">

{assign var="submissionFile" value=$submission->getSubmissionFile()}
{assign var="suppFiles" value=$submission->getSuppFiles()}

<ul class="no-list">
{if $isDirector}{*track director could be a reviewer as well*}
	<li>
		<header>{translate key="paper.authors"}</header>
		{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$submission->getAuthorEmails() subject=$submission->getLocalizedTitle() paperId=$submission->getPaperId()}
		<a href="{$url}" alt="{translate key="paper.authors.mail"}" title="{translate key="paper.authors.mail"}">{$submission->getAuthorString()|escape}</a>
	</li>
{/if}
<li>
	<header>{translate key="paper.submitterId"}</header>
	{$submitterId}</li>
<li>
	<header>{translate key="paper.title"}</header>
	{$submission->getLocalizedTitle()|strip_unsafe_html}
</li>
{if $isDirector}
  {if $submissionFile || $submission->getReviewMode() != REVIEW_MODE_ABSTRACTS_ALONE}
		<li>
			<header>{translate key="submission.originalFile"}</header>
			{if $submissionFile}
				<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$submissionFile->getFileId()}" class="file">{$submissionFile->getFileName()|escape}</a>&nbsp;&nbsp;({$submissionFile->getDateModified()|date_format:$dateFormatShort})
			{else}
				{translate key="common.none"}
			{/if}
		</li>
  {/if}

	<li>
		<header>{translate key="submission.submitter"}</header>
		{assign var="submitter" value=$submission->getUser()}
		{assign var=emailString value=$submitter->getFullName()|concat:" <":$submitter->getEmail():">"}
		{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getLocalizedTitle|strip_tags paperId=$submission->getPaperId()}
		{$submitter->getFullName()|escape}
	</li>
{/if}
<li>
	<header>{translate key="common.dateSubmitted"}</header>
	{$submission->getDateSubmitted()|date_format:$dateFormatShort}
</li>
{if $isDirector}{*track director could be a reviewer as well*}
<li>
	<header>{translate key="track.track"}</header>
	{if $tracks|@count == 1}
		{assign var="trackId" value=$submission->getTrackId()}
		{$tracks.$trackId}
	{else}
		<form action="{url op="changeTrack" paperId=$submission->getPaperId()}" method="post">
			<input type="hidden" name="from" value="submission" />
			<input type="hidden" name="stage" value="{$stage|escape}" />
			<select name="trackId" size="1" class="selectMenu" >
				{html_options options=$tracks selected=$submission->getTrackId()}
			</select>
			<input type="submit" value="{translate key="common.record"}" class="button" />
		</form>
	{/if}
</li>
{/if}

{assign var=sessionType value=$submission->getData('sessionType')}
{if is_array($sessionTypes) && !empty($sessionTypes) && !(count($sessionTypes) == 1 && !empty($sessionType) && isset($sessionTypes[$sessionType]))}
	<li>
		<header>{translate key="paper.sessionType"}</header>
		<form action="{url op="changeSessionType" paperId=$submission->getPaperId()}" method="post">
			<select name="sessionType" size="1" class="selectMenu">
				{if empty($sessionType) || !isset($sessionTypes[$sessionType])}<option value=""></option>{/if}
				{html_options options=$sessionTypes selected=$sessionType}
			</select>
			<input type="submit" value="{translate key="common.record"}" class="button" />
		</form>
	</li>
{/if}{* if we should display session type dropdown *}

{if $submission->getCommentsToDirector()}
<li>
	<header>{translate key="paper.commentsToDirector"}</header>
	{$submission->getCommentsToDirector()|strip_unsafe_html|nl2br}
</li>
{/if}
{*{if $publishedPaper}
<li>
	<header>{translate key="submission.abstractViews"}</header>
	{$publishedPaper->getViews()}
</li>
{/if}*}
</ul>
</div>
