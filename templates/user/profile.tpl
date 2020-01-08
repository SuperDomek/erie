{**
 * profile.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User profile form.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="user.profile.editProfile"}
{url|assign:"url" op="profile"}
{include file="common/header.tpl"}
{/strip}

{literal}
<script type="text/javascript">
<!--
// Global variable for addresses
var addresses = null;
// Global variable for Company Reg Numbers
var companyIds = {/literal}{$companyIds|@json_encode}{literal};
// GLobal variable for VAT Reg. Numbers
var vatRegNos = {/literal}{$VATRegNos|@json_encode}{literal};
// Global variable for affiliation suffixes
var suffixes = {/literal}{$suffixes|@json_encode}{literal};
// Global variable for english affiliations
var affiliationsEn = {/literal}{$affiliationsEn|@json_encode}{literal};
// Processes addresses from smarty into a javascript variable

function initVars() {
  addresses = {{/literal}
    {foreach from=$addresses item=address key=key name=addressloop}
          "{$key}":"{$address}"
    {if !$smarty.foreach.addressloop.last},{/if}
    {/foreach}{literal}
  };
}

// Sets up address, affiliation, CompanyId and VATRegNo
// @selected Object Object with the selected option
function setInfo(selected){
  
  if (selected === null) {
    document.getElementById("mailingAddress").value = "";
    document.getElementById("affil_text").value = "";
    document.getElementById("companyId").value = "";
    document.getElementById("VATRegNo").value = "";
    //tinyMCE.get('mailingAddress').setContent("");
  }
  else{
		var facultyKey = selected.parentNode.label; //PEF
		var departmentKey = selected.value; //KII
    //var faculty = (selected.label).concat("\n");
		//alert(affiliationsEn[facultyKey][departmentKey] + suffixes[departmentKey]);
    var affil_text = selected.text;
    //document.getElementById("mailingAddress").value = faculty.concat(addresses[facultyKey]);
    document.getElementById("mailingAddress").value = addresses[facultyKey];
		document.getElementById("billingAddress").value = addresses[facultyKey];
    document.getElementById("affil_text").value = affiliationsEn[facultyKey][departmentKey] + suffixes[departmentKey];
    document.getElementById("companyId").value = companyIds["CULS"];
    document.getElementById("VATRegNo").value = vatRegNos["CULS"];
    //tinyMCE.get('mailingAddress').setContent(addresses[key]);
  }
}

// shows affiliation box if required; sets up address if affiliation set up
function showAffilBox(sel) {
  // init global arrays if not already
  if (addresses === null) initVars();

  var selected = sel.options[sel.selectedIndex];
	if(selected.value == "else"){ //custom affil
    document.getElementById("affil_box").style.display = "table-row";
    setInfo(null);
  }
  else if (selected.value != ""){ //selected affil
    document.getElementById("affil_box").style.display = "none";
    setInfo(selected);
  }
  else { // blank affil
    document.getElementById("affil_box").style.display = "none";
  }
}
// -->
</script>
{/literal}

<form name="profile" method="post" action="{url op="saveProfile"}" enctype="multipart/form-data">

{include file="common/formErrors.tpl"}

<table class="data" width="100%">
{if count($formLocales) > 1}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" required="true" key="common.language"}</td>
		<td width="80%" class="value">
			{url|assign:"userProfileUrl" page="user" op="profile" escape=false}
			{form_language_chooser form="profile" url=$userProfileUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
<tr valign="top">
	<td width="20%" class="label">{fieldLabel suppressId="true" name="username" key="user.username"}</td>
	<td width="80%" class="value">{$username|escape}</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="salutation" key="user.salutation"}</td>
	<td class="value"><input type="text" name="salutation" id="salutation" value="{$salutation|escape}" size="20" maxlength="40" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="firstName" required="true" key="user.firstName"}</td>
	<td class="value"><input type="text" name="firstName" id="firstName" value="{$firstName|escape}" size="20" maxlength="40" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="middleName" key="user.middleName"}</td>
	<td class="value"><input type="text" name="middleName" id="middleName" value="{$middleName|escape}" size="20" maxlength="40" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="lastName" required="true" key="user.lastName"}</td>
	<td class="value"><input type="text" name="lastName" id="lastName" value="{$lastName|escape}" size="20" maxlength="90" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="initials" key="user.initials"}</td>
	<td class="value"><input type="text" name="initials" id="initials" value="{$initials|escape}" size="5" maxlength="5" class="textField" />&nbsp;&nbsp;{translate key="user.initialsExample"}</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="gender" key="user.gender"}</td>
	<td class="value">
		<select name="gender" id="gender" size="1" class="selectMenu">
			{html_options_translate options=$genderOptions selected=$gender}
		</select>
	</td>
