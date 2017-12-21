{**
 * peerReview.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the author's peer review table.
 *
 * $Id$
 *}

{literal}
<script type="text/javascript">
<!--
function revealHide(obj){
	var messageShow = '{/literal}{translate key="paper.previousRounds.show"}{literal}';
	var messageHide = '{/literal}{translate key="paper.previousRounds.hide"}{literal}';
	if(obj.style.visibility == 'collapse' || obj.style.visibility == ''){
		obj.style.visibility = 'visible';
		document.getElementById('hideButton').innerHTML = messageHide;
	}
	else{
		obj.style.visibility = 'collapse';
		document.getElementById('hideButton').innerHTML = messageShow;
	}
};

function showRounds(){
	var rounds = document.getElementsByClassName("previousRounds");
	for (var i = 0; i < rounds.length; i++){
		revealHide(rounds[i]);
	}
};
// -->
</script>
{/literal}

<div id="peerReview">

{if ($stage == $smarty.const.REVIEW_STAGE_PRESENTATION && $submission->getCurrentStage() != $smarty.const.REVIEW_STAGE_PRESENTATION)}
	{assign var="isStageDisabled" value=true}
{/if}

{if $submission->getReviewMode() == $smarty.const.REVIEW_MODE_BOTH_SIMULTANEOUS}
  <h3>{translate key="submission.review"}</h3>
{elseif $stage == $smarty.const.REVIEW_STAGE_ABSTRACT}
  <h3>{translate key="submission.abstractReview"}</h3>
{else}{* REVIEW_STAGE_PRESENTATION *}
  <h3>{translate key="submission.paperReview"}</h3>
{/if}

<div class="tbl-container">
<table class="data" width="100%">
{if $isStageDisabled}
  <tr>
		<td><span class="instruct">{translate key="author.paper.stageDisabled"}</span></td>
	</tr>
