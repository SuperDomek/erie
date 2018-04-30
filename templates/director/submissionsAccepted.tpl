{**
 * submissionsArchives.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show listing of submission archives.
 *
 * $Id$
 *}
<div id="submissions">

<table width="100%" class="listing sortable">
<thead>
	<tr>
		<td width="3%">{translate key="common.id"}</td>
		<td width="4%">{translate key="submissions.track"}</td>
		<td width="19%">{translate key="paper.authors"}</td>
		<td width="48%">{translate key="paper.title"}</td>
		<!--<td width="8%">{translate key="common.order"}</td>-->
		<td width="8%">{translate key="common.country"}</td>
		<td width="4%">{translate key="paper.editing"}</td>
		<td width="4%">{translate key="paper.pages"}</td>
		<td width="10%" align="right">{translate key="common.status"}</td>
	</tr>
</thead>
<tbody>
	{iterate from=submissions item=submission}
	<tr>
		{assign var=lastTrackId value=$submission->getTrackId()}
	</tr>
	{assign var="user" value=$submission->getUser()}
	{assign var="paperId" value=$submission->getPaperId()}
	<input type="hidden" name="paperIds[]" value="{$paperId|escape}" />
	<tr valign="top">
		<td>{$paperId|escape}</td>
		<td>{$submission->getTrackAbbrev()|escape}</td>
		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
		<td><a href="{url op="submissionReview" path=$paperId}" class="action">{$submission->getLocalizedTitle()|strip_tags|truncate:60:"..."|default:"&mdash;"}</a></td>
		<!--<td>
			<a href="{url op="movePaper" d=u paperId=$submission->getPaperId()}" class="plain">&uarr;</a>
			<a href="{url op="movePaper" d=d paperId=$submission->getPaperId()}" class="plain">&darr;</a>
		</td>-->
		<td>
			{$user->getCountry()}
		</td>
		<td>
			{if $submission->getEditing()}{translate key="common.yes"}{else}{translate key="common.no"}{/if}
		</td>
		<td>
			{$submission->getPages()|escape}
		</td>
		<td align="right">
			{assign var="status" value=$submission->getStatus()}
			{if $status == STATUS_ARCHIVED}
				{translate key="submissions.archived"}&nbsp;&nbsp;<a href="{url op="deleteSubmission" path=$paperId}" onclick="return confirm('{translate|escape:"jsparam" key="director.submissionArchive.confirmDelete"}')" class="action">{translate key="common.delete"}</a>
			{elseif $status == STATUS_PUBLISHED}
				{translate key="submissions.published"}
			{elseif $status == STATUS_DECLINED}
				{translate key="submissions.declined"}&nbsp;&nbsp;<a href="{url op="deleteSubmission" path=$paperId}" onclick="return confirm('{translate|escape:"jsparam" key="director.submissionArchive.confirmDelete"}')" class="action">{translate key="common.delete"}</a>
			{/if}
		</td>
	</tr>
{/iterate}
{if $submissions->wasEmpty()}
	<tr>
		<td colspan="7" class="nodata">{translate key="submissions.noSubmissions"}</td>
	</tr>
{/if}
</tbody>
</table>
<p>
{page_info iterator=$submissions}
{page_links anchor="submissions" name="submissions" iterator=$submissions searchField=$searchField searchMatch=$searchMatch search=$search track=$track sort=$sort sortDirection=$sortDirection}
</p>
</div>
