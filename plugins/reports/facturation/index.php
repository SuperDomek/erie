<?php

/**
 * @defgroup plugins_reports_facturation
 */
 
/**
 * @file index.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Wrapper for facturation report plugin.
 *
 * @ingroup plugins_reports_facturation
 */

//$Id$

require_once('FacturationReportPlugin.inc.php');

return new FacturationReportPlugin();

?>
