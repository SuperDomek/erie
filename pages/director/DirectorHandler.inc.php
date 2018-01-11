<?php

/**
 * @file DirectorHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DirectorHandler
 * @ingroup pages_director
 *
 * @brief Handle requests for director functions.
 *
 */

// $Id$


import('trackDirector.TrackDirectorHandler');

define('DIRECTOR_TRACK_HOME', 0);
define('DIRECTOR_TRACK_SUBMISSIONS', 1);
define('DIRECTOR_TRACK_MANAGEMENT', 2);

// Filter director
define('FILTER_DIRECTOR_ALL', 0);
define('FILTER_DIRECTOR_ME', 1);

import ('submission.director.DirectorAction');
import('handler.validation.HandlerValidatorRoles');

class DirectorHandler extends TrackDirectorHandler {
	/**
	 * Constructor
	 **/
	function DirectorHandler() {
		parent::TrackDirectorHandler();

		$this->addCheck(new HandlerValidatorConference($this));
		$this->addCheck(new HandlerValidatorSchedConf($this));
		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_DIRECTOR)));
	}

	/**
	 * Displays the director role selection page.
	 */

	function index($args) {
		$this->validate();
		$this->setupTemplate(DIRECTOR_TRACK_HOME);

		$templateMgr =& TemplateManager::getManager();
		$schedConf =& Request::getSchedConf();
		$directorSubmissionDao =& DAORegistry::getDAO('DirectorSubmissionDAO');
		$submissionsCount =& $directorSubmissionDao->getDirectorSubmissionsCount($schedConf->getId());
		$templateMgr->assign('submissionsCount', $submissionsCount);
		$templateMgr->assign('helpTopicId', 'editorial.directorsRole');
		$templateMgr->display('director/index.tpl');
	}

	/**
	 * Display director submission queue pages.
	 */
	function submissions($args) {
		$this->validate();
		$this->setupTemplate(DIRECTOR_TRACK_SUBMISSIONS);

		$schedConf =& Request::getSchedConf();
		$schedConfId = $schedConf->getId();
		$user =& Request::getUser();

		$directorSubmissionDao =& DAORegistry::getDAO('DirectorSubmissionDAO');
		$trackDao =& DAORegistry::getDAO('TrackDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');

		$page = isset($args[0]) ? $args[0] : '';
		$export = isset($args[1]) ? $args[1] : '';
		$tracks =& $trackDao->getTrackTitles($schedConfId);

		$filterDirectorOptions = array(
			FILTER_DIRECTOR_ALL => AppLocale::Translate('director.allDirectors'),
			FILTER_DIRECTOR_ME => AppLocale::Translate('director.me')
		);

		$filterTrackOptions = array(
			FILTER_TRACK_ALL => AppLocale::Translate('director.allTracks')
		) + $tracks;

		// Get the user's search conditions, if any
		$searchField = Request::getUserVar('searchField');
		$searchMatch = Request::getUserVar('searchMatch');
		$search = Request::getUserVar('search');

		$sort = Request::getUserVar('sort');
		$sortDirection = Request::getUserVar('sortDirection');

		switch($page) {
			case 'submissionsUnassigned':
				$functionName = 'getDirectorSubmissionsUnassigned';
				$helpTopicId = 'editorial.directorsRole.submissions.unassigned';
				$sort = isset($sort) ? $sort : 'id';
				break;
			case 'submissionsAccepted':
				$functionName = 'getDirectorSubmissionsAccepted';
				$helpTopicId = 'editorial.directorsRole.submissions.presentations';
				$sort = isset($sort) ? $sort : 'seq';
				break;
			case 'submissionsArchives':
				$functionName = 'getDirectorSubmissionsArchives';
				$helpTopicId = 'editorial.directorsRole.submissions.archives';
				$sort = isset($sort) ? $sort : 'id';
				break;
			default:
				$page = 'submissionsInReview';
				$functionName = 'getDirectorSubmissionsInReview';
				$helpTopicId = 'editorial.directorsRole.submissions.inReview';
				$sort = isset($sort) ? $sort : 'id';
		}

		$filterDirector = Request::getUserVar('filterDirector');
		if ($filterDirector != '' && array_key_exists($filterDirector, $filterDirectorOptions)) {
			$user->updateSetting('filterDirector', $filterDirector, 'int', $schedConfId);
		} else {
			$filterDirector = $user->getSetting('filterDirector', $schedConfId);
			if ($filterDirector == null) {
				$filterDirector = FILTER_DIRECTOR_ALL;
				$user->updateSetting('filterDirector', $filterDirector, 'int', $schedConfId);
			}
		}

		if ($filterDirector == FILTER_DIRECTOR_ME) {
			$directorId = $user->getId();
		} else {
			$directorId = FILTER_DIRECTOR_ALL;
		}

		$filterTrack = Request::getUserVar('filterTrack');
		if ($filterTrack != '' && array_key_exists($filterTrack, $filterTrackOptions)) {
			$user->updateSetting('filterTrack', $filterTrack, 'int', $schedConfId);
		} else {
			$filterTrack = $user->getSetting('filterTrack', $schedConfId);
			if ($filterTrack == null) {
				$filterTrack = FILTER_TRACK_ALL;
				$user->updateSetting('filterTrack', $filterTrack, 'int', $schedConfId);
			}
		}

		$rangeInfo =& Handler::getRangeInfo('submissions', array($functionName, (string) $searchField, (string) $searchMatch, (string) $search));
		while (true) {
			$submissions =& $directorSubmissionDao->$functionName(
				$schedConfId,
				$filterTrack,
				$directorId,
				$searchField,
				$searchMatch,
				$search,
				null,
				null,
				null,
				$rangeInfo,
				$sort,
				$sortDirection
			);
			if ($submissions->isInBounds()) break;
			unset($rangeInfo);
			$rangeInfo =& $submissions->getLastPageRangeInfo();
			unset($submissions);
		}

		import('core.ArrayItemIterator');
		if ($sort == 'status') {
			// Sort all submissions by status, which is too complex to do in the DB
			$submissionsArray = $submissions->toArray();
			$compare = create_function('$s1, $s2', 'return strcmp($s1->getSubmissionStatus(), $s2->getSubmissionStatus());');
			usort ($submissionsArray, $compare);
			if($sortDirection == 'DESC') {
				$submissionsArray = array_reverse($submissionsArray);
			}
			// Convert submission array back to an ItemIterator class

			$submissions =& ArrayItemIterator::fromRangeInfo($submissionsArray, $rangeInfo);
		}

		// Workaround because can't access ReviewFiles through submissions in the template
		$reviewFiles;
		$tempSubmissions = $submissions->toArray();
		foreach($tempSubmissions as $submission){
			if($submission->getCurrentStage() >= 2){ //PRESENTATION STAGE
				// FIX warnings when no reviewAssignments in the stage
				$reviewAssignmentsStages = $submission->getReviewAssignments();
				if (array_key_exists($submission->getCurrentStage(), $reviewAssignmentsStages))
					$reviewAssignments = $submission->getReviewAssignments()[$submission->getCurrentStage()];
				else
					$reviewAssignments = null;
				if($reviewAssignments){
					foreach($reviewAssignments as $reviewAssignment){
						if($submission->getReviewFileId() == $reviewAssignment->getReviewFileId()){
							if(!is_null($reviewAssignment->getReviewFile()))
								$reviewFiles[$submission->getPaperId()] = (int) $reviewAssignment->getReviewFile()->getChecked();
						}
					}
				}
			}
		}

		// Need to reinitialize the $submissions object
		unset($submissions);
		while (true) {
			$submissions =& $directorSubmissionDao->$functionName(
				$schedConfId,
				$filterTrack,
				$directorId,
				$searchField,
				$searchMatch,
				$search,
				null,
				null,
				null,
				$rangeInfo,
				$sort,
				$sortDirection
			);
			if ($submissions->isInBounds()) break;
			unset($rangeInfo);
			$rangeInfo =& $submissions->getLastPageRangeInfo();
			unset($submissions);
		}
		// END Workaround
		// so far the only export format is PDF so in future you'll need to distinguish what is inside $export
		if ($export){
			try {
				$p = new PDFlib();
				/*  open new PDF file; insert a file name to create the PDF on disk */
				if ($p->begin_document("", "") == 0) {
						die("Error: " . $p->get_errmsg());
				}
				// set up the encoding for strings
				$p->set_option("stringformat=utf8");

				$p->set_info("Creator", $schedConf->getLocalizedTitle());
				$p->set_info("Author", $user->getFullName());
				$p->set_info("Title", $page);
		
				$p->begin_page_ext(0, 0, "width=a4.width height=a4.height");
				// use unicode version of font
				$font_body = $p->load_font("Helvetica", "unicode", "");
				$font_head = $p->load_font("Helvetica-Bold", "unicode", "");
		
				// document header
				$p->setfont($font_head, 15.0);
				$p->set_text_pos(50, 792);
				$p->show("Editorial manager export");
				$p->setfont($font_body, 13.0);
				$p->continue_text("Conference: ". $schedConf->getLocalizedTitle());
				$p->continue_text("Type: ". $page);
				$p->continue_text("Exported by: " . $user->getFullName());
				$p->continue_text("Date: " . date("d. m. Y"));
				// header division line

				/* Set the drawing properties for the dash patterns */
				$p->setlinewidth(0.6);
				/* Set the first dash pattern with a dash and gap length of 3 */
				$p->set_graphics_option("dasharray={3 3}");
				/* Stroke a line with that pattern */
				$p->moveto(50, 725);
				$p->lineto(545, 725);
				$p->stroke();

				$this->renderPDFTable($p, $page, $submissions->toArray());
				
				

				//$p->end_page_ext("");
				$p->end_document("");
		
				$buf = $p->get_buffer();
				$len = strlen($buf);
		
				header("Content-type: application/pdf");
				header("Content-Length: $len");
				header("Content-Disposition: inline; filename=" . $page . ".pdf");
				print $buf;
			}
			catch (PDFlibException $e) {
					die("PDFlib exception occurred during the export:\n" .
					"[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
					$e->get_errmsg() . "\n");
			}
			catch (Exception $e) {
					die($e);
			}
			$p = 0;
		}else{
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('pageToDisplay', $page);
			$templateMgr->assign('director', $user->getFullName());
			$templateMgr->assign('directorOptions', $filterDirectorOptions);
			$templateMgr->assign('trackOptions', $filterTrackOptions);
			$templateMgr->assign_by_ref('submissions', $submissions);
			$templateMgr->assign_by_ref('reviewFiles', $reviewFiles); //workaround
			$templateMgr->assign('filterDirector', $filterDirector);
			$templateMgr->assign('filterTrack', $filterTrack);
			$templateMgr->assign('yearOffsetFuture', SCHED_CONF_DATE_YEAR_OFFSET_FUTURE);
			$templateMgr->assign('durationOptions', TrackDirectorHandler::getDurationOptions());
			$sessionTypesArray = array();
			$paperTypeDao = DAORegistry::getDAO('PaperTypeDAO');
			$sessionTypes = $paperTypeDao->getPaperTypes($schedConfId);
			while ($sessionType = $sessionTypes->next()) {
				$sessionTypesArray[$sessionType->getId()] = $sessionType;
			}
			$templateMgr->assign('sessionTypes', $sessionTypesArray);

			// Set search parameters
			$duplicateParameters = array(
				'searchField', 'searchMatch', 'search'
			);
			foreach ($duplicateParameters as $param)
				$templateMgr->assign($param, Request::getUserVar($param));

			$templateMgr->assign('reviewType', Array(
				REVIEW_STAGE_ABSTRACT => __('submission.abstract'),
				REVIEW_STAGE_PRESENTATION => __('submission.paper')
			));

			$templateMgr->assign('fieldOptions', Array(
				SUBMISSION_FIELD_TITLE => 'paper.title',
				SUBMISSION_FIELD_AUTHOR => 'user.role.author',
				SUBMISSION_FIELD_DIRECTOR => 'user.role.director',
				SUBMISSION_FIELD_REVIEWER => 'user.role.reviewer'
			));

			$templateMgr->assign('helpTopicId', $helpTopicId);
			$templateMgr->assign('sort', $sort);
			$templateMgr->assign('sortDirection', $sortDirection);
			$templateMgr->display('director/submissions.tpl');
		}
	}

	/**
	 * Delete the specified edit assignment.
	 */
	function deleteEditAssignment($args) {
		$this->validate();

		$schedConf =& Request::getSchedConf();
		$editId = (int) (isset($args[0])?$args[0]:0);

		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignment =& $editAssignmentDao->getEditAssignment($editId);

		if ($editAssignment) {
			$paperDao =& DAORegistry::getDAO('PaperDAO');
			$paper =& $paperDao->getPaper($editAssignment->getPaperId());

			if ($paper && $paper->getSchedConfId() === $schedConf->getId()) {
				$editAssignmentDao->deleteEditAssignmentById($editAssignment->getEditId());
				Request::redirect(null, null, null, 'submission', $paper->getId());
			}
		}

		Request::redirect(null, null, null, 'submissions');
	}

	/**
	 * Assigns the selected director to the submission.
	 */
	function assignDirector($args) {
		$this->validate();
		AppLocale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER)); // manager.people.noneEnrolled

		$schedConf =& Request::getSchedConf();
		$paperId = Request::getUserVar('paperId');
		$directorId = Request::getUserVar('directorId');
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		$isDirector = $roleDao->roleExists($schedConf->getConferenceId(), $schedConf->getId(), $directorId, ROLE_ID_DIRECTOR) || $roleDao->roleExists($schedConf->getConferenceId(), 0, $directorId, ROLE_ID_DIRECTOR);
		$isTrackDirector = $roleDao->roleExists($schedConf->getConferenceId(), $schedConf->getId(), $directorId, ROLE_ID_TRACK_DIRECTOR) || $roleDao->roleExists($schedConf->getConferenceId(), 0, $directorId, ROLE_ID_TRACK_DIRECTOR);

		if (isset($directorId) && $directorId != null && ($isDirector || $isTrackDirector)) {
			// A valid track director has already been chosen;
			// either prompt with a modifiable email or, if this
			// has been done, send the email and store the director
			// selection.

			$this->setupTemplate(DIRECTOR_TRACK_SUBMISSIONS, $paperId, 'summary');

			// FIXME: Prompt for due date.
			if (DirectorAction::assignDirector($paperId, $directorId, $isDirector, Request::getUserVar('send'), true)) {
				Request::redirect(null, null, null, 'submission', $paperId);
			}
		} else {
			// Allow the user to choose a track director or director.
			$this->setupTemplate(DIRECTOR_TRACK_SUBMISSIONS, $paperId, 'summary');

			$searchType = null;
			$searchMatch = null;
			$search = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (!empty($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} elseif (!empty($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$forDirectors = isset($args[0]) && $args[0] === 'director';
			$rangeInfo =& Handler::getRangeInfo('directors', array($forDirectors, (string) $searchType, (string) $search, (string) $searchMatch));
			$directorSubmissionDao =& DAORegistry::getDAO('DirectorSubmissionDAO');

			if ($forDirectors) {
				$roleName = 'user.role.director';
				$rolePath = 'director';
				while (true) {
					$directors =& $directorSubmissionDao->getUsersNotAssignedToPaper($schedConf->getId(), $paperId, RoleDAO::getRoleIdFromPath('director'), $searchType, $search, $searchMatch, $rangeInfo);
					if ($directors->isInBounds()) break;
					unset($rangeInfo);
					$rangeInfo =& $directors->getLastPageRangeInfo();
					unset($directors);
				}
			} else {
				$roleName = 'user.role.trackDirector';
				$rolePath = 'trackDirector';
				while (true) {
					$directors =& $directorSubmissionDao->getUsersNotAssignedToPaper($schedConf->getId(), $paperId, RoleDAO::getRoleIdFromPath('trackDirector'), $searchType, $search, $searchMatch, $rangeInfo);
					if ($directors->isInBounds()) break;
					unset($rangeInfo);
					$rangeInfo =& $directors->getLastPageRangeInfo();
					unset($directors);
				}
			}

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign_by_ref('directors', $directors);
			$templateMgr->assign('roleName', $roleName);
			$templateMgr->assign('rolePath', $rolePath);
			$templateMgr->assign('paperId', $paperId);

			$trackDao =& DAORegistry::getDAO('TrackDAO');
			$trackDirectorTracks =& $trackDao->getDirectorTracks($schedConf->getId());

			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$directorStatistics = $editAssignmentDao->getDirectorStatistics($schedConf->getId());

			$templateMgr->assign_by_ref('directorTracks', $trackDirectorTracks);
			$templateMgr->assign('directorStatistics', $directorStatistics);

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $search);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
			$templateMgr->assign('helpTopicId', 'editorial.directorsRole.summaryPage.submissionManagement');
			$templateMgr->display('director/selectTrackDirector.tpl');
		}
	}

	/**
	 * Delete a submission.
	 */
	function deleteSubmission($args) {
		$paperId = isset($args[0]) ? (int) $args[0] : 0;
		$this->validate();

		$schedConf =& Request::getSchedConf();

		$paperDao =& DAORegistry::getDAO('PaperDAO');
		$paper =& $paperDao->getPaper($paperId);

		$status = $paper->getStatus();
		$progress = $paper->getSubmissionProgress();
		$stage = $paper->getCurrentStage();

		if ($paper->getSchedConfId() == $schedConf->getId() && ($status == STATUS_DECLINED || $status == STATUS_ARCHIVED
			|| ($progress != 0 && ($stage == REVIEW_STAGE_ABSTRACT || ($stage == REVIEW_STAGE_PRESENTATION && $progress < 3))))) {
			// Delete paper files
			import('file.PaperFileManager');
			$paperFileManager = new PaperFileManager($paperId);
			$paperFileManager->deletePaperTree();

			// Delete paper database entries
			$paperDao->deletePaperById($paperId);
		}

		Request::redirect(null, null, null, 'submissions', 'submissionsArchives');
	}

	/**
	 * Change the sequence of the papers.
	 */
	function movePaper($args) {
		$paperId = Request::getUserVar('paperId');
		$schedConf =& Request::getSchedConf();
		$this->validate();

		$publishedPaperDao =& DAORegistry::getDAO('PublishedPaperDAO');
		$publishedPaper =& $publishedPaperDao->getPublishedPaperByPaperId($paperId);

		if ($publishedPaper != null && $publishedPaper->getSchedConfId() == $schedConf->getId()) {
			$publishedPaper->setSeq($publishedPaper->getSeq() + (Request::getUserVar('d') == 'u' ? -1.5 : 1.5));
			$publishedPaperDao->updatePublishedPaper($publishedPaper);
			$publishedPaperDao->resequencePublishedPapers($publishedPaper->getTrackId(), $schedConf->getId());
		}

		Request::redirect(null, null, null, 'submissions', 'submissionsAccepted');
	}

	/**
	 * Allows directors to write emails to users associated with the conference.
	 */
	function notifyUsers($args) {
		$this->validate();
		$this->setupTemplate(DIRECTOR_TRACK_MANAGEMENT);

		$userDao =& DAORegistry::getDAO('UserDAO');
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$registrationDao =& DAORegistry::getDAO('RegistrationDAO');

		$conference =& Request::getConference();
		$conferenceId = $conference->getId();
		$schedConf =& Request::getSchedConf();
		$schedConfId = $schedConf->getId();

		$user =& Request::getUser();
		$templateMgr =& TemplateManager::getManager();

		import('mail.MassMail');
		$email = new MassMail('PUBLISH_NOTIFY');

		if (Request::getUserVar('send') && !$email->hasErrors()) {
			$email->addRecipient($user->getEmail(), $user->getFullName());

			switch (Request::getUserVar('whichUsers')) {
				case 'allPaidRegistrants':
					$recipients =& $registrationDao->getRegisteredUsers($schedConfId);
					break;
				case 'allRegistrants':
					$recipients =& $registrationDao->getRegisteredUsers($schedConfId, false);
					break;
				case 'allAuthorsAbstractAccepted':
					$recipients =& $authorDao->getAuthorsAlphabetizedByStageAndDecision($schedConfId, STATUS_QUEUED, REVIEW_STAGE_ABSTRACT, SUBMISSION_DIRECTOR_DECISION_INVITE, true);
					break;
				case 'allAuthorsAbstractRevisions':
					$recipients =& $authorDao->getAuthorsAlphabetizedByStageAndDecision($schedConfId, STATUS_QUEUED, REVIEW_STAGE_ABSTRACT, SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS, true);
					break;
				case 'allAuthors':
					$recipients =& $authorDao->getAuthorsAlphabetizedBySchedConf($schedConfId, null, null, true);
					break;
				case 'allUsers':
					$recipients =& $roleDao->getUsersBySchedConfId($schedConfId);
					break;
				case 'allReaders':
				default:
					$recipients =& $roleDao->getUsersByRoleId(
						ROLE_ID_READER,
						$conferenceId,
						$schedConfId
					);
					break;
			}
			while (!$recipients->eof()) {
				$recipient =& $recipients->next();
				$email->addRecipient($recipient->getEmail(), $recipient->getFullName());
				unset($recipient);
			}
			if (Request::getUserVar('includeToc')=='1') {
				$publishedPaperDao =& DAORegistry::getDAO('PublishedPaperDAO');
				$publishedPapers =& $publishedPaperDao->getPublishedPapersInTracks($schedConfId);

				$templateMgr->assign_by_ref('conference', $conference);
				$templateMgr->assign_by_ref('schedConf', $schedConf);
				$templateMgr->assign('body', $email->getBody());
				$templateMgr->assign_by_ref('publishedPapers', $publishedPapers);

				$email->setBody($templateMgr->fetch('director/notifyUsersEmail.tpl'));
			}

			$callback = array(&$email, 'send');
			$templateMgr->setProgressFunction($callback);
			unset($callback);

			$email->setFrequency(10); // 10 emails per callback
			$callback = array('TemplateManager', 'updateProgressBar');
			$email->setCallback($callback);
			unset($callback);

			$templateMgr->assign('message', 'common.inProgress');
			$templateMgr->display('common/progress.tpl');
			echo '<script type="text/javascript">window.location = "' . Request::url(null, null, 'director') . '";</script>';
		} else {
			if (!Request::getUserVar('continued')) {
				$email->assignParams(array(
					'editorialContactSignature' => $user->getContactSignature()
				));
			}

			// FIXME: There should be a better way of doing this.
			$allAuthors =& $authorDao->getAuthorsAlphabetizedBySchedConf($schedConfId);
			$allAuthorsCount = $allAuthors->getCount();

			$authorsAbstractAccepted =& $authorDao->getAuthorsAlphabetizedByStageAndDecision($schedConfId, STATUS_QUEUED, REVIEW_STAGE_ABSTRACT, SUBMISSION_DIRECTOR_DECISION_INVITE);
			$authorsAbstractAcceptedCount = $authorsAbstractAccepted->getCount();

			$authorsAbstractRevisions =& $authorDao->getAuthorsAlphabetizedByStageAndDecision($schedConfId, STATUS_QUEUED, REVIEW_STAGE_ABSTRACT, SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS);
			$authorsAbstractRevisionsCount = $authorsAbstractRevisions->getCount();

			$email->displayEditForm(
				Request::url(null, null, null, 'notifyUsers'),
				array(),
				'director/notifyUsers.tpl',
				array(
					'allReadersCount' => $roleDao->getSchedConfUsersCount($schedConfId, ROLE_ID_READER),
					'allAuthorsCount' => $allAuthorsCount,
					'allAuthorsAbstractAcceptedCount' => $authorsAbstractAcceptedCount,
					'allAuthorsAbstractRevisionsCount' => $authorsAbstractRevisionsCount,
					'allPaidRegistrantsCount' => $registrationDao->getRegisteredUserCount($schedConfId),
					'allRegistrantsCount' => $registrationDao->getRegisteredUserCount($schedConfId, false),
					'allUsersCount' => $roleDao->getSchedConfUsersCount($schedConfId)
				)
			);
		}
	}

	/**
	 *	Shows list of trackDirectors
	 */

	function manageTrackDirectors($args){
		$this->validate();
		AppLocale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER)); // manager.people.noneEnrolled

		$schedConf =& Request::getSchedConf();
		$paperId = Request::getUserVar('paperId');
		$directorId = Request::getUserVar('directorId');
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$this->setupTemplate(DIRECTOR_TRACK_MANAGEMENT);

		$searchType = null;
		$searchMatch = null;
		$search = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (!empty($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} elseif (!empty($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo =& Handler::getRangeInfo('directors', array($forDirectors, (string) $searchType, (string) $search, (string) $searchMatch));
		$directorSubmissionDao =& DAORegistry::getDAO('DirectorSubmissionDAO');

		$roleName = 'user.role.trackDirector';
		$rolePath = 'trackDirector';
		while (true) {
			$directors =& $directorSubmissionDao->getUsersNotAssignedToPaper($schedConf->getId(), $paperId, RoleDAO::getRoleIdFromPath('trackDirector'), $searchType, $search, $searchMatch, $rangeInfo);
			if ($directors->isInBounds()) break;
			unset($rangeInfo);
			$rangeInfo =& $directors->getLastPageRangeInfo();
			unset($directors);
		}

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('directors', $directors);
		$templateMgr->assign('roleName', $roleName);
		$templateMgr->assign('rolePath', $rolePath);
		$templateMgr->assign('paperId', $paperId);

		$trackDao =& DAORegistry::getDAO('TrackDAO');
		$trackDirectorTracks =& $trackDao->getDirectorTracks($schedConf->getId());

		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$directorStatistics = $editAssignmentDao->getDirectorStatistics($schedConf->getId());

		$templateMgr->assign_by_ref('directorTracks', $trackDirectorTracks);
		$templateMgr->assign('directorStatistics', $directorStatistics);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		$templateMgr->assign('fieldOptions', Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email'
		));
		$templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
		$templateMgr->assign('helpTopicId', 'editorial.directorsRole.summaryPage.submissionManagement');

		$templateMgr->display('director/manageTrackDirectors.tpl');
	}

	/**
	 * Renders the corresponding table to the PDF file
	 * @dependance pdflib.dll
	 * @param $p The object with PDF typesetting
	 * @param $page The page that is being exported
	 * 
	 */
	function renderPDFTable(&$p, $page = false, $submissionsArray){
		$llx= 50; $lly=50; $urx=545; $ury=710;
		$tf=0; $tbl=0;
		$row = 1;
		$col = 1;
		$p->set_text_pos(50, 710);
		$margin_head = 5;
		$margin_body = 3;

		if ($page == "submissionsUnassigned"){
			// Header
			$font = $p->load_font("Helvetica-Bold", "unicode", "");
			$optlist = "fittextline={position=center font=" . $font . " fontsize=15 margin=" . $margin_head . "} ";
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('common.id'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('paper.authors'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('paper.title'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col, $row++, __('user.role.trackDirectors'), $optlist);
			// Rows
			$font = $p->load_font("Helvetica", "unicode", "");
			$optlist = "fittextline={position=center font=" . $font . " fontsize=13 margin=" . $margin_body . "} ";
			foreach($submissionsArray as $submission){
				// restart col index for the new row
				$col = 1;
				$tbl = $p->add_table_cell($tbl, $col++, $row, $submission->getPaperId(), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getAuthorString(true), 0, 30), $optlist);
				$tbl = $p->add_table_cell($tbl, $col, $row++, substr($submission->getLocalizedTitle(), 0, 50), $optlist);
			}
		}
		else if($page == "submissionsAccepted"){
			// Header
			$font = $p->load_font("Helvetica-Bold", "unicode", "");
			$optlist = "fittextline={position=center font=" . $font . " fontsize=15 margin=" . $margin_head . "} ";
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('common.id'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('paper.authors'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('paper.title'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('common.country'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col, $row++, __('submissions.track'), $optlist);

			// Rows
			$font = $p->load_font("Helvetica", "unicode", "");
			$optlist = "fittextline={position=center font=" . $font . " fontsize=13 margin=" . $margin_body . "} ";
			foreach($submissionsArray as $submission){
				// restart col index for the new row
				$user = $submission->getUser();
				$col = 1;
				$tbl = $p->add_table_cell($tbl, $col++, $row, $submission->getPaperId(), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getAuthorString(true), 0, 30), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getLocalizedTitle(), 0, 40), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, $user->getcountry(), $optlist);
				$tbl = $p->add_table_cell($tbl, $col, $row++, substr($submission->getTrackTitle(), 0, 20), $optlist);
			}
		}
		else if($page == "submissionsArchives"){
			// Header
			$font = $p->load_font("Helvetica-Bold", "unicode", "");
			$optlist = "fittextline={position=center font=" . $font . " fontsize=15 margin=" . $margin_head . "} ";
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('common.id'), $optlist);
			//$tbl = $p->add_table_cell($tbl, $col++, $row, __('submissions.submitted'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('paper.authors'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('paper.title'), $optlist);
			//$tbl = $p->add_table_cell($tbl, $col++, $row, __('submissions.reviewStage'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('common.status'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('common.date'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col, $row++, __('user.role.trackDirectors'), $optlist);

			// Rows
			$font = $p->load_font("Helvetica", "unicode", "");
			$optlist = "fittextline={position=center font=" . $font . " fontsize=13 margin=" . $margin_body . "} ";
			foreach($submissionsArray as $submission){
				// restart col index for the new row
				$col = 1;
				switch ($submission->getStatus()){
					case STATUS_ARCHIVED:
						$date = $submission->getDateToArchive();
						$status = __('submissions.archived');
						break;
					case STATUS_DECLINED:
						$decision = end(end($submission->getDecisions()));
						$date = $decision['dateDecided'];
						$status = __('submissions.declined');
						break;
					case STATUS_PUBLISHED:
					default:
						$date = "";
						$status = "";
				}
				$tbl = $p->add_table_cell($tbl, $col++, $row, $submission->getPaperId(), $optlist);
				//$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getDateSubmitted(), 0, 10), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getAuthorString(true), 0, 30), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getLocalizedTitle(), 0, 40), $optlist);
				//$tbl = $p->add_table_cell($tbl, $col++, $row, $submission->getStage(), $optlist);
				
				//$tbl = $p->add_table_cell($tbl, $col++, $row, $decisionTag, $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, $status, $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, substr($date, 0, 10), $optlist);
				$tbl = $p->add_table_cell($tbl, $col, $row++, substr($submission->getTrackDirectorString(true), 0, 30), $optlist);
			}
		}
		else if($page == "submissionsInReview"){
			// Header
			$font = $p->load_font("Helvetica-Bold", "unicode", "");
			$optlist = "fittextline={position=center font=" . $font . " fontsize=15 margin=" . $margin_head . "} ";
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('common.id'), $optlist);
			//$tbl = $p->add_table_cell($tbl, $col++, $row, __('submissions.submitted'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('paper.authors'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('paper.title'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('submissions.reviewStage'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col++, $row, __('submission.decision'), $optlist);
			$tbl = $p->add_table_cell($tbl, $col, $row++, __('user.role.trackDirectors'), $optlist);

			// Rows
			$font = $p->load_font("Helvetica", "unicode", "");
			$optlist = "fittextline={position=center font=" . $font . " fontsize=13 margin=" . $margin_body . "} ";
			foreach($submissionsArray as $submission){
				// restart col index for the new row
				$col = 1;
				// get Paper object
				//$paper = $paperDao->getPaper()
				// set up decision tag
				$decision = end(end($submission->getDecisions()));
				$decisionTag = '';
				switch ($decision['decision']) {
					case SUBMISSION_DIRECTOR_DECISION_ACCEPT:
					$decisionTag = 'ACC';
					break;
					case SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS:
					case SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS:
					$decisionTag = 'REV';
					break;
					case SUBMISSION_DIRECTOR_DECISION_DECLINE:
					$decisionTag = 'DEC';
					break;
					case '':
					case NULL:
					$decisionTag = '';
					break;
					default:
					$decisionTag = 'ERR';
				}
				$tbl = $p->add_table_cell($tbl, $col++, $row, $submission->getPaperId(), $optlist);
				//$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getDateSubmitted(), 0, 10), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getAuthorString(true), 0, 30), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, substr($submission->getLocalizedTitle(), 0, 40), $optlist);
				$tbl = $p->add_table_cell($tbl, $col++, $row, $submission->getCurrentStage() - 1, $optlist);
				
				$tbl = $p->add_table_cell($tbl, $col++, $row, $decisionTag, $optlist);
				$tbl = $p->add_table_cell($tbl, $col, $row++, substr($submission->getTrackDirectorString(true), 0, 30), $optlist);
			}
		}
		else{
			
			$p->show("Error: no export page selected.");
		}
		/* ---------- Place the table on one or more pages ---------- */
		// First page is special
		$optlist = "header=1 rowheightdefault=auto " .
			"fill={{area=rowodd fillcolor={gray 0.9}}} " .
			"stroke={{line=other}} ";
		/* Place the table instance */
		$result = $p->fit_table($tbl, $llx, $lly, $urx, $ury, $optlist);
		if ($result ==  "_error") {
				die("Couldn't place table: " . $p->get_errmsg());
		}
		$p->end_page_ext("");
		/*
		 * Loop until all of the table is placed; create new pages
		 * as long as more table instances need to be placed.
		 */
		while($result == "_boxfull") {
			$p->begin_page_ext(0, 0, "width=a4.width height=a4.height");

			/* Shade every other $row; draw lines for all table cells.
			 * Add "showcells showborder" to visualize cell borders 
			 */
			$optlist = "header=1 rowheightdefault=auto " .
			"fill={{area=rowodd fillcolor={gray 0.9}}} " .
			"stroke={{line=other}} ";

			/* Place the table instance */
			$result = $p->fit_table($tbl, $llx, $lly, $urx, 792, $optlist);
			if ($result ==  "_error") {
					die("Couldn't place table: " . $p->get_errmsg());
			}

			$p->end_page_ext("");
		}

	/* Check the $result; "_stop" means all is ok. */
	if ($result != "_stop") {
			if ($result ==  "_error") {
					die("Error when placing table: " . $p->get_errmsg());
			}
			else {
					/* Any other return value is a user exit caused by
					 * the "return" option; this requires dedicated code to
					 * deal with.
					 */
					die("User return found in Table");
			}
	}

	/* This will also delete Textflow handles used in the table */
	$p->delete_table($tbl, "");
	}

	/**
	 * Setup common template variables.
	 * @param $level int set to 0 if caller is at the same level as this handler in the hierarchy; otherwise the number of levels below this handler
	 */
	function setupTemplate($level = DIRECTOR_TRACK_HOME, $paperId = 0, $parentPage = null) {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();

		$conference =& Request::getConference();
		$schedConf =& Request::getSchedConf();
		$pageHierarchy = array();

		if ($schedConf) {
			$pageHierarchy[] = array(Request::url(null, null, 'index'), $schedConf->getFullTitle(), true);
		} elseif ($conference) {
			$pageHierarchy[] = array(Request::url(null, 'index', 'index'), $conference->getConferenceTitle(), true);
		}

		$pageHierarchy[] = array(Request::url(null, null, 'user'), 'navigation.user');
		if ($level==DIRECTOR_TRACK_SUBMISSIONS) {
			$pageHierarchy[] = array(Request::url(null, null, 'director'), 'user.role.director');
			$pageHierarchy[] = array(Request::url(null, null, 'director', 'submissions'), 'paper.submissions');
		}
		elseif($level==DIRECTOR_TRACK_MANAGEMENT){
			$pageHierarchy[] = array(Request::url(null, null, 'director'), 'user.role.director');
		}

		import('submission.trackDirector.TrackDirectorAction');
		$submissionCrumb = TrackDirectorAction::submissionBreadcrumb($paperId, $parentPage, 'director');
		if (isset($submissionCrumb)) {
			$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}

?>
