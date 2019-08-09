<?php

/**
 * @file FacturationReportDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class FacturationReportDAO
 * @ingroup plugins_reports_facturation
 * @see FacturationReportPlugin
 *
 * @brief Facturation report DAO
 *
 */

// $Id$


import('db.DBRowIterator');

class FacturationReportDAO extends DAO {
	/**
	 * Get the registrant to invoice report data.
	 * @param $conferenceId int
	 * @param $schedConfId int
	 * @return array
	 */
	function getFacturationReport($conferenceId, $schedConfId) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();

		$result =& $this->retrieve(
			'SELECT
				r.registration_id AS registration_id,
				r.user_id AS userid,
				u.username AS uname,
				u.first_name AS fname,
				u.middle_name AS mname,
				u.last_name AS lname,
				u.affiliation AS affiliation,
				u.email AS email,
				u.phone AS phone,
				u.billing_address AS billing_address,
				u.company_id AS companyid,
				u.vat_reg_no AS vatregno,
				u.country AS country,
				rtsl.setting_value AS type,
				r.date_registered AS regdate,
				r.date_paid AS paiddate,
				r.special_requests AS specialreq,
				p.paper_id AS paperid,
				p.pages AS pages,
				p.editing AS editing,
				p.status AS status
			FROM
				registrations r
					JOIN users u ON r.user_id=u.user_id
					LEFT JOIN registration_type_settings rtsl ON (r.type_id=rtsl.type_id AND rtsl.locale=? AND rtsl.setting_name=?)
					LEFT JOIN papers p ON (r.user_id=p.user_id AND r.sched_conf_id = p.sched_conf_id)
			WHERE
				r.sched_conf_id = ?
				AND r.type_id <> 0
				AND (p.status is null OR p.status BETWEEN 2 AND 3)
			UNION
			SELECT
				r.registration_id AS registration_id,
				u.user_id AS userid,
				u.username AS uname,
				u.first_name AS fname,
				u.middle_name AS mname,
				u.last_name AS lname,
				u.affiliation AS affiliation,
				u.email AS email,
				u.phone AS phone,
				u.billing_address AS billing_address,
				u.company_id AS companyid,
				u.vat_reg_no AS vatregno,
				u.country AS country,
				rtsl.setting_value AS type,
				r.date_registered AS regdate,
				r.date_paid AS paiddate,
				r.special_requests AS specialreq,
				p.paper_id AS paperid,
				p.pages AS pages,
				p.editing AS editing,
				p.status AS status
			FROM
				registrations r
					LEFT JOIN registration_type_settings rtsl ON (r.type_id=rtsl.type_id AND rtsl.locale=? AND rtsl.setting_name=?)
					RIGHT JOIN papers p ON (r.user_id=p.user_id AND r.sched_conf_id = p.sched_conf_id)
					JOIN users u ON p.user_id=u.user_id
			WHERE
				p.sched_conf_id = ?
				AND p.status BETWEEN 2 AND 3
			ORDER BY
				userid',
			array(
				$primaryLocale,
				'name',
				$schedConfId,
				$primaryLocale,
				'name',
				$schedConfId
			)
		);
		// prepare an iterator of all the registration information
		$facturationReturner = new DBRowIterator($result);

		$result =& $this->retrieve(
			'SELECT 
				r.registration_id as registration_id,
				roa.option_id as option_id
			FROM
				registrations r 
					LEFT JOIN registration_option_assoc roa ON (r.registration_id = roa.registration_id)
			WHERE 
				r.sched_conf_id = ?',
			(int) $schedConfId
		);
		
		// Prepare an array of registration Options by registration Id
		$registrationOptionDAO =& DAORegistry::getDAO('RegistrationOptionDAO');
		$iterator = new DBRowIterator($result);
		$registrationOptionReturner = array();
		while ($row =& $iterator->next()) {
			$registrationId = $row['registration_id'];
			$registrationOptionReturner[$registrationId] =& $registrationOptionDAO->getRegistrationOptions($registrationId);
		}

		return array($facturationReturner, $registrationOptionReturner);
	}
}

?>
