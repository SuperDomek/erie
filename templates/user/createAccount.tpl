{**
 * createAccount.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User registration form.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="navigation.account"}
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
    // clean the prefilled boxes
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

//shows Billing address textbox if the calling checkbox checked
function showBillAddr(checkbox){
  if(checkbox.checked) {
    var mailAddr = '{/literal}{fieldLabel name="mailingAddress" required="true" key="common.mailingAddress"}{literal}';
    document.getElementById("billingAddress").parentNode.parentNode.style.display = "table-row";
    document.getElementById("mailAddrLabel").innerHTML = mailAddr;
  }
  else {
    var mailBillAddr = '{/literal}{fieldLabel name="mailingAddress" required="true" key="common.mailingBillingAddress"}{literal}';
    document.getElementById("billingAddress").parentNode.parentNode.style.display = "none";
    document.getElementById("mailAddrLabel").innerHTML = mailBillAddr;
  }
}
// -->
</script>
{/literal}

<form name="createAccount" method="post" action="{url op="createAccount"}">

<p>{translate key="user.account.completeForm"}</p>

{if !$existingUser}
	{url|assign:"url" page="user" op="account" existingUser=1}
	<p>{translate key="user.account.hasAccount" accountUrl=$url}</p>
{else}
	{url|assign:"url" page="user" op="account"}
	<p>{translate key="user.account.hasNoAccount" accountUrl=$url}</p>
	<input type="hidden" name="existingUser" value="1"/>
{/if}

{if $source}
	<input type="hidden" name="source" value="{$source|escape}" />
{/if}

<h3>{translate key="user.profile"}</h3>
{include file="common/formErrors.tpl"}

{if $existingUser}
<p>{translate key="user.account.loginToRegister"}</p>
{/if}

{* NOTE: The absolutely required fields in following form should be synced
   with the implementation in templates/registration/userRegistrationForm.tpl *}

<table class="data" width="100%">
{if count($formLocales) > 1 && !$existingUser}
	<tr valign="top">
		<td width="20%" class="label">{fieldLabel name="formLocale" key="form.formLanguage"}</td>
		<td width="80%" class="value">
			{url|assign:"createAccountUrl" page="user" op="account" escape=false}
			{form_language_chooser form="createAccount" url=$createAccountUrl}
			<span class="instruct">{translate key="form.formLanguage.description"}</span>
		</td>
	</tr>
{/if}
<tr valign="top">
	<td width="20%" class="label">{fieldLabel name="username" required="true" key="user.username"}</td>
	<td width="80%" class="value"><input type="text" name="username" value="{$username|escape}" id="username" size="20" maxlength="32" class="textField" /></td>
</tr>
{if !$existingUser}
<tr valign="top">
	<td></td>
	<td class="instruct">{translate key="user.account.usernameRestriction"}</td>
</tr>
{/if}

<tr valign="top">
	<td class="label">{fieldLabel name="password" required="true" key="user.password"}</td>
	<td class="value"><input type="password" name="password" value="{$password|escape}" id="password" size="20" maxlength="32" class="textField" /></td>
</tr>

{if !$existingUser}
<tr valign="top">
	<td></td>
	<td class="instruct">{translate key="user.account.passwordLengthRestriction" length=$minPasswordLength}</td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="password2" required="true" key="user.account.repeatPassword"}</td>
	<td class="value"><input type="password" name="password2" id="password2" value="{$password2|escape}" size="20" maxlength="32" class="textField" /></td>
</tr>

{if $captchaEnabled}
	<tr>
		<td class="label" valign="top">{fieldLabel name="captcha" required="true" key="common.captchaField"}</td>
		<td class="value">
			<img src="{url page="user" op="viewCaptcha" path=$captchaId}" alt="{translate key="common.captchaField.altText"}" /><br />
			<span class="instruct">{translate key="common.captchaField.description"}</span><br />
			<input name="captcha" id="captcha" value="" size="20" maxlength="32" class="textField" />
			<input type="hidden" name="captchaId" value="{$captchaId|escape:"quoted"}" />
		</td>
	</tr>
{/if}

<tr valign="top">
	<td class="label">{fieldLabel name="salutation" key="user.salutation"}</td>
	<td class="value"><input type="text" name="salutation" id="salutation" value="{$salutation|escape}" size="20" maxlength="40" class="textField" /></td>
