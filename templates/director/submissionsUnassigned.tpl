{**
 * submissionsUnassigned.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show listing of unassigned submissions.
 *
 * $Id$
 *}
<div id="submissions">
<table width="100%" class="listing sortable">
	<thead>
	<tr>
		<td width="5%">{translate key="common.id"}</td>
		<td width="5%"><span class="disabled">MM-DD</span><br />{translate key="submissions.submit"}</td>
		<td width="5%">{translate key="submissions.track"}</td>
		<!--<td width="5%">{sort_search key="paper.sessionType" sort="sessionType"}</td>-->
		<td width="20%">{translate key="paper.authors"}</td>
		<td width="40%">{translate key="paper.title"}</td>
    <td width="15%">{translate key="paper.manage"}
	</tr>
	</thead>
	<tbody>
	{iterate from=submissions item=submission}
	<tr valign="top">
		<td>{$submission->getPaperId()}</td>
		<td>{$submission->getDateSubmitted()|date_format:$dateFormatTrunc}</td>
		<td>{$submission->getTrackAbbrev()|escape}</td>
		<!--<td>
			{assign var="sessionTypeId" value=$submission->getData('sessionType')}
			{if $sessionTypeId}
				{assign var="sessionType" value=$sessionTypes.$sessionTypeId}
				{$sessionType->getLocalizedName()|escape}
			{/if}
		</td>-->
		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
		{translate|assign:"untitledPaper" key="common.untitled"}
		{* EDIT: Hardcoded link to first review round - abstract*}
		<td><a href="{url op="submissionReview" path=$submission->getPaperId()|to_array:1}" class="action">{$submission->getLocalizedTitle()|default:$untitledPaper|strip_tags|truncate:60:"..."|default:"&mdash;"}</a>
			{if $submissionProgress != 0 && ($currentStage == REVIEW_STAGE_ABSTRACT || ($currentStage == REVIEW_STAGE_PRESENTATION && $submissionProgress < 3))}
				(<a href="{url op="deleteSubmission" path=$paperId}" class="action" onclick="return confirm('{translate|escape:"jsparam" key="author.submissions.confirmDelete"}')">{translate key="common.delete"}</a>)
			{/if}
		</td>
    <td>
      <a href="{url page="director" op="assignDirector" path="trackDirector" paperId=$submission->getPaperId()}">{translate key="director.paper.assignTrackDirector"}</a>
    </td>
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
{page_links anchor="submissions" name="submissions" iterator=$submissions searchField=$searchField searchMatch=$searchMatch search=$search track=$track sort=$sort sortDirection=$sortDirection}
</p>
</div>