</tr>

<tr valign="top" >
	<td class="label">{fieldLabel name="affiliation" key="user.affiliation" required="true"}</td>
	<td class="value">
    <select name="affiliation_select" id="affil_select" class="selectMenu selectForm" onchange="showAffilBox(this);">
      {if !$affiliation_select}
				<option value="">{translate key="common.select"}</option>
			{/if}
      {html_options options=$affiliations selected=$affiliation_select}
    </select>
  </td>
</tr>
<tr valign="top" id="affil_box" {if $affiliation_select neq 'else'}class="hidden"{/if}>
  <td class="label">
  </td>
  <td class="value">
    <textarea name="affiliation" id="affil_text" rows="5" cols="40" class="textArea">{$affiliation|escape}</textarea>
  </td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="signature" key="user.signature"}</td>
	<td class="value"><textarea name="signature[{$formLocale|escape}]" id="signature" rows="5" cols="40" class="textArea">{$signature[$formLocale]|escape}</textarea></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="email" required="true" key="user.email"}</td>
	<td class="value"><input type="text" name="email" id="email" value="{$email|escape}" size="30" maxlength="90" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="userUrl" key="user.url"}</td>
	<td class="value"><input type="text" name="userUrl" id="userUrl" value="{$userUrl|escape}" size="30" maxlength="90" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="phone" key="user.phone"}</td>
	<td class="value"><input type="text" name="phone" id="phone" value="{$phone|escape}" size="15" maxlength="24" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="fax" key="user.fax"}</td>
	<td class="value"><input type="text" name="fax" id="fax" value="{$fax|escape}" size="15" maxlength="24" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="mailingAddress" key="common.mailingAddress"}</td>
	<td class="value"><textarea name="mailingAddress" id="mailingAddress" rows="5" cols="40" class="textArea">{$mailingAddress|escape}</textarea></td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="billingAddress" key="common.billingAddress"}</td>
	<td class="value"><textarea name="billingAddress" id="billingAddress" rows="4" cols="40" class="textArea">{$billingAddress|escape}</textarea></td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="companyId" key="common.companyId"}</td>
	<td class="value"><input type="text" name="companyId" id="companyId" size="15" maxlength="24" class="textField" value="{$companyId|escape}"/></td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="VATRegNo" key="common.VATRegNo"}</td>
	<td class="value"><input type="text" name="VATRegNo" id="VATRegNo" size="15" maxlength="24" class="textField" value="{$VATRegNo|escape}"/></td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="country" key="common.country" required="true"}</td>
	<td class="value">
		<select name="country" id="country" class="selectMenu selectForm">
			<option value=""></option>
			{html_options options=$countries selected=$country}
		</select>
	</td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="timeZone" key="common.timeZone"}</td>
	<td class="value">
		<select name="timeZone" id="timeZone" class="selectMenu">
			<option value=""></option>
			{html_options options=$timeZones selected=$timeZone}
		</select>
	</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="interests" key="user.interests"}</td>
	<td class="value"><textarea name="interests[{$formLocale|escape}]" id="interests" rows="5" cols="40" class="textArea">{$interests[$formLocale]|escape}</textarea></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="biography" key="user.biography"}<br />{translate key="user.biography.description"}</td>
	<td class="value"><textarea name="biography[{$formLocale|escape}]" id="biography" rows="5" cols="40" class="textArea">{$biography[$formLocale]|escape}</textarea></td>
