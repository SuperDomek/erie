{**
 * submissionsInReview.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show director's submissions in review.
 *
 * $Id$
 *}
<div id="submissions">
<table width="100%" class="listing sortable">
	<thead>
	<tr>
		<td width="4%">{translate key="common.id"}</td>
		<td width="7%">{translate key="submissions.submitted"}</td>
		<td width="15%">{translate key="paper.authors"}</td>
		<td width="34%">{translate key="paper.title"}</td>
		<td width="25%" class="sorttable_nosort">
			<center style="border-bottom: 1px solid gray;margin-bottom: 3px;">{translate key="submission.peerReview"}</center>
			<table width="100%" class="nested">
				<tr valign="top">
					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submissions.reviewStage"}</td>
					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submission.ask"}</td>
					<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{translate key="submission.done"}</td>
				</tr>
			</table>
		</td>
		<td width="5%">{translate key="submission.decision"}</td>
    <td width="6%">{translate key="submission.fileOkayed"}</td>
		<td width="10%">{translate key="user.role.trackDirectors"}</td>
	</tr>
	</thead>
	<tbody>
	{iterate from=submissions item=submission}
  {assign var=paperId value=$submission->getPaperId()}
	<tr>
		<td>{$submission->getPaperId()}</td>
		<td>{$submission->getDateSubmitted()|date_format:$dateFormatTrunc}</td>
		<!--<td>
			{assign var="sessionTypeId" value=$submission->getData('sessionType')}
			{if $sessionTypeId}
				{assign var="sessionType" value=$sessionTypes.$sessionTypeId}
				{$sessionType->getLocalizedName()|escape}
			{/if}
		</td>-->
		<td>{$submission->getAuthorString(true)|truncate:30:"..."|escape}</td>
		<td><a href="{url op="submissionReview" path=$submission->getPaperId()|to_array:$submission->getCurrentStage()}" class="action">{$submission->getLocalizedTitle()|strip_tags|truncate:40:"..."|default:"&mdash;"}</a></td>
		<td>
		<table width="100%">
			{foreach from=$submission->getReviewAssignments() item=reviewAssignments key=assStage}
				{if $assStage != REVIEW_STAGE_ABSTRACT}
					{foreach from=$reviewAssignments item=assignment name=assignmentList}
						{if not $assignment->getCancelled() and not $assignment->getDeclined()}
						<tr valign="top">
							<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getStage() == REVIEW_STAGE_ABSTRACT}{translate key="submission.abstract"}{else}{translate key="submission.paper"} {$assignment->getStage()-1}{/if}</td>
							<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateNotified()}{$assignment->getDateNotified()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>
							<!--<td width="25%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateCompleted() || !$assignment->getDateConfirmed()}&mdash;{else}{$assignment->getWeeksDue()|default:"&mdash;"}{/if}</td>-->
							<td width="33%" style="padding: 0 4px 0 0; font-size: 1.0em">{if $assignment->getDateCompleted()}{$assignment->getDateCompleted()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>
						</tr>
						{/if}
					{/foreach}
				{/if}
			{/foreach}
			</table>
		</td>
		<td >
			{* don't show abstract decision
      {assign var="decisionsAbstract" value=$submission->getDecisions()|@reset}
      {assign var="decisionAbstract" value=$decisionsAbstract|@end}
      {if $decisionAbstract.decision == SUBMISSION_DIRECTOR_DECISION_INVITE}
        <span style="color:#0b9e3f;">ACC</span>
      {elseif $decisionAbstract.decision == SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS}
        <span style="color:#ea5b0d;">REV</span>
      {elseif $decisionAbstract.decision == SUBMISSION_DIRECTOR_DECISION_DECLINE}
        <span style="color:#e85a09;">DEC</span>
      {else}
        <span style="color:#a5a3a5;">&mdash;</span>
      {/if}
      <br />
			*}
      {assign var="decisions" value=$submission->getDecisions()|@end}
      {assign var="decision" value=$decisions|@end}
      {if $decision.decision == SUBMISSION_DIRECTOR_DECISION_ACCEPT}
        <span style="color:#0b9e3f;">ACC</span>
      {elseif $decision.decision == SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS ||
      $decision.decision == SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS}
        <span style="color:#ea5b0d;">REV</span>
      {elseif $decisionAbstract.decision == SUBMISSION_DIRECTOR_DECISION_DECLINE}
        <span style="color:#e85a09;">DEC</span>
      {else}
        <span style="color:#a5a3a5;"></span>
      {/if}
		</td>
    <td style="vertical-align: middle;">
      {if $paperId|array_key_exists:$reviewFiles}
        {if $reviewFiles[$paperId] == 1}
          <span style="color:#0b9e3f;">{translate key="submission.fileAccepted"}</span>
        {else}
          <span style="color:#e85a09;">{translate key="submission.filePending"}</span>
        {/if}
      {else}
        <span style="color:#a5a3a5;">{translate key="submission.noFile"}</span>
      {/if}
    </td>
		<td>{$submission->getTrackDirectorString(true)|truncate:30:"..."|escape}</td>
	</tr>
{/iterate}
{if $submissions->wasEmpty()}
	<tr>
		<td colspan="8" class="nodata">{translate key="submissions.noSubmissions"}</td>
	</tr>
{/if}
	</tbody>
</table>
<p>
{page_info iterator=$submissions}
{page_links anchor="submissions" name="submissions" iterator=$submissions searchField=$searchField searchMatch=$searchMatch search=$search track=$track sort=$sort sortDirection=$sortDirection}
</p>
</div>
