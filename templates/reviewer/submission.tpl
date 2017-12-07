{**
 * submission.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the reviewer administration page.
 *
 * FIXME: At "Notify The Director", fix the date.
 *
 * $Id$
 *}
{strip}
{assign var="paperId" value=$submission->getPaperId()}
{assign var="reviewId" value=$reviewAssignment->getId()}
{if $reviewAssignment->getStage() == REVIEW_STAGE_ABSTRACT}
	{translate|assign:"pageTitleTranslated" key="submission.page.abstractReview" id=$paperId}
	{assign var="pageCrumbTitle" value="submission.abstractReview"}
{else}
	{translate|assign:"pageTitleTranslated" key="submission.page.paperReview" id=$paperId}
	{assign var="pageCrumbTitle" value="submission.paperReview"}
{/if}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
{literal}
<!--

var askLeaving = '{/literal}{$askLeaving}{literal}';

$(window).bind("beforeunload",function() {
  if(askLeaving == '1'){
    return 'The recommendation is not set up yet.';
  }
  else return undefined;
});

function confirmSubmissionCheck() {
  askLeaving = '0';
	if (document.recommendation.recommendation.value=='') {
		alert('{/literal}{translate|escape:"javascript" key="reviewer.paper.mustSelectDecision"}{literal}');
		return false;
	}
	return confirm('{/literal}{translate|escape:"javascript" key="reviewer.paper.confirmDecision"}{literal}');
}

// -->
{/literal}
</script>
<div id="submissionToBeReviewed">
<h3>{translate key="reviewer.paper.submissionToBeReviewed"}</h3>

<table width="100%" class="data">
<tr valign="top">
	<td width="20%" class="label">{translate key="paper.title"}</td>
	<td width="80%" class="value">{$submission->getLocalizedTitle()|strip_unsafe_html}</td>
</tr>

{assign var=sessionType value=$submission->getData('sessionType')}
{if isset($sessionTypes[$sessionType])}
	<tr valign="top">
		<td width="20%" class="label">{translate key="paper.sessionType"}</td>
		<td width="80%" colspan="2" class="data">{$sessionTypes[$sessionType]|escape}</td>
	</tr>
{/if}{* isset($sessionTypes[$submissionType]) *}

<tr valign="top">
	<td class="label">{translate key="paper.abstract"}</td>
	<td class="value">{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br}</td>
</tr>
{assign var=editAssignments value=$submission->getEditAssignments()}
{foreach from=$editAssignments item=editAssignment}
	{if !$notFirstEditAssignment}
		{assign var=notFirstEditAssignment value=1}
		<tr valign="top">
			<td class="label">{translate key="reviewer.paper.submissionDirector"}</td>
			<td class="value">
	{/if}
			{assign var=emailString value=$editAssignment->getDirectorFullName()|concat:" <":$editAssignment->getDirectorEmail():">"}
			{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$currentUrl subject=$submission->getLocalizedTitle()|strip_tags paperId=$paperId}
			{$editAssignment->getDirectorFullName()|escape} {icon name="mail" url=$url}
			({if $editAssignment->getIsDirector()}{translate key="user.role.director"}{else}{translate key="user.role.trackDirector"}{/if})
			<br/>
{/foreach}
{if $notFirstEditAssignment}
		</td>
	</tr>
{/if}
<tr valign="top">
	<td class="label">{translate key="submission.metadata"}</td>
	<td class="value">
		<a href="{url op="viewMetadata" path=$reviewId|to_array:$paperId}" class="action" target="_new">{translate key="submission.viewMetadata"}</a>
	</td>
</tr>
</table>
</div>
<div class="separator"></div>
<div id="reviewSchedule">
<h3>{translate key="reviewer.paper.reviewSchedule"}</h3>
<table width="100%" class="data">
<tr valign="top">
	<td class="label" width="20%">{translate key="reviewer.paper.schedule.request"}</td>
	<td class="value" width="80%">{if $submission->getDateNotified()}{$submission->getDateNotified()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="reviewer.paper.schedule.response"}</td>
	<td class="value">{if $submission->getDateConfirmed()}{$submission->getDateConfirmed()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="reviewer.paper.schedule.submitted"}</td>
	<td class="value">{if $submission->getDateCompleted()}{$submission->getDateCompleted()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="reviewer.paper.schedule.due"}</td>
	<td class="value">{if $submission->getDateDue()}{$submission->getDateDue()|date_format:$dateFormatShort}{else}&mdash;{/if}</td>
