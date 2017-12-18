{**
 * submissionsArchives.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show track director's submission archive.
 *
 * $Id$
 *}
<div id="submissions">
<table width="100%" class="listing sortable">
<thead>
	<tr>
		<td width="3%">{translate key="common.id"}</td>
		<td width="4%">{translate key="submissions.track"}</td>
		<!--<td width="4%">{sort_search key="paper.sessionType" sort="sessionType"}</td>-->
		<td width="15%">{translate key="paper.authors"}</td>
		<td>{translate key="paper.title"}</td>
		<td width="10%" align="right">{translate key="common.status"}</td>
	</tr>
</thead>
<tbody>
{iterate from=submissions item=submission}
	{assign var="paperId" value=$submission->getPaperId()}
	<input type="hidden" name="paperIds[]" value="{$paperId|escape}" />
	<tr valign="top">
		<td>{$submission->getPaperId()}</td>
		<td>{$submission->getTrackAbbrev()|escape}</td>
		<!--<td>
			{assign var="sessionTypeId" value=$submission->getData('sessionType')}
			{if $sessionTypeId}
				{assign var="sessionType" value=$sessionTypes.$sessionTypeId}
				{$sessionType->getLocalizedName()|escape}
			{/if}
		</td>-->
		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
		<td><a href="{url op="submissionReview" path=$paperId}" class="action">{$submission->getLocalizedTitle()|strip_tags|truncate:60:"..."|default:"&mdash;"}</a></td>
		<td align="right">
			{assign var="status" value=$submission->getStatus()}
			{if $status == STATUS_ARCHIVED}
				{translate key="submissions.archived"}
			{elseif $status == STATUS_PUBLISHED}
				{translate key="submissions.published"}
			{elseif $status == STATUS_DECLINED}
				{translate key="submissions.declined"}
			{/if}
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
