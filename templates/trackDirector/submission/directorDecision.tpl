{**
 * directorDecision.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the director decision table.
 *
 * $Id$
 *}

{literal}
<script type="text/javascript">
<!--
// shows decision comment box if decision is revisions
function showCommentBox(sel) {
	var selected = sel.options[sel.selectedIndex];
	var commentBox = document.getElementById("decision_comment");
	if(selected.value == "{/literal}{$smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS}{literal}" ||
	selected.value == "{/literal}{$smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS}{literal}" ||
	selected.value == "{/literal}{$smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS}{literal}"){
    commentBox.style.display = "table-row";
		document.getElementById("decision_submit").disabled = "disabled"; // turn off the submit button
		// turn on submit after at least 10 chars submitted to the comment box
		$('#comment_text').live('input',function() {
			if (String($(this).val()).length >= 10) {
				document.getElementById("decision_submit").disabled = "";
			}
			else {
				document.getElementById("decision_submit").disabled = "disabled";
			}
		});
	}
  else {
		document.getElementById("decision_submit").disabled = "";
    commentBox.style.display = "none";
  }
}

function confirmDecision(sel){
	var reviewsComplete = "{/literal}{$completeReviews}{literal}";
	if (reviewsComplete) {
		return confirm('{/literal}{translate|escape:"jsparam" key="director.submissionReview.confirmDecision"}{literal}');
	}
	else {
		return confirm('{/literal}{translate|escape:"jsparam" key="director.submissionReview.confirmDecisionReviewsOpen"}{literal}');
	}
}
// -->
</script>
{/literal}

<div id="directorDecision" class="blockable">
<h3>{translate key="submission.directorDecision"}</h3>

<form method="post" action="{url op="recordDecision" path=$stage}" id="form1">

<span>
<input type="hidden" name="paperId" value="{$submission->getPaperId()}" />
{assign var=availableDirectorDecisionOptions value=$submission->getDirectorDecisionOptions($currentSchedConf,$stage)}
{assign var=lastDecision value=$directorDecisions[0].decision}
{if $lastDecision} {* Different text and look for user when decision entered *}
	<select name="decision" id="decision" size="1" class="selectMenu lastDecision"{if not $allowRecommendation} disabled="disabled"{/if} onchange="showCommentBox(this);">
		{html_options_translate options=$availableDirectorDecisionOptions selected=$lastDecision strict=1}
	</select>
	<button type="submit" form="form1" id="decision_submit" {if $stage != $smarty.const.REVIEW_STAGE_ABSTRACT}onclick="return confirmDecision(this);"{/if} name="submit" value="Submit" {if not $allowRecommendation}disabled="disabled"{/if} class="button">{translate key="director.paper.changeDecision"}</button>
{else}
	<select name="decision" id="decision" size="1" class="selectMenu"{if not $allowRecommendation} disabled="disabled"{/if} onchange="showCommentBox(this);">
		{html_options_translate options=$availableDirectorDecisionOptions selected=$lastDecision strict=1}
	</select>
	<button type="submit" form="form1" id="decision_submit" {if $stage != $smarty.const.REVIEW_STAGE_ABSTRACT}onclick="return confirmDecision(this);"{/if} name="submit" value="Submit" {if not $allowRecommendation}disabled="disabled"{/if} class="button">{translate key="director.paper.recordDecision"}</button>
{/if}
{if $isDirector}
	{if $lastDecision == SUBMISSION_DIRECTOR_DECISION_DECLINE}
    {if $submission->getStatus() == STATUS_ARCHIVED}
			{translate key="submissions.archived"}
		{else}
			<a href="{url op="archiveSubmission" path=$submission->getPaperId()}" onclick="return window.confirm('{translate|escape:"jsparam" key="director.submissionReview.confirmToArchive"}')">
			<button class="button">{translate key="director.paper.sendToArchive"}</button></a>
		{/if}
    {if $submission->getDateToArchive()}
			{$submission->getDateToArchive()|date_format:$dateFormatShort}
		{/if}
	{/if}
{/if}
{if not $allowRecommendation and $isCurrent}<br />{translate key="director.paper.cannotRecord"}{/if}
</span>

<ul class="no-list">
	<li id="decision_comment" class="hidden"> <!--class="hidden"-->
		<label for="comment_text" class="error">{translate key="director.paper.decisionComment"}</label>
    <textarea name="comment_text" id="comment_text" rows="5" cols="40" class="textArea"></textarea>
		
  </li>
	{assign var=decisionsCount value=$directorDecisions|@count}
	{if $decisionsCount >= 1}
		<li>
			{foreach from=$directorDecisions item=directorDecision key=decisionKey}
				{if $decisionKey neq 0} <br /> {/if}
				{assign var=decision value=$directorDecision.decision}
				{if $decisionKey == 0} {* First array field contains the last decision*}
					<strong><header>{$directorDecision.dateDecided|date_format:$dateFormatShort}</header>
					{translate key=$directorDecisionOptions.$decision}</strong>
				{else}
					<header>{$directorDecision.dateDecided|date_format:$dateFormatShort}</header>
					{translate key=$directorDecisionOptions.$decision}
				{/if}
			{foreachelse}
				{translate key="common.none"}
			{/foreach}
		</li>
	{/if}

</ul>
</form>


{assign var=authorFiles value=$submission->getAuthorFileRevisions($stage)}
{assign var=directorFiles value=$submission->getDirectorFileRevisions($stage)}
{assign var=reviewFile value=$submission->getReviewFile()}
{assign var="authorRevisionExists" value=false}
{assign var="directorRevisionExists" value=false}
{assign var="sendableVersionExists" value=false}


  {if $lastDecision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS ||
      $lastDecision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS ||
			$lastDecision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS}
	<p><h5>{translate key="submission.directorDecisionComment"}</h5>
	<span>{$decisionComment|escape}</span>
	</p>
  {/if}
</div>
{if $isFinalReview}

	<div class="separator"></div>

	{include file="trackDirector/submission/complete.tpl"}

	<div class="separator"></div>
	{if $isDirector}
		{include file="trackDirector/submission/layout.tpl"}
	{/if}
{/if}