</table>
{else}
  {if $stage > $smarty.const.REVIEW_STAGE_PRESENTATION}
    <button type="button" id="hideButton" onclick="showRounds();">{translate key="paper.previousRounds.show"}</button>
  {/if}
  
  {foreach from=$reviewAssignments item=reviewAssignmentsStage key=stageTemp}
    {assign var=reviewIndexesStage value=$reviewIndexes[$stageTemp]}
    {if $stage == $smarty.const.REVIEW_STAGE_ABSTRACT && $stageTemp < $smarty.const.REVIEW_STAGE_PRESENTATION}
      {* Abstract review *}
      {assign var=start value="A"|ord}
      {assign var=authorFiles value=$submission->getAuthorFileRevisions($stage)}
      {assign var="directorFiles" value=$submission->getDirectorFileRevisions($stage)}
      {assign var="viewableFiles" value=$authorViewableFilesByStage[$stage]}

      <tr valign="top">
        <td class="label" width="20%">
          {translate key="submission.initiated"}
        </td>
        <td class="value" width="80%">
          {if $reviewEarliestNotificationByStage[$stageTemp]}
            {$reviewEarliestNotificationByStage[$stageTemp]|date_format:$dateFormatShort}
          {else}
            &mdash;
          {/if}
        </td>
      </tr>
      <tr valign="top">
        <td class="label" width="20%">
          {translate key="submission.lastModified"}
        </td>
        <td class="value" width="80%">
          {if $reviewModifiedByStage[$stageTemp]}
            {$reviewModifiedByStage[$stageTemp]|date_format:$dateFormatShort}
          {else}
            &mdash;
          {/if}
        </td>
      </tr>

      {assign var="start" value="A"|ord}
      {foreach from=$reviewAssignments[$stageTemp] item=reviewAssignment key=reviewKey}
        {assign var="reviewId" value=$reviewAssignment->getId()}
        {if not $reviewAssignment->getCancelled()}
          <tr>
            {assign var="reviewIndex" value=$reviewIndexesStage[$reviewId]}
            <td class="label" width="20%">
              <h5>{translate key="user.role.reviewer"} {$reviewIndex+$start|chr}</h5>
            </td>
            <td class="value" width="80%">
              {if $reviewAssignment->getRecommendation() !== null && $reviewAssignment->getRecommendation() !== ''}
                {assign var="recommendation" value=$reviewAssignment->getRecommendation()}
                {translate key=$reviewerRecommendationOptions.$recommendation}
                &nbsp;&nbsp;&nbsp;&nbsp;
                <a href="javascript:openComments('{url op="viewReviewFormResponse" path=$submission->getPaperId()|to_array:$reviewAssignment->getId()}');" class="icon">{icon name="letter"}</a>
              {else}
                {translate key="common.none"}
              {/if}
            </td>
          </tr>
        {/if}
      {foreachelse}
        <tr>
          <td colspan="2">
            {translate key="common.noneAssigned"}
          </td>
        </tr>
      {/foreach}
    {elseif $stage >= $smarty.const.REVIEW_STAGE_PRESENTATION && $stageTemp >= $smarty.const.REVIEW_STAGE_PRESENTATION}
      {* Paper review *}
      {if $stage != $stageTemp}
        <tbody class="previousRounds">
      {else}
        <tbody>
      {/if}
      {assign var=start value="A"|ord}
      {assign var=authorFiles value=$submission->getAuthorFileRevisions($stage)}
      <tr valign="top">
        <td class="value" colspan="2">
          <h4>{translate key="submission.stage" stage=$stageTemp-1}</h4>
        </td>
      </tr>

      <tr valign="top">
        <td class="label" width="20%">
          {translate key="submission.initiated"}
        </td>
        <td class="value" width="80%">
          {if $reviewEarliestNotificationByStage[$stageTemp]}
            {$reviewEarliestNotificationByStage[$stageTemp]|date_format:$dateFormatShort}
          {else}
            &mdash;
          {/if}
        </td>
      </tr>

      <tr valign="top">
        <td class="label" width="20%">
          {translate key="submission.lastModified"}
        </td>
        <td class="value" width="80%">
          {if $reviewModifiedByStage[$stageTemp]}
            {$reviewModifiedByStage[$stageTemp]|date_format:$dateFormatShort}
          {else}
            &mdash;
          {/if}
        </td>
      </tr>
      
      <tr valign="top">
        <td class="label" width="20%">
          {translate key="submission.reviewVersion"}
        </td>
        <td class="value" width="80%">
          {assign var="reviewFile" value=$reviewFilesByStage[$stageTemp]}
          {if $reviewFile}
            {if $reviewFile->getChecked() == 1}
              <a href="{url op="downloadFile" path=$submission->getPaperId()|to_array:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file">{$reviewFile->getFileName()|escape}</a>&nbsp;&nbsp;{$reviewFile->getDateModified()|date_format:$dateFormatShort}
            {else}
              {translate key="submission.fileNotChecked"}
            {/if}
          {else}
            {translate key="common.none"}
          {/if}
        </td>
      </tr>

      {foreach from=$reviewAssignments[$stageTemp] item=reviewAssignment key=reviewKey}
        {assign var="reviewId" value=$reviewAssignment->getId()}
        {if not $reviewAssignment->getCancelled()}
        <tr>
          {assign var="reviewIndex" value=$reviewIndexesStage[$reviewId]}
          <td class="label" width="20%">
            <strong>{translate key="user.role.reviewer"} {$reviewIndex+$start|chr}</strong>
          </td>
          <td class="value" width="80%">
            {if $reviewAssignment->getRecommendation() !== null && $reviewAssignment->getRecommendation() !== ''}
              {assign var="recommendation" value=$reviewAssignment->getRecommendation()}
              {translate key=$reviewerRecommendationOptions.$recommendation|escape}
              &nbsp;&nbsp;&nbsp;&nbsp;
              <a href="javascript:openComments('{url op="viewReviewFormResponse" path=$submission->getPaperId()|to_array:$reviewAssignment->getId()}');" class="icon">{icon name="letter"}</a>
            {else}
              {translate key="common.none"}
            {/if}
          </td>
        </tr>
        {/if}
      {foreachelse}
        <tr>
          <td colspan="2">
            {translate key="common.noneAssigned"}
          </td>
        </tr>
      {/foreach}
    </tbody>
    {/if}
  {/foreach}
  </table>
</div>
{/if}
</div>
