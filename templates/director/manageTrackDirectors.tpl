{**
 * manageTrackDirectors.tpl
 *
 * Copyright (c) 2017 Dominik Bláha
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Shows listing of conference trackDirectors.
 *
 * $Id$
 *}
 {strip}
 {assign var="pageTitle" value="director.manageTrackDirectors"}
 {assign var="pageCrumbTitle" value="director.manageTrackDirectors"}
 {include file="common/header.tpl"}
 {/strip}

<h3>{translate key="director.paper.selectDirector" roleName=$roleName|translate}</h3>

<form name="submit" method="post" action="{url op="manageTrackDirectors"}">
	<select name="searchField" size="1" class="selectMenu">
		{html_options_translate options=$fieldOptions selected=$searchField}
	</select>
	<select name="searchMatch" size="1" class="selectMenu">
		<option value="contains"{if $searchMatch == 'contains'} selected="selected"{/if}>{translate key="form.contains"}</option>
		<option value="is"{if $searchMatch == 'is'} selected="selected"{/if}>{translate key="form.is"}</option>
		<option value="startsWith"{if $searchMatch == 'startsWith'} selected="selected"{/if}>{translate key="form.startsWith"}</option>
	</select>
	<input type="text" name="search" class="textField" value="{$search|escape}" />&nbsp;<input type="submit" value="{translate key="common.search"}" class="button" />
</form>

<p>{foreach from=$alphaList item=letter}<a href="{url op="manageTrackDirectors" searchInitial=$letter}">{if $letter == $searchInitial}<strong>{$letter|escape}</strong>{else}{$letter|escape}{/if}</a> {/foreach}<a href="{url op="manageTrackDirectors"}">{if $searchInitial==''}<strong>{translate key="common.all"}</strong>{else}{translate key="common.all"}{/if}</a></p>

<div id="trackDirectors">
  <table width="100%" class="listing sortable">
	<thead>
  <tr>
  	<td width="60%">{translate key="user.name"}</td>
  	<td width="20%">{translate key="submissions.completed"}</td>
  	<td width="20%">{translate key="submissions.active"}</td>
  </tr>
	</thead>
  {iterate from=directors item=director}
  {assign var=directorId value=$director->getId()}
  <tr valign="top">
  	<td><a class="action" href="{url op="userProfile" path=$directorId}">{$director->getFullName()|escape}</a></td>
  	<td>
  		{if $directorStatistics[$directorId] && $directorStatistics[$directorId].complete}
  			{$directorStatistics[$directorId].complete}
  		{else}
  			0
  		{/if}
  	</td>
  	<td>
  		{if $directorStatistics[$directorId] && $directorStatistics[$directorId].incomplete}
  			{$directorStatistics[$directorId].incomplete}
  		{else}
  			0
  		{/if}
  	</td>
  </tr>
  {/iterate}
  {if $directors->wasEmpty()}
  <tr>
  <td colspan="3" class="nodata">{translate key="manager.people.noneEnrolled"}</td>
  </tr>
  {/if}
  </table>
	<p>
	{page_info iterator=$directors}
	{page_links anchor="directors" name="directors" iterator=$directors searchInitial=$searchInitial searchField=$searchField searchMatch=$searchMatch search=$search dateFromDay=$dateFromDay dateFromYear=$dateFromYear dateFromMonth=$dateFromMonth dateToDay=$dateToDay dateToYear=$dateToYear dateToMonth=$dateToMonth paperId=$paperId}
	</p>
</div>
{include file="common/footer.tpl"}
