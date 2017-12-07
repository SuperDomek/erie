{**
 * selectRegistrationType.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Attendee page to select registration type.
 *
 * $Id$
 *}
{assign var="pageTitle" value="schedConf.registration"}
{include file="common/header.tpl"}

<form action="{url op="registration"}" method="post">
<div id="registrationType">

  <table class="listing" width="100%">
    <tr valign="top">
    	<td class="label">{fieldLabel name="typeId" required="true" key="manager.registration.form.typeId"}</td>
    	<td class="value"><select name="registrationTypeId" id="typeId" class="selectMenu">
    		{iterate from=registrationTypes item=registrationType}
    		<option value="{$registrationType->getTypeId()}"{if $typeId == $registrationType->getTypeId()} selected="selected"{/if}>{$registrationType->getSummaryString()|escape}</option>
    		{/iterate}
    	</select></td>
    </tr>
    <tr>
  		<td colspan="2" class="endseparator">&nbsp;</td>
  	</tr>
  </table>
</div>
{if $currentSchedConf->getSetting('registrationName')}

<div id="registrationContact">
<h3>{translate key="manager.registrationPolicies.registrationContact"}</h3>

<table class="data" width="100%">
	<tr valign="top">
		<td width="20%" class="label">{translate key="user.name"}</td>
		<td width="80%" class="value">{$currentSchedConf->getSetting('registrationName')|escape}</td>
	</tr>
	{if $currentSchedConf->getSetting('registrationEmail')}<tr valign="top">
		<td class="label">{translate key="about.contact.email"}</td>
		<td class="value">{mailto address=$currentSchedConf->getSetting('registrationEmail')|escape encode="hex"}</td>
	</tr>{/if}
	{if $currentSchedConf->getSetting('registrationPhone')}<tr valign="top">
		<td class="label">{translate key="about.contact.phone"}</td>
		<td class="value">{$currentSchedConf->getSetting('registrationPhone')|escape}</td>
	</tr>{/if}
	{if $currentSchedConf->getSetting('registrationFax')}<tr valign="top">
		<td class="label">{translate key="about.contact.fax"}</td>
		<td class="value">{$currentSchedConf->getSetting('registrationFax')|escape}</td>
	</tr>{/if}
	{if $currentSchedConf->getSetting('registrationMailingAddress')}<tr valign="top">
		<td class="label">{translate key="common.mailingAddress"}</td>
		<td class="value">{$currentSchedConf->getSetting('registrationMailingAddress')|nl2br}</td>
	</tr>{/if}
</table>
</div>
{/if}{* if displaying reg manager info *}

{if strtotime($registrationType->getOpeningDate()) < time() && strtotime($registrationType->getClosingDate()) > time()}
  {assign var="registrationMethodAvailable" value=1}
{/if}

<p><input type="submit" value="{translate key="schedConf.registration.register"}" {if !$registrationMethodAvailable}disabled="disabled" class="button" {else}class="button defaultButton" {/if}/></p>

</form>

{include file="common/footer.tpl"}
