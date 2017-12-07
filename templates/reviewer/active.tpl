{**
 * active.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show reviewer's active submissions.
 *
 * $Id$
 *}

<div id="submissions">
<table class="listing" width="100%">
	<tr><td colspan="7" class="headseparator">&nbsp;</td></tr>
	<tr class="heading" valign="bottom">
		<td width="7%">{translate key="common.id"}</td>
		<td width="7%"><span class="disabled">MM-DD</span><br />{translate key="common.assigned"}</td>
		<td width="58%">{translate key="paper.title"}</td>
		<td width="8%">{translate key="submission.due"}</td>
		<td width="10%">{translate key="submissions.reviewType"}</td>
    <td width="10%">{translate key="submission.fileOkayed"}</td>
	</tr>
	<tr><td colspan="7" class="headseparator">&nbsp;</td></tr>

{iterate from=submissions item=submission}
	{assign var="paperId" value=$submission->getPaperId()}
	{assign var="reviewId" value=$submission->getReviewId()}

	<tr valign="top">
		<td>{$paperId|escape}</td>
		<td>{$submission->getDateNotified()|date_format:$dateFormatTrunc}</td>
		<td><a href="{url op="submission" path=$reviewId}" class="action">{$submission->getLocalizedTitle()|strip_tags|truncate:60:"..."}</a></td>
		<td class="nowrap">{$submission->getDateDue()|date_format:$dateFormatTrunc}</td>
		{if $submission->getReviewMode() == REVIEW_MODE_BOTH_SEQUENTIAL}
			<td>
				{if $submission->getStage()==REVIEW_STAGE_ABSTRACT}{* Reviewing abstract *}
					{translate key="submission.abstract"}
				{else}
					{translate key="submission.paper"}
				{/if}
			</td>
		{/if}
    <td style="vertical-align: middle;">
      {assign var=reviewFile value=$submission->getReviewFile()}
      {if $reviewFile}
        {if $reviewFile->getChecked() == 1}
          <span style="color:#0b9e3f;">{translate key="submission.fileAccepted""}</span>
        {else}
          <span style="color:#e85a09;">{translate key="submission.filePending"}</span>
        {/if}
      {else}
        <span style="color:#a5a3a5;">{translate key="submission.noFile"}</span>
      {/if}
    </td>
	</tr>
	<tr>
		<td colspan="7" class="{if $submissions->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $submissions->wasEmpty()}
<tr>
		<td colspan="7" class="nodata">{translate key="submissions.noSubmissions"}</td>
	</tr>
	<tr>
		<td colspan="7" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td colspan="3" align="left">{page_info iterator=$submissions}</td>
		<td colspan="4" align="right">{page_links anchor="submissions" name="submissions" iterator=$submissions}</td>
	</tr>
{/if}
</table>
</div>