</tr>
</table>
</div>
<div class="separator"></div>
<div id="reviewSteps">
<h3>{translate key="reviewer.paper.reviewSteps"}</h3>

{include file="common/formErrors.tpl"}

{assign var="currentStep" value=1}

<table width="100%" class="data">
<tr valign="top">
	<td width="3%">{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td width="97%"><span class="instruct">{translate key="reviewer.paper.notifyEditorA"}{if $editAssignment}, {$editAssignment->getDirectorFullName()|escape},{/if} {translate key="reviewer.paper.notifyEditorB"}</span></td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td>
		<!--{translate key="submission.response"}&nbsp;&nbsp;&nbsp;&nbsp;-->
		{if not $confirmedStatus}
			{url|assign:"acceptUrl" op="confirmReview" reviewId=$reviewId}
			{url|assign:"declineUrl" op="confirmReview" reviewId=$reviewId declineReview=1}

			{if !$submission->getCancelled()}
        <div style="float:left;text-align:center;">
          <a href="{$acceptUrl}" alt="Accept the review">
			      <img src="{$baseUrl}/templates/images/icons/accept_big.png" alt="Accept the review"/><br />{translate key="reviewer.paper.canDoReview"}</a>
        </div>

        <div style="float:left;text-align:center;margin-left:2em;">
          <a href="{$declineUrl}" alt="Reject the review">
			      <img src="{$baseUrl}/templates/images/icons/decline_big.png" alt="Reject the review"/><br />{translate key="reviewer.paper.cannotDoReview"}</a>
        </div>
			{else}
        {translate key="submission.event.reviewer.reviewCancelled"}
			{/if}
		{else}
			{if not $declined}
        <div style="float:left;text-align:center;">
          <img src="{$baseUrl}/templates/images/icons/accept_big.png" alt="Accepted the review"/><br />{translate key="submission.accepted"}
        </div>
      {else}
        <div style="float:left;text-align:center;">
          <img src="{$baseUrl}/templates/images/icons/decline_big.png" alt="Rejected the review"/><br />{translate key="submission.rejected"}
        </div>
      {/if}
		{/if}
	</td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
{if $schedConf->getLocalizedSetting('reviewGuidelines') != ''}
<tr valign="top">
        <td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td><span class="instruct">{translate key="reviewer.paper.consultGuidelines"}</span></td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
{/if}
<tr valign="top">
	<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td><span class="instruct">{translate key="$reviewerInstruction3"}</span></td>
</tr>
{if $schedConf->getSetting('reviewMode') != REVIEW_MODE_ABSTRACTS_ALONE}
<tr valign="top">
	<td>&nbsp;</td>
	<td>
		<table width="100%" class="data">
			{if ($confirmedStatus and not $declined) or not $schedConf->getSetting('restrictReviewerFileAccess')}
				{if $reviewAssignment->getStage() == REVIEW_STAGE_ABSTRACT}
					<tr valign="top">
						<td width="30%" class="label">
							{translate key="submission.abstract"}
						</td>
						<td class="value" width="70%">
							{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br}
						</td>
					</tr>
				{else}
					{if $reviewFile}
						{if $submission->getDateConfirmed() or not $schedConf->getSetting('restrictReviewerAccessToFile')}
							{if $reviewFile->getChecked() == 1}
								<tr valign="top">
									<td width="30%" class="label">
										{translate key="submission.submissionManuscript"}
									</td>
									<td class="value" width="70%">
										<a href="{url op="downloadFile" path=$reviewId|to_array:$paperId:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file">{$reviewFile->getFileName()|escape}</a>
										&nbsp;&nbsp;{$reviewFile->getDateModified()|date_format:$dateFormatShort}
									</td>
								</tr>
								{if $changes}
									<tr valign="top">
										<td width="30%" class="label">
											{translate key="common.checklistOfAdjustments"}
										</td>
										<td class="value" width="70%">
											<span>{$changes|escape}</span>
										</td>
									</tr>
								{/if} {* $changes *}
							{else} {* $reviewFile->getChecked() != 1 *}
									<tr valign="top">
										<td width="30%" class="label">
											{translate key="submission.submissionManuscript"}
										</td>
										<td class="value" width="70%">
											<span class="warning">{translate key="submission.fileNotChecked"}</span>
										</td>
									</tr>
							{/if} {* $reviewFile->getChecked() *}
						{else}
							<tr valign="top">
								<td width="30%" class="label">
									{translate key="submission.submissionManuscript"}
								</td>
								<td class="value" width="70%">
									{$reviewFile->getFileName()|escape}
									&nbsp;&nbsp;{$reviewFile->getDateModified()|date_format:$dateFormatShort}
								</td>
							</tr>
						{/if} {* confirmed or not restricted *}
					{else}
						<tr valign="top">
							<td width="30%" class="label">
								{translate key="submission.submissionManuscript"}
							</td>
							<td class="value" width="70%">
								<span class="warning">{translate key="submission.fileNotUploadedYet"}</span>
							</td>
						</tr>
					{/if} {* $reviewFile *}
				{/if} {* $reviewAssignment->getStage() *}
			{/if} {* $confirmedStatus and not $declined *}
		</table>
	</td>
</tr>
{/if}
<tr>
	<td colspan="2">&nbsp;</td>
</tr>

 {* Grey out text if the file is not checked or uploaded yet *}
  {if $reviewAssignment->getStage() != REVIEW_STAGE_ABSTRACT}
    {if $reviewFile}
      {if $reviewFile->getChecked() != 1}
        {assign var="greyOut" value=1}
      {/if}
    {else}
      {assign var="greyOut" value=1}
    {/if}
  {/if}

<tbody {if $greyOut}style="color:#a5a3a5 !important;"{/if}>
{if $reviewAssignment->getReviewFormId()}	
	<tr valign="top">
		<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
		<td><span class="instruct">{translate key="reviewer.paper.enterReviewForm"}</span></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
      {if $confirmedStatus and not $declined}
        <div style="float:left;text-align:center;">
					{if $greyOut}
						<img src="{$baseUrl}/templates/images/icons/review_form.png" alt="Open the review form"/><br />{translate key="submission.reviewForm"}
					{else}
						<a href="{url op="editReviewFormResponse" path=$reviewId|to_array:$reviewAssignment->getReviewFormId()}" class="icon">
							<img src="{$baseUrl}/templates/images/icons/review_form.png" alt="Open the review form"/><br />{translate key="submission.reviewForm"}
						</a>
					{/if}
        </div>
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
{else}{* $reviewAssignment->getReviewFormId() *}
	<tr valign="top">
		<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
		<td><span class="instruct">{translate key="reviewer.paper.enterReviewA"}</span></td>
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			{translate key="event.logType.review"}
			{if $confirmedStatus and not $declined}
				<a href="javascript:openComments('{url op="viewPeerReviewComments" path=$paperId|to_array:$reviewId}');" class="icon">{icon name="comment"}</a>
			{else}
				 {icon name="comment" disabled="disabled"}
			{/if}
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
{/if}{* $reviewAssignment->getReviewFormId() *}

<!--
<tr valign="top">
	<td>{$currentStep|escape}.{*assign var="currentStep" value=$currentStep+1*}</td>
	<td><span class="instruct">{translate key="reviewer.paper.uploadFile"}</span></td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td>
		<table class="data" width="100%">
			{foreach from=$submission->getReviewerFileRevisions() item=reviewerFile key=key}
				{assign var=uploadedFileExists value="1"}
				<tr valign="top">
				<td class="label" width="30%">
					{if $key eq "0"}
						{translate key="reviewer.paper.uploadedFile"}
					{/if}
				</td>
				<td class="value" width="70%">
					<a href="{url op="downloadFile" path=$reviewId|to_array:$paperId:$reviewerFile->getFileId():$reviewerFile->getRevision()}" class="file">{$reviewerFile->getFileName()|escape}</a>
					{$reviewerFile->getDateModified()|date_format:$dateFormatShort}
					{if ($submission->getRecommendation() === null || $submission->getRecommendation() === '') && (!$submission->getCancelled())}
						<a class="action" href="{url op="deleteReviewerVersion" path=$reviewId|to_array:$reviewerFile->getFileId():$reviewerFile->getRevision()}">{translate key="common.delete"}</a>
					{/if}
				</td>
				</tr>
			{foreachelse}
				<tr valign="top">
				<td class="label" width="30%">
					{translate key="reviewer.paper.uploadedFile"}
				</td>
				<td class="nodata">
					{translate key="common.none"}
				</td>
				</tr>
			{/foreach}
		</table>
		{if $submission->getRecommendation() === null || $submission->getRecommendation() === ''}
			<form method="post" action="{url op="uploadReviewerVersion"}" enctype="multipart/form-data">
				<input type="hidden" name="reviewId" value="{$reviewId|escape}" />
				<input type="file" name="upload" {if not $confirmedStatus or $declined or $submission->getCancelled()}disabled="disabled"{/if} class="uploadField" />
				<input type="submit" name="submit" value="{translate key="common.upload"}" {if not $confirmedStatus or $declined or $submission->getCancelled()}disabled="disabled"{/if} class="button" />
			</form>
			<span class="instruct">
				<a class="action" href="javascript:openHelp('{get_help_id key="editorial.trackDirectorsRole.review.blindPeerReview" url="true"}')">{translate key="reviewer.paper.ensuringBlindReview"}</a>
			</span>
		{/if}
	</td>
</tr>

<tr>
	<td colspan="2">&nbsp;</td>
</tr>-->
<tr valign="top">
	<td>{$currentStep|escape}.{assign var="currentStep" value=$currentStep+1}</td>
	<td><span class="instruct">{translate key="reviewer.paper.selectRecommendation"}</span></td>
</tr>
<tr valign="top">
	<td>&nbsp;</td>
	<td>
		<table class="data" width="100%">
			<tr valign="top">
				<td class="label" width="30%">{translate key="submission.recommendation"}</td>
				<td class="value" width="70%">
				{if $submission->getRecommendation() !== null && $submission->getRecommendation() !== ''}
					{assign var="recommendation" value=$submission->getRecommendation()}
					<strong>{translate key=$reviewerRecommendationOptions.$recommendation}</strong>&nbsp;&nbsp;
					{$submission->getDateCompleted()|date_format:$dateFormatShort}
				{else}
					<form name="recommendation" method="post" action="{url op="recordRecommendation"}">
					<input type="hidden" name="reviewId" value="{$reviewId|escape}" />
					<select name="recommendation" {if not $confirmedStatus or $declined or $submission->getCancelled() or (!$reviewFormResponseExists and !$reviewAssignment->getMostRecentPeerReviewComment() and !$uploadedFileExists)}disabled="disabled"{/if} class="selectMenu">
						{html_options_translate options=$reviewerRecommendationOptions selected=''}
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="submit" name="submit" onclick="return confirmSubmissionCheck()" class="button" value="{translate key="reviewer.paper.submitReview"}" {if not $confirmedStatus or $declined or $submission->getCancelled() or (!$reviewFormResponseExists and !$reviewAssignment->getMostRecentPeerReviewComment() and !$uploadedFileExists)}disabled="disabled"{/if} />
					</form>
				{/if}
				</td>
			</tr>
		</table>
	</td>
</tr>
</tbody>
</table>
</div>
{if $schedConf->getLocalizedSetting('reviewGuidelines') != ''}
<div class="separator"></div>
<div id="reviewerGuidelines">
<h3>{translate key="reviewer.paper.reviewerGuidelines"}</h3>
<p>{$schedConf->getLocalizedSetting('reviewGuidelines')|nl2br}</p>
</div>
{/if}

{include file="common/footer.tpl"}