</tr>
<tr valign="top">
	<td class="label">{fieldLabel name="firstName" required="true" key="user.firstName"}</td>
	<td class="value"><input type="text" id="firstName" name="firstName" value="{$firstName|escape}" size="20" maxlength="40" class="textField" /></td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="lastName" required="true" key="user.lastName"}</td>
	<td class="value"><input type="text" id="lastName" name="lastName" value="{$lastName|escape}" size="20" maxlength="90" class="textField" /></td>
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
	<td class="label">{fieldLabel name="email" required="true" key="user.email"}</td>
	<td class="value"><input type="text" id="email" name="email" value="{$email|escape}" size="30" maxlength="90" class="textField" /></td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="phone" key="user.phone"}</td>
	<td class="value"><input type="text" name="phone" id="phone" value="{$phone|escape}" size="15" maxlength="24" class="textField" /></td>
</tr>

<tr valign="top">
	<td class="label" id="mailAddrLabel">{fieldLabel name="mailingAddress" required="true" key="common.mailingBillingAddress"}</td>
	<td class="value"><textarea name="mailingAddress" id="mailingAddress" rows="4" cols="40" class="textArea">{$mailingAddress|escape}</textarea></td>
</tr>

<tr valign="top">
  <td class="label">{fieldLabel name="billingAddressCheck" key="common.billingAddressCheck"}</td>
  <td class="value"><input type="checkbox" name="billingAddressCheck" id="billingAddressCheck" onclick="showBillAddr(this)"/></td>
</tr>

<tr valign="top" class="hidden">
	<td class="label">{fieldLabel name="billingAddress" key="common.billingAddress"}</td>
	<td class="value"><textarea name="billingAddress" id="billingAddress" rows="4" cols="40" class="textArea">{$billingAddress|escape}</textarea></td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="companyId" key="common.companyId"}</td>
	<td class="value"><input type="text" name="companyId" id="companyId" size="15" maxlength="24" class="textField" value="{$companyId|escape}"/></td>
</tr>

<tr valign="top">
	<td class="label">{fieldLabel name="VATRegNo" key="common.VATRegNo" required="true"}</td>
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
	<td class="label">{fieldLabel name="sendPassword" key="user.sendPassword"}</td>
	<td class="value">
		<input type="checkbox" name="sendPassword" id="sendPassword" value="1"{if $sendPassword} checked="checked"{/if} /> <label for="sendPassword">{translate key="user.sendPassword.description"}</label>
	</td>
</tr>
{/if}

{if ($allowRegReader || $allowRegReader === null) or $enableOpenAccessNotification or ($allowRegAuthor || $allowRegAuthor === null) or ($allowRegReviewer || $allowRegReviewer === null)}
<tr valign="top">
	<td class="label">{fieldLabel suppressId="true" name="createAs" key="user.account.createAs"}</td>
	<td class="value">
	{*if $allowRegReader || $allowRegReader === null*}
			<input type="hidden" name="createAsReader" id="createAsReader" value="1"{if $createAsReader} checked="checked"{/if} /><!-- <label for="createAsReader">{translate key="user.role.reader"}</label>: {translate key="user.account.readerDescription"}<br />-->
		{*/if*}
		{if $enableOpenAccessNotification}
			<input type="checkbox" name="openAccessNotification" id="openAccessNotification" value="1"{if $openAccessNotification} checked="checked"{/if} /> <label for="openAccessNotification">{translate key="user.role.reader"}</label>: {translate key="user.account.openAccessNotificationDescription"}<br />
		{/if}
		{if $allowRegAuthor || $allowRegAuthor === null}
			<input type="checkbox" name="createAsAuthor" id="createAsAuthor" value="1" checked="checked" /> <label for="createAsAuthor">{translate key="user.role.author"}</label>: {translate key="user.account.authorDescription"}<br />
    {else}
      <input type="checkbox" name="createAsAuthor" id="createAsAuthor" value="1" disabled /> <label for="createAsAuthor">{translate key="user.role.author"}</label>: {translate key="author.submit.notAccepting"}<br />
		{/if}
		{if $allowRegReviewer || $allowRegReviewer === null}
      <input type="checkbox" name="createAsReviewer" id="createAsReviewer" value="1"{if $createAsReviewer} checked="checked"{/if} /> <label for="createAsReviewer">{translate key="user.role.reviewer"}</label>: {if $existingUser}{translate key="user.account.reviewerDescriptionNoInterests"}{else}{translate key="user.account.reviewerDescription"} <input type="text" name="interests[{$formLocale|escape}]" value="{$interests[$formLocale]|escape}" size="20" maxlength="255" class="textField" />{/if}
    {else}
      <input type="checkbox" name="createAsReviewer" id="createAsReviewer" value="1"{if $createAsReviewer} checked="checked"{/if} disabled /> <label for="createAsReviewer">{translate key="user.role.reviewer"}</label>: {translate key="reviewer.notAccepting"}
		{/if}
	</td>
