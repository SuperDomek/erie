{**
 * block.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site sidebar menu -- "Notification" block.
 *
 * $Id$
 *}
{if $currentConference}
  {if $isUserLoggedIn}
  <div class="block" id="notification">
  	<span class="blockTitle">{translate key="notification.notifications"}</span>
  	<ul>
  			<li>
          <a href="{url page="notification"}">{translate key="common.view"} ({$unreadNotifications|escape})</a>
				</li>
  			<!--<li><a href="{url page="notification" op="settings"}">{translate key="common.manage"}</a></li>-->
  	</ul>
  </div>
	{/if}
{/if}
