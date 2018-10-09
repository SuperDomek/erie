<?php

/**
 * @file FacturationReportPlugin.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Copyright (c) 2017 Dominik Bláha
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class FacturationReportPlugin
 * @ingroup plugins_reports_facturation
 * @see FacturationReportDAO
 *
 * @brief Facturation report plugin
 * @brief Returns rows for users with an accepted submission or 
 * @brief self-registered without an submission
 */

//$Id$

import('classes.plugins.ReportPlugin');

class FacturationReportPlugin extends ReportPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True if plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		if ($success) {
			$this->import('FacturationReportDAO');
			$facturationReportDAO = new FacturationReportDAO();
			DAORegistry::registerDAO('FacturationReportDAO', $facturationReportDAO);
		}
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'FacturationReportPlugin';
	}

	function getDisplayName() {
		return __('plugins.reports.facturation.displayName');
	}

	function getDescription() {
		return __('plugins.reports.facturation.description');
	}

	function display(&$args) {
		$conference =& Request::getConference();
		$schedConf =& Request::getSchedConf();
		AppLocale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OCS_MANAGER));

		header('content-type: text/csv; charset=utf-8');
		header('content-disposition: attachment; filename=facturation_report.csv');

		$facturationReportDao =& DAORegistry::getDAO('FacturationReportDAO');
		list($registrants, $registrantOptions) = $facturationReportDao->getFacturationReport(
			$conference->getId(),
			$schedConf->getId()
		);
				
		$columns = array(
			'userid' => __('plugins.reports.facturation.userid'),
			'uname' => __('user.username'),
			'fname' => __('user.firstName'),
			'lname' => __('user.lastName'),
			'affiliation' => __('user.affiliation'),
			'email' => __('user.email'),
			'phone' => __('user.phone'),
			'billing_address' => __('common.billingAddress'),
			'companyid' => __('common.companyId'),
			'vatregno' => __('common.VATRegNo'),
			'country' => __('common.country'),
			'type' => __('manager.registration.registrationType')
		);
		
		$registrationOptionDAO =& DAORegistry::getDAO('RegistrationOptionDAO');
		$registrationOptions =& $registrationOptionDAO->getRegistrationOptionsBySchedConfId($schedConf->getId());
		
		// column name = 'option' + optionId => column value = name of the registration option
		while ($registrationOption =& $registrationOptions->next()) {
			$registrationOptionIds[] = $registrationOption->getOptionId();
			$columns = array_merge($columns, array('option' . $registrationOption->getOptionId() => $registrationOption->getRegistrationOptionName()));
			unset($registrationOption);
		} 
		
		$columns = array_merge($columns, array(
			'regdate' => __('manager.registration.dateRegistered'),
			'paiddate' => __('manager.registration.datePaid'),
			'source' => __('plugins.reports.facturation.source'),
			'paperid' => __('paper.submissionId'),
			'pages' => __('paper.pages'),
			'editing' => __('paper.editing'),
			'status' => __('common.status')
			));

		//EDIT Add BOM
		$BOM = "\xEF\xBB\xBF";
		$fp = fopen('php://output', 'wt');
		fwrite($fp, $BOM);
		String::fputcsv($fp, array_values($columns), ";");

		while ($row =& $registrants->next()) {
			if ( isset($registrantOptions[$row['registration_id']]) ) {
				$options = $this->mergeRegistrantOptions($registrationOptionIds, $registrantOptions[$row['registration_id']]);
			} else {
				$options = $this->mergeRegistrantOptions($registrationOptionIds);
			}
			foreach ($columns as $index => $junk) {
				if (isset($row[$index])) {
					if ($index == 'affiliation'){
						$withoutCRLF = str_replace(array("\r\n", "\n\r", "\n", "\r"), ", ", $row[$index]);
						$columns[$index] = html_entity_decode(strip_tags($withoutCRLF), ENT_QUOTES, 'UTF-8');
					}
					elseif ($index == 'billing_address') {
						$withoutCRLF = str_replace(array("\r\n", "\n\r", "\n", "\r"), ", ", $row[$index]);
						$columns[$index] = html_entity_decode(strip_tags($withoutCRLF), ENT_QUOTES, 'UTF-8');
					}
					else if ($index == 'regdate' || $index == 'paiddate')
						$columns[$index] = $facturationReportDao->dateFromDB($row[$index]);
					else if ($index == 'status'){
						if ($row[$index] == 2)
							$columns[$index] = __('submissions.layout');
						else if ($row[$index] == 3)
							$columns[$index] = __('submissions.published');
						else if ($row[$index] == 'None')
							$columns[$index] = "None";
					}
					elseif ($index == 'editing'){
						if($row[$index] == 1)
							$columns[$index] = __('common.yes');
						else if($row[$index] == 'None')
							$columns[$index] = "None";
						else{
							$columns[$index] = __('common.no');
						}
					}
					else
						$columns[$index] = $row[$index];
				} else if (isset($options[$index])) {
					$columns[$index] = $options[$index];
				} else {
					$columns[$index] = '';}
			}
			// EDIT Change delimiter to ;
			String::fputcsv($fp, $columns, ";");
			unset($row);
		}
		fclose($fp);
	}

	
	/**
	 * Make a single array of "Yes"/"No" for each option id
	 * @param $registrationOptionIds array list of Option Ids for a given schedConfId
	 * @param $registrantOptions array list of Option Ids for a given Registrant
	 * @return array
	 */
	function mergeRegistrantOptions($registrationOptionIds, $registrantOptions = array()) {
		$returner = array();
		if(isset($registrationOptionIds)){
			foreach ( $registrationOptionIds as $id ) { 
				$returner['option'. $id] = ( in_array($id, $registrantOptions) )?__('common.yes'):__('common.no');
			}
		}
		return $returner;
	}
}

?>