</tr>
<tr valign="top">
       <td class="label">
	       {fieldLabel name="profileImage" key="user.profile.form.profileImage"}
       </td>
       <td class="value">
	       <input type="file" id="profileImage" name="profileImage" class="uploadField" /> <input type="submit" name="uploadProfileImage" value="{translate key="common.upload"}" class="button" />
	       {if $profileImage}
		       {translate key="common.fileName"}: {$profileImage.name|escape} {$profileImage.dateUploaded|date_format:$datetimeFormatShort} <input type="submit" name="deleteProfileImage" value="{translate key="common.delete"}" class="button" />
		       <br />
		       <img src="{$sitePublicFilesDir}/{$profileImage.uploadName|escape:"url"}" width="{$profileImage.width|escape}" height="{$profileImage.height|escape}" style="border: 0;" alt="{translate key="user.profile.form.profileImage"}" />
	       {/if}
       </td>
</tr>

{if $allowRegReader || $allowRegAuthor || $allowRegReviewer}
	<tr valign="top">
		<td class="label">{translate key="user.roles"}</td>
		<td class="value">
			{*{if $allowRegReader}
				<input type="checkbox" id="readerRole" name="readerRole" {if $isReader || $readerRole}checked="checked" {/if}/>&nbsp;{fieldLabel name="readerRole" key="user.role.reader"}<br/>
			{/if}*}
			<!-- HIDDEN reader -->
			<input type="hidden" id="readerRole" name="readerRole" value="checked"/>
			{if $allowRegAuthor}
				<input type="checkbox" id="authorRole" name="authorRole" {if $isAuthor || $authorRole}checked="checked" {/if}/>&nbsp;{fieldLabel name="authorRole" key="user.role.author"}<br/>
			{/if}
			{if $allowRegReviewer}
				<input type="checkbox" id="reviewerRole" name="reviewerRole" {if $isReviewer || $reviewerRole}checked="checked" {/if}/>&nbsp;{fieldLabel name="reviewerRole" key="user.role.reviewer"}<br/>
			{/if}
		</td>
	</tr>
{/if}

<!-- GDPR -->

	<tr valign="top">
		<td class="label">
			<input type="checkbox" id="gdpr" name="gdpr" value="1" {if $gdpr}checked="checked" {/if}/>&nbsp;{fieldLabel name="gdpr" key=" " required="true"}
		</td>
		<td class="value">
			{fieldLabel name="gdpr" key="user.gdpr.text" required="true"}
		</td>
	</tr>

<!-- GDPR -->

{if $displayOpenAccessNotification}
	{assign var=notFirstSchedConf value=0}
	{foreach from=$schedConfs name=schedConfOpenAccessNotifications key=thisSchedConfId item=thisSchedConf}
		{assign var=thisSchedConfId value=$thisSchedConf->getId()}
		{assign var=enableOpenAccessNotification value=$thisSchedConf->getSetting('enableOpenAccessNotification')}
		{assign var=notificationEnabled value=$user->getSetting('openAccessNotification', $thisSchedConfId)}
		{if !$notFirstSchedConf}
			{assign var=notFirstSchedConf value=1}
			<tr valign="top">
				<td class="label">{translate key="user.profile.form.openAccessNotifications"}</td>
				<td class="value">
		{/if}

		{if $enableOpenAccessNotification}
			<input type="checkbox" name="openAccessNotify[]" {if $notificationEnabled}checked="checked" {/if}id="openAccessNotify-{$thisSchedConfId|escape}" value="{$thisSchedConfId|escape}" /> <label for="openAccessNotify-{$thisSchedConfId|escape}">{$thisSchedConf->getFullTitle()|escape}</label><br/>
		{/if}

		{if $smarty.foreach.schedConfOpenAccessNotifications.last}
				</td>
			</tr>
		{/if}
	{/foreach}
{/if}

</table>

<p><input type="submit" value="{translate key="common.save"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="user"}'" /></p>
</form>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{include file="common/footer.tpl"}
