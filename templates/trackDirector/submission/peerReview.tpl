{**
 * peerReview.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the peer review table.
 *
 * $Id$
 *}

{if $stage != $smarty.const.REVIEW_STAGE_ABSTRACT}
	{if $reviewFile}
	{literal}
	<script type="text/javascript">
	$(function(){
		var reviewFile = {/literal}{$reviewFile->getChecked()}{literal};
		if(reviewFile != 1){
			$('div.blockable').block({
				message: '<h4>{/literal}{translate key="submission.fileNotChecked.block"}{literal}</h4>',
				css: {cursor: 'default'},
				overlayCSS: {cursor: 'default'}
				});
		}
	});
	</script>
	{/literal}
	{else}
	{literal}
	<script type="text/javascript">
	$(function(){
		$('div.blockable').block({
			message: '<h4>{/literal}{translate key="submission.fileNotUploadedYet"}{literal}</h4>',
			css: {cursor: 'default'},
			overlayCSS: {cursor: 'default'}
			});
	});
	</script>
	{/literal}
	{/if}
{/if}


<div id="submission">

<ul class="no-list">
  {if $isDirector}
	<li><header>{translate key="paper.authors"}</header>
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$submission->getAuthorEmails() subject=$submission->getLocalizedTitle() paperId=$submission->getPaperId()}
			<a href="{$url}" alt="Mail the author" title="Mail the author">{$submission->getAuthorString()|escape}</a>{*icon name="mail" url=$url*}
	</li>
	{/if}
	<li><header>{translate key="paper.submitterId"}</header>
		{$submitterId}</li>
	<li><header>{translate key="paper.title"}</header>
			{$submission->getLocalizedTitle()|strip_unsafe_html}</li>
	{*<li><header>{translate key="track.track"}</header>
	<form name="trackForm" id="trackForm" action="{url op="changeTrack" paperId=$submission->getPaperId()}" method="post">
		<input type="hidden" name="from" value="submissionReview" />
		<input type="hidden" name="stage" value="{$stage|escape}" />
		<select name="trackId" size="1" class="selectMenu">{html_options options=$tracks selected=$submission->getTrackId()}</select>
		<button type="submit" name="submit" form="trackForm" id="track_submit" value="Submit" class="button">{translate key="common.record"}</button>
	</form>
	</li>*}
	<li><header>{translate key="user.role.trackDirector"}</header>
	{assign var=editAssignments value=$submission->getEditAssignments()}
	{foreach from=$editAssignments item=editAssignment}
		{if $isDirector}
			{url|assign:"url" page="director" op="userProfile" path=$editAssignment->getDirectorId()}
			<a href="{$url}" alt="{translate key="user.profile.publicProfile" user=$editAssignment->getDirectorFullName()|escape}" title="{translate key="user.profile.publicProfile" user=$editAssignment->getDirectorFullName()|escape}">{$editAssignment->getDirectorFullName()|escape}</a>
		{else}
			{$editAssignment->getDirectorFullName()|escape}
		{/if}
		{if $editAssignment->getIsDirector()} (main){/if}
		<br/>
	{foreachelse}
		{translate key="common.noneAssigned"}
		<br />
	{/foreach}
	<br />
	{if $isDirector}
		<a href="{url page="director" op="assignDirector" path="trackDirector" paperId=$submission->getPaperId()}">
			<button class="button">{translate key="director.paper.assignTrackDirector"}</button></a>
	{/if}
	</li>
	{if $layoutFile && $isDirector}
	<li><header>{translate key="submission.layout.layoutFile"}</header>
		<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$layoutFile->getFileId()}" class="file" >
			{icon name="page_text"} {$layoutFile->getFileName()|escape}
		</a>
	</li>
	{/if}
</ul>
</div>

	{if $reviewingAbstractOnly}
	<div id="abstract">
	<h3>{translate key="submission.abstract"}</h3>
		{* If this review level is for the abstract only, show the abstract. *}

			{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br|default:"&mdash;"}

		{if $abstractChangesLast}

	<p>{translate key="submission.abstractChangedDate"}: 
			{$abstractChangesLast->getDateLogged()|date_format:$dateFormatShort}</p>


		{/if}
	</div>
	{/if}

{if ($stage == REVIEW_STAGE_PRESENTATION && $submission->getCurrentStage() < $smarty.const.REVIEW_STAGE_PRESENTATION)}
	{assign var="isStageDisabled" value=true}
{/if}

