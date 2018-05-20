<?php

/**
 * @file PaperReportDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class PaperReportDAO
 * @ingroup plugins_reports_paper
 * @see PaperReportPlugin
 *
 * @brief Paper report DAO
 *
 */

// $Id$


import('submission.common.Action');
import('db.DBRowIterator');

class PaperReportDAO extends DAO {
	/**
	 * Get the paper report data.
	 * @param $conferenceId int
	 * @param $schedConfId int
	 * @return array
	 */
	function getPaperReport($conferenceId, $schedConfId) {
		$primaryLocale = AppLocale::getPrimaryLocale();
		$locale = AppLocale::getLocale();
		$paperTypeDao =& DAORegistry::getDAO('PaperTypeDAO'); // Load constants

		$result =& $this->retrieve(
			'SELECT	p.status AS status,
				p.start_time AS start_time,
				p.end_time AS end_time,
				pp.room_id AS room_id,
				p.paper_id AS paper_id,
				p.user_id AS user_id,
				p.comments_to_dr as comments,
				COALESCE(psl1.setting_value, pspl1.setting_value) AS title,
				COALESCE(psl2.setting_value, pspl2.setting_value) AS abstract,
				COALESCE(psl3.setting_value, pspl3.setting_value) AS sponsor,
				COALESCE(tl.setting_value, tpl.setting_value) AS track_title,
				p.language AS language,
				p.current_stage AS stage,
				p.pages AS pages,
				p.editing AS editing
			FROM	papers p
				LEFT JOIN published_papers pp ON (p.paper_id = pp.paper_id)
				LEFT JOIN paper_settings pspl1 ON (pspl1.paper_id=p.paper_id AND pspl1.setting_name = ? AND pspl1.locale = ?)
				LEFT JOIN paper_settings psl1 ON (psl1.paper_id=p.paper_id AND psl1.setting_name = ? AND psl1.locale = ?)
				LEFT JOIN paper_settings pspl2 ON (pspl2.paper_id=p.paper_id AND pspl2.setting_name = ? AND pspl2.locale = ?)
				LEFT JOIN paper_settings psl2 ON (psl2.paper_id=p.paper_id AND psl2.setting_name = ? AND psl2.locale = ?)
				LEFT JOIN paper_settings pspl3 ON (pspl3.paper_id=p.paper_id AND pspl3.setting_name = ? AND pspl3.locale = ?)
				LEFT JOIN paper_settings psl3 ON (psl3.paper_id=p.paper_id AND psl3.setting_name = ? AND psl3.locale = ?)
				LEFT JOIN paper_settings pti ON (pti.paper_id=p.paper_id AND pti.setting_name = ?)
				LEFT JOIN controlled_vocabs cv ON (cv.symbolic = ? AND cv.assoc_type = ? AND cv.assoc_id = ?)
				LEFT JOIN controlled_vocab_entries cve ON (cve.controlled_vocab_id = cv.controlled_vocab_id AND pti.setting_value = cve.controlled_vocab_entry_id)
				LEFT JOIN controlled_vocab_entry_settings cvesp ON (cve.controlled_vocab_entry_id = cvesp.controlled_vocab_entry_id AND cvesp.setting_name = ? AND cvesp.locale = ?)
				LEFT JOIN controlled_vocab_entry_settings cvesl ON (cve.controlled_vocab_entry_id = cvesl.controlled_vocab_entry_id AND cvesl.setting_name = ? AND cvesl.locale = ?)
				LEFT JOIN track_settings tpl ON (tpl.track_id=p.track_id AND tpl.setting_name = ? AND tpl.locale = ?)
				LEFT JOIN track_settings tl ON (tl.track_id=p.track_id AND tl.setting_name = ? AND tl.locale = ?)
			WHERE	p.sched_conf_id = ? AND
				(p.submission_progress = 0 OR p.submission_progress = 2)
			GROUP BY p.paper_id
			ORDER BY p.paper_id',
			array(
				'title', $primaryLocale, // Paper title
				'title', $locale,
				'abstract', $primaryLocale, // Paper abstract
				'abstract', $locale,
				'sponsor', $primaryLocale, // Paper sponsors
				'sponsor', $locale,
				'sessionType', // Paper type (controlled vocab)
				PAPER_TYPE_SYMBOLIC,
				ASSOC_TYPE_SCHED_CONF,
				$schedConfId,
				'description', // Paper type (primary locale)
				$primaryLocale,
				'description', // Paper type (current locale)
				$locale,
				'title', $primaryLocale, // Track title
				'title', $locale,
				$schedConfId
			)
		);
		$papersReturner = new DBRowIterator($result);
		unset($result);

		// Fetching decisions
		$result =& $this->retrieve(
			'SELECT	MAX(ed.date_decided) AS date_decided,
				ed.paper_id AS paper_id
			FROM edit_decisions ed,
				papers p
			WHERE	p.sched_conf_id = ? AND
				p.submission_progress = 0 AND
				p.paper_id = ed.paper_id
			GROUP BY p.paper_id, ed.paper_id',
			array($schedConfId)
		);
		$decisionDatesIterator = new DBRowIterator($result);
		unset($result);

		$decisionsReturner = array();
		while ($row =& $decisionDatesIterator->next()) {
			// EDIT pair stage of the decision and current decision so the query returns only current decisions
			$result =& $this->retrieve(
				'SELECT	d.decision AS decision,
					d.paper_id AS paper_id
				FROM	edit_decisions d,
					papers p
				WHERE	d.date_decided = ? AND
					d.paper_id = p.paper_id AND
					d.stage = p.current_stage AND 
					(p.submission_progress = 0 OR p.submission_progress = 2) AND
					p.paper_id = ?',
				array(
					$row['date_decided'],
					$row['paper_id']
				)
			);
			$decisionsReturner[] = new DBRowIterator($result);
			unset($result);
		}

		// Fetching authors
		$paperDao =& DAORegistry::getDAO('PaperDAO');
		$papers =& $paperDao->getPapersBySchedConfId($schedConfId);
		$authorsReturner = array();
		$index = 1;
		while ($paper =& $papers->next()) {
			// EDIT Concatenated name into one field instead of three
			$result =& $this->retrieve(
				'SELECT	CONCAT(pa.first_name, IF(pa.middle_name, " " + pa.middle_name + " ", " "), pa.last_name) AS name,
					pa.email AS email,
					pa.affiliation AS affiliation,
					pa.country AS country,
					pa.url AS url,
					COALESCE(pasl.setting_value, pas.setting_value) AS biography
				FROM	paper_authors pa
					JOIN papers p ON pa.paper_id=p.paper_id
					LEFT JOIN paper_author_settings pas ON (pa.author_id=pas.author_id AND pas.setting_name = ? AND pas.locale = ?)
					LEFT JOIN paper_author_settings pasl ON (pa.author_id=pasl.author_id AND pasl.setting_name = ? AND pasl.locale = ?)
				WHERE	p.sched_conf_id = ? AND
					(p.submission_progress = 0 OR p.submission_progress = 2) AND
					p.paper_id = ?
				ORDER BY pa.primary_contact DESC, pa.seq',
				array(
					'biography',
					$primaryLocale,
					'biography',
					$locale,
					$schedConfId,
					$paper->getId()
				)
			);
			$authorIterator = new DBRowIterator($result);
			unset($result);
			$authorsReturner[$paper->getId()] = $authorIterator;
			unset($authorIterator);
			$index++;
			unset($paper);
		}

		return array($papersReturner, $authorsReturner, $decisionsReturner);
	}
}

?>
