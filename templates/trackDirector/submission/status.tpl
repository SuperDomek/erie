{**
 * status.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the submission status table.
 *
 * $Id$
 *}
<div id="status">
<h3>{translate key="common.status"}</h3>

<ul class="no-list">
	<li>
		{assign var="status" value=$submission->getSubmissionStatus()}
		<header>{translate key="common.status"}</header>
		{if $status == STATUS_ARCHIVED}{translate key="submissions.archived"}
		{elseif $status == STATUS_QUEUED_UNASSIGNED}{translate key="submissions.queuedUnassigned"}
		{elseif $status == STATUS_QUEUED_EDITING}{translate key="submissions.queuedEditing"}
		{elseif $status == STATUS_QUEUED_REVIEW}
			{if $submission->getCurrentStage() >= REVIEW_STAGE_PRESENTATION}
				{translate key="submissions.queuedPaperReview"}
			{else}
				{translate key="submissions.queuedAbstractReview"}
			{/if}
		{elseif $status == STATUS_PUBLISHED}{translate key="submissions.published"}
		{elseif $status == STATUS_DECLINED}{translate key="submissions.declined"}
		{elseif $status == STATUS_INCOMPLETE}{translate key="submissions.incomplete"}
		{/if}
		<br />
		{if $status != STATUS_ARCHIVED}
			<a href="{url op="unsuitableSubmission" paperId=$submission->getPaperId()}">
				<button class="button">{translate key="director.paper.archiveSubmission"}</button></a>
		{else}
			<a href="{url op="restoreToQueue" path=$submission->getPaperId()}">
				<button class="button">{translate key="director.paper.restoreToQueue"}</button></a>
		{/if}
	</li>
	<li>
		<header>{translate key="submission.initiated"}</header>
		{$submission->getDateStatusModified()|date_format:$dateFormatShort}
	</li>
	<li>
		<header>{translate key="submission.lastModified"}</header>
		{$submission->getLastModified()|date_format:$dateFormatShort}
	</li>
{if $enableComments}
	<li>
		<header>{translate key="comments.readerComments"}</header>
		{translate key=$submission->getCommentsStatusString()}
		<form action="{url op="updateCommentsStatus" path=$submission->getPaperId()}" method="post">{translate key="submission.changeComments"} <select name="commentsStatus" size="1" class="selectMenu">{html_options_translate options=$commentsStatusOptions selected=$submission->getCommentsStatus()}</select> <input type="submit" value="{translate key="common.record"}" class="button" /></form>
	</li>
{/if}
</div>