{if $isStageDisabled}
<div class="separator"></div>
	<table class="data" width="100%">
		<tr valign="middle">
			<td><h3>{translate key="submission.peerReview"}</h3></td>
		</tr>
		<tr>
			<td><span class="instruct">{translate key="director.paper.stageDisabled"}</span></td>
		</tr>
	</table>
{elseif $stage == $smarty.const.REVIEW_STAGE_ABSTRACT && $submission->getReviewMode() != $smarty.const.REVIEW_MODE_BOTH_SIMULTANEOUS}
{* No reviewers in abstract stage*}
{else}
<div id="peerReview">
	{if $submission->getReviewMode() == $smarty.const.REVIEW_MODE_BOTH_SIMULTANEOUS}
		<h3>{translate key="submission.review"}</h3>
	{elseif $stage >= $smarty.const.REVIEW_STAGE_PRESENTATION}
		<h3>{translate key="submission.stage" stage=$submission->getCurrentStage()-1}</h3>
	{/if}
	{if $stage != $smarty.const.REVIEW_STAGE_ABSTRACT}
		<h4>{translate key="submission.reviewVersion"}</h4>
		{if $reviewFile}
			{if $reviewFile->getChecked() == 1}
				<div class="tbl-container">
					<table class="files">
					<tbody>
						<tr>
							<td width="5%">
								<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file" >
									{icon name="page_text"}
								</a>
							</td>
							<td width="50%">
								<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file" >{$reviewFile->getFileName()|escape}</a>
							</td>
							<td><span style="color: #0b9e3f;">{translate key="submission.fileAccepted"}</span></td>
							<td width="10%">{$reviewFile->getNiceFileSize()}</td>
							{*<td width="20%">{$reviewFile->getFileType()|truncate:30}</td>*}
							<td width="15%">{$reviewFile->getDateModified()|date_format:$dateFormatShort}</td>
						</tr>
					</tbody>
					</table>
				</div>
				{if $stage >= $smarty.const.REVIEW_STAGE_PRESENTATION}
					<header>{translate key="common.checklistOfAdjustments"}</header>
					<p>
					{if $changes}
						{$changes|nl2br}
					{else}
						{translate key="common.none"}
					{/if}
					</p>
				{/if} {* $stage > $smarty.const.REVIEW_STAGE_PRESENTATION *}
			{elseif $isDirector}
				<div class="tbl-container">
					<table class="files">
					<tbody>
						<tr>
							<td width="5%">
								<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file" >
									{icon name="page_text"}
								</a>
							</td>
							<td width="50%">
								<a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file" >{$reviewFile->getFileName()|escape}</a>
							</td>
							<td><span style="color: #e85a09;">{translate key="submission.filePending"}</span></td>
							<td width="10%">{$reviewFile->getNiceFileSize()}</td>
							<td width="15%">{$reviewFile->getDateModified()|date_format:$dateFormatShort}</td>
						</tr>
					</tbody>
					</table>
				</div>
			{else} {* $reviewFile->getChecked() == 0 || !$isDirector *}
				<div class="tbl-container">
					<table class="files">
					<tbody>
						<tr>
							<td>
								<span class="warning">{translate key="submission.fileNotChecked"}</span>
							</td>
						</tr>
					</tbody>
					</table>
				</div>
			{/if}
			{if $reviewFile->getChecked() == null && $isDirector}
			<form method="post" id="formCheck" action="{url op="makeFileChecked"}">
				<input type="hidden" name="paperId" value="{$submission->getPaperId()}"/>
				<input type="hidden" name="fileId" value="{$reviewFile->getFileId()}"/>
				<input type="hidden" name="revision" value="{$reviewFile->getRevision()}"/>
				<header>{translate key="editor.paper.confirmReviewFile"}</header>
				<button type="submit" form="formCheck" name="checked" value="1" class="positive button">{translate key="submission.fileOkay"}</button>
				&nbsp;
				<button type="submit" form="formCheck" name="checked" value="0" class="negative button">{translate key="submission.fileNotOkay"}</button>
			</form>
			{elseif $reviewFile->getChecked() == 0 && $isDirector}
				<header>{translate key="director.paper.uploadReviewVersion"}</header>
			<form method="post" id="formUpload" action="{url op="uploadReviewVersion"}" enctype="multipart/form-data">
				<input type="hidden" name="paperId" value="{$submission->getPaperId()}" />
				<input type="file" name="upload" class="uploadField" />
				<button type="submit" name="submit" form="formUpload" value="Submit" class="button">{translate key="common.upload"}</button>
			</form>
			{/if} {* $reviewFile->getChecked() *}
					<!--&nbsp;&nbsp;&nbsp;&nbsp;<a class="action" href="javascript:openHelp('{get_help_id key="editorial.trackDirectorsRole.review.blindPeerReview" url="true"}')">{translate key="reviewer.paper.ensuringBlindReview"}</a> -->
		{else} {* !$reviewFile *}
			<div class="tbl-container">
				<table class="files">
				<tbody>
					<tr>
						<td>
							{translate key="common.none"}
						</td>
					</tr>
				</tbody>
				</table>
			</div>
		{/if} {* $reviewFile *}
	{/if} {* $stage *}

