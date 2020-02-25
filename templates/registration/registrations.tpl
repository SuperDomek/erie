{**
 * registrations.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of registrations in scheduled conference management.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="manager.registration"}
{assign var="pageId" value="manager.registration"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
{literal}
<!--
function sortSearch(heading, direction) {
	document.submit.sort.value = heading;
	document.submit.sortDirection.value = direction;
	document.submit.submit();
}
// -->
{/literal}
</script>

<ul class="menu">
	<li class="current"><a href="{url op="registration" clearPageContext=1}">{translate key="manager.registration"}</a></li>
	<li><a href="{url op="registrationTypes" clearPageContext=1}">{translate key="manager.registrationTypes"}</a></li>
	<li><a href="{url op="registrationPolicies"}">{translate key="manager.registrationPolicies"}</a></li>
	<li><a href="{url op="registrationOptions"}">{translate key="manager.registrationOptions"}</a></li>
</ul>
<p>
{translate key="manager.registration.info"}
</p>
<div id="registrations">
<pre>
{*$papers|@print_r*}
</pre>
<table width="100%" class="listing sortable">
<thead>
	<tr>
		<td width="20%">{translate key="manager.registration.user"}</td>
    	<td width="5%">{translate key="common.specSymbol"}</td>
		<td width="20%">{translate key="manager.registration.registrationType"}</td>
		<td width="25%">{translate key="paper.title"}</td>
		<td width="9">{translate key="paper.sessionType"}</td>
		<td width="9%">{translate key="manager.registration.dateRegistered"}</td>
		<td width="12%">{translate key="common.action"}</td>
	</tr>
</thead>
<tbody>
{iterate from=registrations item=registration}
	{assign var="paperId" value=$registration->getSubmissionId()}
	{assign var="paper" value=$papers.$paperId}
	{assign var="registrationId" value=$registration->getId()}
	{assign var="registrationTypeId" value=$registration->getRegistrationTypeName()|escape}
	{assign var="paperTypeId" value=$registration->getSubmissionType()}
	<tr valign="top">
		<td>{$registration->getUserFullName()|escape}</td>
    	<td>{$registration->getUserId()|escape}</td>
		<td>
			{if empty($registrationTypeId)}
				{translate key="manager.registration.noRegistration"}
			{else}
				{$registration->getRegistrationTypeName()|escape}
			{/if}
		</td>
		<td>
			{if empty($paperId)}
				{translate key="common.none"}
			{else}
				<a href="{url page="trackDirector" op="submissionReview" path=$paperId}" class="action">{$paper->getLocalizedTitle()|strip_tags|truncate:25:"..."}</a>
			{/if}
		</td>
		<td>
			{if empty($paperTypeId)}
				{translate key="common.none"}
			{else}
				{assign var="sessionType" value=$sessionTypes.$paperTypeId}
				{$sessionType|escape}
			{/if}
		</td>
		<td>{$registration->getDateRegistered()|date_format:$dateFormatShort}</td>
		<td>
			{if empty($registrationId)}
			<!--Create Button-->
			{else}
				<a href="{url op="editRegistration" path=$registration->getId()}" class="action"><button class="button">{translate key="common.edit"}</button></a>&nbsp;|&nbsp;<a href="{url op="deleteRegistration" path=$registration->getId()}" onclick="return confirm('{translate|escape:"jsparam" key="manager.registration.confirmDelete"}')" class="action"><button class="button">{translate key="common.delete"}</button></a>
			{/if}		
		</td>
	</tr>
{/iterate}
{if $registrations->wasEmpty()}
	<tr>
		<td colspan="6" class="nodata">{translate key="manager.registration.noneCreated"}</td>
	</tr>
{/if}
</tbody>
</table>
<p>
{page_info iterator=$registrations}
{page_links anchor="registrations" name="registrations" iterator=$registrations searchField=$searchField searchMatch=$searchMatch search=$search dateSearchField=$dateSearchField dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth sort=$sort sortDirection=$sortDirection}
</p>

<a href="{url op="selectRegistrant"}" class="action">{translate key="manager.registration.create"}</a>
</div>
{include file="common/footer.tpl"}
