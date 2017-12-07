{**
 * footer.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site footer.
 *
 *}
{if $displayCreativeCommons}
{translate key="common.ccLicense"}
{/if}
<div id="footer">
{if $pageFooter}
  <p>
    {$pageFooter}
  </p>
{/if}
{call_hook name="Templates::Common::Footer::PageFooter"}
</div><!-- footer -->
</div><!-- content -->
</div><!-- main -->
</div><!-- body -->

{get_debug_info}
{if $enableDebugStats}{include file=$pqpTemplate}{/if}

</div><!-- container -->
</body>
</html>