<div class="separator"></div>

	<div id="reviewers" class="blockable">
		<div class="revMenu"> 
			<h4>{translate key="user.role.reviewers"}</h4>
			<a href="{url op="selectReviewer" path=$submission->getPaperId()}"><button class="button">{translate key="director.paper.selectReviewer"}</button></a>
			<a href="{url op="submissionRegrets" path=$submission->getPaperId()}"><button class="button">{translate|escape key="trackDirector.regrets.link"}</button></a>
		</div>

	<table class="sortable revTable">
		<thead>
			<tr>
				<td width="6%">
					{translate key="reviewer.paper.table.order"}
				</td>
				<td width="30%">
					{translate key="user.role.reviewer"}
				</td>
				<td width="11%">
					{translate key="reviewer.paper.schedule.due"}
				</td>
				<td width="8%">
					{translate key="submission.reviewForm"}
				</td>
				<td width="25%">
					{translate key="reviewer.paper.recommendation"}
				</td>
				<td width="10%">
					{translate key="reviewer.paper.status"}
				</td>
				<td width="10%" colspan="2">
				</td>
			</tr>
		</thead>
		<tbody>
	{assign var="start" value="A"|ord}
	{foreach from=$reviewAssignments item=reviewAssignment key=reviewKey}
	{assign var="reviewId" value=$reviewAssignment->getId()}

  {* Grey out text if the file is not checked *}
  {if $stage != REVIEW_STAGE_ABSTRACT}
    {if $reviewFile}
      {if $reviewFile->getChecked() != 1}
        {assign var="greyOut" value=1}
      {/if}
    {else}
      {assign var="greyOut" value=1}
    {/if}
  {/if}

	{if not $reviewAssignment->getCancelled()}
		{assign var="reviewIndex" value=$reviewIndexes[$reviewId]}
		{* This is the trackDirector's review *}
			{if $user->getId() == $reviewAssignment->getReviewerId()}
				<tr class="owns">
			{else}
				<tr>
			{/if}
			<td>{$reviewIndex+$start|chr}</td> {* Index *}
			<td> {* Name *}
        <strong>{$reviewAssignment->getReviewerFullName()|escape}</strong>
      </td>
			<td> {* Due date *}
				{if $reviewAssignment->getDeclined()}
					{translate key="trackDirector.regrets"}
				{else}
					{*<a href="{url op="setDueDate" path=$reviewAssignment->getPaperId()|to_array:$reviewAssignment->getId()}">{if $reviewAssignment->getDateDue()}{$reviewAssignment->getDateDue()|date_format:$dateFormatShort}{else}&mdash;{/if}</a>*}
					{if $reviewAssignment->getDateDue()}{$reviewAssignment->getDateDue()|date_format:$dateFormatShort}{else}&mdash;{/if}
				{/if}
			</td>
			<td class="colRevForm"> {* Review Form *}
			<!-- This is the trackDirector's review -->
			{if $user->getId() == $reviewAssignment->getReviewerId()}
				{if $reviewFormResponses[$reviewId]} {* Responded *}
					{if $greyOut}
							<button class="button" disabled="disabled">{translate key="submission.reviewFormResponse"}</button>
					{else}
						<a href="javascript:openComments('{url op="viewReviewFormResponse" path=$submission->getPaperId()|to_array:$reviewAssignment->getId()}');" alt="{translate key="submission.reviewFormResponse"}" title="{translate key="submission.reviewFormResponse"}">
							<button class="button">{translate key="submission.reviewFormResponse"}</button>
						</a>
					{/if}
				{else} {* Not responded *}
					{if $greyOut}
						<button class="button" disabled="disabled">{translate key="submission.yourReviewFormResponse"}</button>
					{else}
						<a href="javascript:openComments('{url op="editReviewFormResponse" path=$reviewId|to_array:$reviewAssignment->getReviewFormId()}');" class="action">
							<button class="button">{translate key="submission.yourReviewFormResponse"}</button>
						</a>
					{/if}
				{/if}
      {else} {* other's review *}
				{if $greyOut}
					<button class="button" disabled="disabled">{translate key="submission.reviewFormResponse"}</button>
				{else}
					<a href="javascript:openComments('{url op="viewReviewFormResponse" path=$submission->getPaperId()|to_array:$reviewAssignment->getId()}');" class="action">
						<button class="button">{translate key="submission.reviewFormResponse"}</button>
					</a>
				{/if}
			{/if}<!-- This is the trackDirector's review -->
			</td>
			<td> {* Recommendation *}
				{* This is the trackDirector's review *}
				{if $user->getId() == $reviewAssignment->getReviewerId()}
					{* Has filled in review *}
					{if $reviewFormResponses[$reviewId]}
						{* Has filled in recommendation *}
						{if $reviewAssignment->getRecommendation() !== null && $reviewAssignment->getRecommendation() !== ''}
							{assign var="recommendation" value=$reviewAssignment->getRecommendation()}
							{translate key=$reviewerRecommendationOptions.$recommendation}<br />
							({$reviewAssignment->getDateCompleted()|date_format:$dateFormatShort})
						{else}
							<a class="action" href="{url op="enterReviewerRecommendation" paperId=$submission->getPaperId() reviewId=$reviewAssignment->getId()}">
								<button class="button">{translate key="director.paper.enterRecommendation"}</button>
							</a>
						{/if}
					{else}
						{translate key="reviewer.paper.recomendation.formFirst"}
					{/if}
				{else}
					{if $reviewAssignment->getRecommendation() !== null && $reviewAssignment->getRecommendation() !== ''}
						{assign var="recommendation" value=$reviewAssignment->getRecommendation()}
						{translate key=$reviewerRecommendationOptions.$recommendation}<br />
						({$reviewAssignment->getDateCompleted()|date_format:$dateFormatShort})
					{else}
						{translate key="common.none"}
					{/if}
				{/if}
			</td>
			<td> {* Status *}
				{assign var="reviewStatusIndex" value=$reviewAssignment->getReviewStatus()}
				{translate key=$reviewStatusOptions[$reviewStatusIndex]}
			</td>
			<td> {* Cancel *}
				{*if $stage != REVIEW_STAGE_ABSTRACT*}
  					{if not $reviewAssignment->getDateNotified()}
  						<a href="{url op="clearReview" path=$submission->getPaperId()|to_array:$reviewAssignment->getId()}" class="action"><button class="negative button">{translate key="director.paper.clearReview"}</button></a>
  					{elseif $reviewAssignment->getDeclined() or not $reviewAssignment->getDateCompleted()}
  						<a href="{url op="cancelReview" paperId=$submission->getPaperId() reviewId=$reviewAssignment->getId()}" class="action"><button class="negative button">{translate key="director.paper.cancelReview"}</button></a>
					{elseif $isDirector}
						<a href="{url op="cancelReview" paperId=$submission->getPaperId() reviewId=$reviewAssignment->getId()}" class="action"><button class="negative button">{translate key="director.paper.cancelReview"}</button></a>
  					{/if}
        {*/if*}
			</td>
			<td> {* Remind *}
				{if $user->getId() != $reviewAssignment->getReviewerId()} {* Not actual user's review *}
					{if $reviewAssignment->getDateCompleted()}
						<button class="button" disabled="disabled">{translate key="reviewer.paper.sendReminder"}</button>
					{else}
						<a href="{url op="remindReviewer" paperId=$submission->getPaperId() reviewId=$reviewAssignment->getId()}" class="action">
							<button class="button">{translate key="reviewer.paper.sendReminder"}</button>
						</a>
					{/if}
				{/if}
			</td>
		</tr>
		{*
		{if $reviewAssignment->getDateNotified() && !$reviewAssignment->getDeclined() && $rateReviewerOnQuality}
			<tr valign="top">
				<td class="label">{translate key="director.paper.rateReviewer"}</td>
				<td>
					<form method="post" action="{url op="rateReviewer"}">
					<input type="hidden" name="reviewId" value="{$reviewAssignment->getId()}" />
					<input type="hidden" name="paperId" value="{$submission->getPaperId()}" />
					{translate key="director.paper.quality"}&nbsp;
					<select name="quality" size="1" class="selectMenu">
						{html_options_translate options=$reviewerRatingOptions selected=$reviewAssignment->getQuality()}
					</select>&nbsp;&nbsp;
					<input type="submit" value="{translate key="common.record"}" class="button" />
					{if $reviewAssignment->getDateRated()}
						&nbsp;&nbsp;{$reviewAssignment->getDateRated()|date_format:$dateFormatShort}
					{/if}
				</form>
				</td>
			</tr>
		{/if}
		*}
	{/if}
	{/foreach}
	</tbody>
	</table>
	</div>
</div>
{/if}