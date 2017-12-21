{**
 * active.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the details of active submissions.
 *
 * $Id$
 *}
<div id="submissions">
<table class="listing sortable" width="100%">
<thead>
	<tr>
		<td width="5%">{translate key="common.id"}</td>
		<td width="7%"><span class="disabled">MM-DD</span><br />{translate key="submissions.submit"}</td>
		<td width="5%">{translate key="submissions.track"}</td>
		<td width="25%">{translate key="paper.authors"}</td>
		<td width="35%">{translate key="paper.title"}</td>
		<td width="23%" align="right">{translate key="common.status"}</td>
	</tr>
</thead>
<tbody>
{iterate from=submissions item=submission}
	{assign var="paperId" value=$submission->getPaperId()}
	{assign var="currentStage" value=$submission->getCurrentStage()}
	{assign var="submissionProgress" value=$submission->getSubmissionProgress()}
	{assign var="status" value=$submission->getSubmissionStatus()}

	<tr valign="top">
		<td>{$paperId|escape}</td>
		<td>{if $submission->getDateSubmitted()}{$submission->getDateSubmitted()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>
		<td>{$submission->getTrackAbbrev()|escape}</td>
		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
		{if $submissionProgress == 0}
			<td><a href="{url op="submissionReview" path=$paperId|to_array}" class="action">{if $submission->getLocalizedTitle()}{$submission->getLocalizedTitle()|strip_tags|truncate:60:"..."}{else}{translate key="common.untitled"}{/if}</a></td>
			<td align="right">
				{if $status == STATUS_QUEUED_UNASSIGNED}{translate key="submissions.queuedUnassigned"}
				{elseif $status == STATUS_QUEUED_REVIEW}
					{assign var=decision value=$submission->getMostRecentDecision()}
					{if $currentStage>=REVIEW_STAGE_PRESENTATION}
						<a href="{url op="submissionReview" path=$paperId|to_array}" class="action">
							{if $submission->getAuthorFileRevisions($submission->getCurrentStage())}
            		<span>{translate key="author.submissions.queuedPaperReviewRevisions.uploaded"}</span>
              {elseif $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS ||
              $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS ||
              $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS}
                {translate key="author.submissions.queuedPaperReviewRevisions"}
							{else}
								{translate key="submissions.queuedPaperReview"}
							{/if}
						</a>
					{else}
						<a href="{url op="submissionReview" path=$paperId|to_array}" class="action">
							{if $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS ||
              $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS ||
              $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS}
                {translate key="author.submissions.queuedAbstractReviewRevisions"}
							{else}{translate key="submissions.queuedAbstractReview"}
							{/if}
						</a>
					{/if}
				{elseif $status == STATUS_QUEUED_EDITING}
					<a href="{url op="submissionReview" path=$paperId|to_array}" class="action">{translate key="submissions.queuedEditing"}</a>
				{/if}
			</td>
		{else}
			{url|assign:"submitUrl" op="submit" path=$submission->getSubmissionProgress() paperId=$paperId}
			<td><a href="{$submitUrl}" class="action">{if $submission->getLocalizedTitle()}{$submission->getLocalizedTitle()|strip_tags|truncate:60:"..."}{else}{translate key="common.untitled"}{/if}</a></td>
			<td align="right">
				{if $currentStage == REVIEW_STAGE_ABSTRACT || ($currentStage == REVIEW_STAGE_PRESENTATION && $submissionProgress < 2)}
					{translate key="submissions.incomplete"}
					<br />
					<a href="{url op="deleteSubmission" path=$paperId}" class="action" onclick="return confirm('{translate|escape:"jsparam" key="author.submissions.confirmDelete"}')">
						{translate key="common.delete"}
					</a>
				{else}
					<a class="action" href="{$submitUrl}">{translate key="submissions.pendingPresentation"}</a>
				{/if}
			</td>
		{/if}
	</tr>

{/iterate}
{if $submissions->wasEmpty()}
	<tr>
		<td colspan="6" class="nodata">{translate key="submissions.noSubmissions"}</td>
	</tr>
{/if}
</tbody>
</table>
<p>
{page_info iterator=$submissions}
{page_links anchor="submissions" name="submissions" iterator=$submissions sort=$sort sortDirection=$sortDirection}
</p>
</div>
