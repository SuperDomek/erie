<?php

/**
 * @file DirectorSubmission.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DirectorSubmission
 * @ingroup submission
 * @see DirectorSubmissionDAO
 *
 * @brief DirectorSubmission class.
 */

//$Id$

import('submission.trackDirector.TrackDirectorSubmission');

class DirectorSubmission extends TrackDirectorSubmission {

	/**
	 * Constructor.
	 */
	function DirectorSubmission() {
		parent::TrackDirectorSubmission();
	}

	/**
	 * Return string of trackDirectors' names, separated by the specified token
	 * @param $lastOnly boolean return list of lastnames only (default false)
	 * @param $separator string separator for names (default comma+space)
	 * @return string
	 */
	function getTrackDirectorString($lastOnly = false, $separator = ', ') {
		$str = '';
		$editAssignments = $this->getEditAssignments();
		foreach ($editAssignments as $assignment) {
			if (!empty($str)) {
				$str .= $separator;
			}
			$str .= $lastOnly ? $assignment->getDirectorLastName() : $assignment->getDirectorFullName();
		}
		return $str;
	}
}

?>
