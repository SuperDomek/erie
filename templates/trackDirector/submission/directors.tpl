{**
 * directors.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the submission directors table.
 *
 * $Id$
 *}
<div id="directors">
<div class="revMenu">
	<h3>{translate key="user.role.trackDirectors"}</h3>
	{if $isDirector}
		<a href="{url page="director" op="assignDirector" path="trackDirector" paperId=$submission->getPaperId()}">
			<button class="button">{translate key="director.paper.assignTrackDirector"}</button></a>
		<a href="{url page="director" op="assignDirector" path="director" paperId=$submission->getPaperId()}">
			<button class="button">{translate key="director.paper.assignDirector"}</button></a>
		{if !$selfAssigned}
			<a href="{url page="director" op="assignDirector" path="director" directorId=$userId paperId=$submission->getPaperId()}">
				<button class="button">{translate key="common.addSelf"}</button>
			</a>
		{/if}
	{/if}
</div>
<div class="tbl-container">
<table class="listing sortable">
	<thead>
	<tr>
		<td width="20%">{translate key="user.role"}</td>
		<td>{translate key="user.name"}</td>
		<td width="{if $isDirector}20%{else}25%{/if}">{translate key="submission.request"}</td>
		{if $isDirector}<td width="10%">{translate key="common.action"}</td>{/if}
	</tr>
	</thead>
	<tbody>
	{assign var=editAssignments value=$submission->getEditAssignments()}
	{foreach from=$editAssignments item=editAssignment name=editAssignments}
	{if $editAssignment->getDirectorId() == $userId}
		{assign var=selfAssigned value=1}
	{/if}
		<tr valign="top">
			<td>{if $editAssignment->getIsDirector()}{translate key="user.role.director"}{else}{translate key="user.role.trackDirector"}{/if}</td>
			<td>
				{url|assign:"url" page="trackdirector" op="userProfile" path=$editAssignment->getDirectorId()}
				<a href="{$url}" alt="{translate key="user.profile.publicProfile" user=$editAssignment->getDirectorFullName()|escape}" title="{translate key="user.profile.publicProfile" user=$editAssignment->getDirectorFullName()|escape}">{$editAssignment->getDirectorFullName()|escape}</a> {*icon name="mail" url=$url*}
			</td>
			<td>{if $editAssignment->getDateNotified()}{$editAssignment->getDateNotified()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
			{if $isDirector}
				<td>
					<a href="{url page="director" op="deleteEditAssignment" path=$editAssignment->getEditId()}" class="action">
						<button class="negative button">{translate key="common.delete"}</button>
					</a>
				</td>
			{/if}
		</tr>
	{foreachelse}
		<tr>
			<td colspan="{if $isDirector}4{else}3{/if}" class="nodata">{translate key="common.noneAssigned"}</td>
		</tr>
	{/foreach}
</tbody>
</table>
</div>


</div>