</tr>
{/if}
</table>

<!--<h5>{translate key="schedConf.registration"}</h5>-->
<table class="listing" width="100%">
	<tr>
		<td colspan="2" >&nbsp;</td>
	</tr>
	<tr valign="top" class="heading">
		<td width="30%">{translate key="schedConf.registration.type"}</td>
		<td width="70%">{translate key="schedConf.registration.cost"}</td>
	</tr>
	<tr>
		<td colspan="2" class="headseparator">&nbsp;</td>
	</tr>
	{assign var="isFirstRegistrationType" value=true}
	{iterate from=registrationTypes item=registrationType}
	{assign var="typeId" value=$registrationType->getTypeId()}
	{if $registrationType->getPublic()}
		<tr valign="top">
			<td class="label">
        <strong>{$registrationType->getRegistrationTypeName()|escape}</strong>
			</td>
			<td class="data">
				{if strtotime($registrationType->getOpeningDate()) < time() && strtotime($registrationType->getClosingDate()) > time()}
					{assign var="registrationMethodAvailable" value=1}
					<input id="registrationType-{$typeId|escape}" type="radio" {if $isFirstRegistrationType}checked="checked" {/if}name="registrationTypeId" value="{$typeId|escape}" />
					<label for="registrationType-{$typeId|escape}"> {$registrationType->getCost()|string_format:"%.2f"} {$registrationType->getCurrencyCodeAlpha()|escape} / {$registrationType->getCostUni()|string_format:"%.2f"} {$registrationType->getCurrencyCodeUni()|escape}</label>
					{translate key="schedConf.registration.typeCloses" closingDate=$registrationType->getClosingDate()|date_format:$dateFormatShort}
					{assign var="isFirstRegistrationType" value=false}
				{elseif strtotime($registrationType->getOpeningDate()) > time()}
					<input type="radio" name="registrationTypeId" value="{$typeId|escape}" disabled="disabled" />
					{$registrationType->getCost()|string_format:"%.2f"} {$registrationType->getCurrencyCodeAlpha()|escape} / {$registrationType->getCostUni()|string_format:"%.2f"} {$registrationType->getCurrencyCodeUni()|escape}
					{translate key="schedConf.registration.typeFuture" openingDate=$registrationType->getOpeningDate()|date_format:$dateFormatShort}
				{else}
					<input type="radio" name="registrationTypeId" value="{$typeId|escape}" disabled="disabled" />
					{$registrationType->getCost()|string_format:"%.2f"} {$registrationType->getCurrencyCodeAlpha()|escape} / {$registrationType->getCostUni()|string_format:"%.2f"} {$registrationType->getCurrencyCodeUni()|escape}
					{translate key="schedConf.registration.typeClosed" closingDate=$registrationType->getClosingDate()|date_format:$dateFormatShort}
				{/if}
			</td>
		</tr>
		{if $registrationType->getRegistrationTypeDescription()}
			<tr valign="top">
				<td colspan="2">{$registrationType->getRegistrationTypeDescription()|nl2br}</td>
			</tr>
		{/if}
		
		<tr valign="top">
			<td colspan="2">&nbsp;</td>
		</tr>
	{/if}
	{/iterate}
	{if $registrationTypes->wasEmpty()}
		<tr>
			<td colspan="2" class="nodata">{translate key="schedConf.registrationTypes.noneAvailable"}</td>
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
</table>

<p><input type="submit" value="{translate key="user.createAccount"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url page="index"}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

{if $privacyStatement}
<h3>{translate key="user.account.privacyStatement"}</h3>
<p>{$privacyStatement|nl2br}</p>
{/if}
</form>

{include file="common/footer.tpl"}
