{**
 * summary.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the author's submission summary table.
 *
 * $Id$
 *}
{assign var="paperId" value=$submission->getPaperId()}
{assign var="currentStage" value=$submission->getCurrentStage()}
{assign var="submissionProgress" value=$submission->getSubmissionProgress()}
{assign var="status" value=$submission->getSubmissionStatus()}

<div id="submission">

<ul class="no-list">
<li>
  <header>{translate key="paper.authors"}</header>
  {$submission->getAuthorString()|escape}
</li>
<li>
  <header>{translate key="paper.title"}</header>
  {$submission->getLocalizedTitle()|strip_unsafe_html}
</li>
<li>
  <header>{translate key="common.status"}</header>
  {if $submissionProgress == 0}
  {if $status == STATUS_QUEUED_UNASSIGNED}
    <span class="warning">{translate key="submissions.queuedUnassigned"}</span>
  {elseif $status == STATUS_QUEUED_REVIEW}
    {assign var=decision value=$submission->getMostRecentDecision()}
    {if $currentStage>=REVIEW_STAGE_PRESENTATION}
      {if $submission->getAuthorFileRevisions($submission->getCurrentStage())}
        <span>{translate key="author.submissions.queuedPaperReviewRevisions.uploaded"}</span>
      {elseif $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS ||
      $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS ||
      $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS}
        <span>{translate key="author.submissions.queuedPaperReviewRevisions"}</span>
      {else}
        <span>{translate key="submissions.queuedPaperReview"}</span>
      {/if}
    {else}  
        {if $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS ||
        $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS ||
        $decision == $smarty.const.SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS}
          <span class="warning">{translate key="author.submissions.queuedAbstractReviewRevisions"}</span>
          <a href="{url op="viewMetadata" path=$submission->getPaperId()}" class="action">
            <button type="button">{translate key="author.submissions.editAbstract"}</button>
          </a>
        {else}
          <span class="warning">{translate key="submissions.queuedAbstractReview"}</span>
        {/if}
      
    {/if}
  {elseif $status == STATUS_QUEUED_EDITING}
    <a href="{url op="submissionReview" path=$paperId|to_array}" class="action">{translate key="submissions.queuedEditing"}</a>
  {/if}
  {elseif $submissionProgress == 1}
    {translate key="submissions.incomplete"}
  {else}
    {url|assign:"submitUrl" op="submit" path=$submission->getSubmissionProgress() paperId=$paperId}
    <a class="action" href="{$submitUrl}">{translate key="submissions.pendingPresentation"}</a>
  {/if}
</li>
</ul>
</div>
