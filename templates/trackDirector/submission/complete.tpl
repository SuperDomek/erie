{**
 * complete.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the "complete" button for submissions.
 *
 * $Id$
 *}
<div id="complete">
<h3>{translate key="submission.complete"}</h3>
{if $submission->getStatus() == STATUS_LAYOUT || $submission->getStatus() == STATUS_PUBLISHED}
	{assign var="complete" value=1}
{else}
	{assign var="complete" value=0}
{/if}
<form method="post" action="{url op="completePaper"}">
	<input type="hidden" name="paperId" value="{$submission->getPaperId()}" />
	<p>
	<label for="pages">{translate key="submission.complete.pages"}</label>
	<input name="pages" id="pages" type="number" min="0" max="20" {if $complete}disabled="disabled" {/if} {if $submission->getPages()}value="{$submission->getPages()}"{else}value="0"{/if}/>
	</p>
	<p>
	<label for="editing">{translate key="submission.complete.editing"}</label>
	<input name="editing" id="editing" type="checkbox" {if $complete}disabled="disabled" {/if} {if $submission->getEditing()}checked="checked"{/if}/>
	</p>
	<p>{translate key="submission.complete.description"}</p>
	<input name="complete" {if $complete}disabled="disabled" {/if}type="submit" value="{translate key="submission.complete"}" class="button" />
	<input name="remove" {if !$complete}disabled="disabled" {/if}type="submit" value="{translate key="common.remove"}" class="button" />
</form>
</div>
