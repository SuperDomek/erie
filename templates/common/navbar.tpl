{**
 * navbar.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Navigation Bar
 *
 *}
<div id="navbar">
	<ul class="menu">
		<!--<li><a href="{url context=$homeContext op="index"}">{translate key="navigation.home"}</a></li>-->
		{if $isUserLoggedIn}
      {* Hardcoded link to home page of the user in current conference *}
			<li><a href="{url conference="erie" schedConf="2018" page="user"}">{translate key="navigation.home"}</a></li>
      <li><a href="http://erie.pef.czu.cz" target="_blank">{translate key="navigation.about"}</a></li>
      <li><a href="http://erie.pef.czu.cz/" target="_blank">{translate key="navigation.infoForAuthors"}</a></li>
      <li><a href="{url page="login" op="signOut"}">{translate key="user.logOut"}</a></li>
		{else}
      <li><a href="http://erie.pef.czu.cz" target="_blank">{translate key="navigation.about"}</a></li>
      <li><a href="http://erie.pef.czu.cz" target="_blank">{translate key="navigation.infoForAuthors"}</a></li>
			<li><a href="{url page="login"}">{translate key="navigation.login"}</a></li>
			<li><a href="{url page="user" op="account"}">{translate key="navigation.account"}</a></li>
		{/if}{* $isUserLoggedIn *}

		<!--<li><a href="{url page="search"}">{translate key="navigation.search"}</a></li>-->

		{if $currentConference}
			<!--{if $currentSchedConfsExist}<li><a href="{url schedConf="index" page="schedConfs" op="current"}">{translate key="navigation.current"}</a></li>{/if}
			{if $archivedSchedConfsExist}<li><a href="{url schedConf="index" page="schedConfs" op="archive"}">{translate key="navigation.archive"}</a></li>{/if}-->
			<!--{if $enableAnnouncements}
				<li><a href="{url page="announcement"}">{translate key="announcement.announcements"}</a></li>
			{/if}{* $enableAnnouncements *}-->

			{call_hook name="Templates::Common::Header::Navbar::CurrentConference"}
		{/if}{* $currentConference *}

		{foreach from=$navMenuItems item=navItem}
			{if $navItem.url != '' && $navItem.name != ''}
				<li><a href="{if $navItem.isAbsolute}{$navItem.url|escape}{else}{$navItem.url|escape}{/if}">{if $navItem.isLiteral}{$navItem.name|escape}{else}{translate key=$navItem.name}{/if}</a></li>
			{/if}
		{/foreach}
	</ul>
</div>
