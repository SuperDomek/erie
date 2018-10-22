<?php

/**
 * @file AuthorAction.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorAction
 * @ingroup submission
 *
 * @brief AuthorAction class.
 *
 */

// $Id$

import('pages.trackDirector.TrackDirectorHandler');
import('submission.common.Action');

class AuthorAction extends Action {

	/**
	 * Constructor.
	 */
	function AuthorAction() {
		parent::Action();
	}

	/**
	 * Actions.
	 */

	/**
	 * Designates the original file the review version.
	 * @param $authorSubmission object
	 */
	function designateReviewVersion($authorSubmission) {
		import('file.PaperFileManager');
		$paperFileManager = new PaperFileManager($authorSubmission->getPaperId());
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');

		if (!HookRegistry::call('AuthorAction::designateReviewVersion', array(&$authorSubmission))) {
			$submissionFile =& $authorSubmission->getSubmissionFile();
			if ($submissionFile) {
				$reviewFileId = $paperFileManager->copyToReviewFile($submissionFile->getFileId());

				$authorSubmission->setReviewFileId($reviewFileId);

				$authorSubmissionDao->updateAuthorSubmission($authorSubmission);

				$trackDirectorSubmissionDao =& DAORegistry::getDAO('TrackDirectorSubmissionDAO');
				$schedConf =& Request::getSchedConf();
				if (!$schedConf || $schedConf->getId() != $authorSubmission->getSchedConfId()) {
					$schedConfDao =& DAORegistry::getDAO('SchedConfDAO');
					unset($schedConf);
					$schedConf =& $schedConfDao->getSchedConf($authorSubmission->getSchedConfId());
				}
				$trackDirectorSubmissionDao->createReviewStage($authorSubmission->getPaperId(), REVIEW_STAGE_PRESENTATION, 1);
			}
		}
	}

	/**
	 * Delete an author file from a submission.
	 * @param $paper object
	 * @param $fileId int
	 * @param $revisionId int
	 */
	function deletePaperFile($paper, $fileId, $revisionId) {
		import('file.PaperFileManager');

		$paperFileManager = new PaperFileManager($paper->getId());
		$paperFileDao =& DAORegistry::getDAO('PaperFileDAO');
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$paperCommentDao =& DAORegistry::getDAO('PaperCommentDAO');

		$paperFile =& $paperFileDao->getPaperFile($fileId, $revisionId, $paper->getId());
		$authorSubmission = $authorSubmissionDao->getAuthorSubmission($paper->getId());
		$authorRevisions = $authorSubmission->getAuthorFileRevisions();

		// Ensure that this is actually an author file.
		if (isset($paperFile)) {
			HookRegistry::call('AuthorAction::deletePaperFile', array(&$paperFile, &$authorRevisions));
			foreach ($authorRevisions as $stage) {
				foreach ($stage as $revision) {
					if ($revision->getFileId() == $paperFile->getFileId() &&
						$revision->getRevision() == $paperFile->getRevision()) {
							$paperCommentDao->deletePaperComments($paper->getId(), $authorSubmission->getCurrentStage(), COMMENT_TYPE_AUTHOR_REVISION_CHANGES);
							$paperFileManager->deleteFile($paperFile->getFileId(), $paperFile->getRevision());
					}
				}
			}
		}
	}

	/**
	 * Upload the revised version of a paper.
	 * @param $authorSubmission object
	 */
	function uploadRevisedVersion($authorSubmission) {
		import('file.PaperFileManager');
		$paperFileManager = new PaperFileManager($authorSubmission->getPaperId());
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$session =& Request::getSession();
		$paperId = $authorSubmission->getPaperId();
		$conference =& Request::getConference();
		
		$changes = (String) Request::getUserVar('file_changes');

		// Check that the changes box is not empty and has at least 10 characters
		if(strlen($changes) <= 10) {
			$errors["file_changes"] = __("common.uploadChangesEmpty");
			$session->setSessionVar('isError', true);
			$session->setSessionVar('errors', $errors);
			$session->setSessionVar('changes', $changes);
			return false;
		}

		$fileName = 'revision_upload';
		if($paperFileManager->uploadErrorNoFile($fileName)){
			$errors["revision_upload"] = __("common.uploadFailed.noFile");
			$session->setSessionVar('isError', true);
			$session->setSessionVar('errors', $errors);
			$session->setSessionVar('changes', $changes);
			return false;
		}
		if ($paperFileManager->uploadError($fileName)) {
			$errors["revision_upload"] = __("common.uploadFailed");
			$session->setSessionVar('isError', true);
			$session->setSessionVar('errors', $errors);
			$session->setSessionVar('changes', $changes);
			return false;
		}
		if (!$paperFileManager->uploadedFileExists($fileName)) {
			$errors["revision_upload"] = __("common.uploadFailed");
			$session->setSessionVar('isError', true);
			$session->setSessionVar('errors', $errors);
			$session->setSessionVar('changes', $changes);
			return false;
		}
		HookRegistry::call('AuthorAction::uploadRevisedVersion', array(&$authorSubmission));
		if ($authorSubmission->getRevisedFileId() != null) {
			$fileId = $paperFileManager->uploadDirectorDecisionFile($fileName, $authorSubmission->getRevisedFileId());
		} else {
			$fileId = $paperFileManager->uploadDirectorDecisionFile($fileName, null);
		}
		if (!$fileId) {
			$errors["revision_upload"] = __("common.uploadFailed");
			$session->setSessionVar('isError', true);
			$session->setSessionVar('errors', $errors);
			$session->setSessionVar('changes', $changes);
			return false;
		}
		$authorSubmission->setRevisedFileId($fileId);
		$authorSubmissionDao->updateAuthorSubmission($authorSubmission);

		// Refresh authorSubmission
		$authorSubmission =& $authorSubmissionDao->getAuthorSubmission($paperId);

		// Set file as new review file
		// FIX: using trackDirector functions
		$trackDirectorSubmissionDao =& DAORegistry::getDAO('TrackDirectorSubmissionDAO');
		$submission =& $trackDirectorSubmissionDao->getTrackDirectorSubmission($paperId);
		$revisionPaper =& $authorSubmission->getRevisedFile();
		TrackDirectorAction::setReviewFile($submission, $fileId, $revisionPaper->getRevision());

		/* At this point the notification comes from the TrackDirectorAction::setReviewFile function
		*
		// Send a notification to associated users
		import('notification.NotificationManager');
		$notificationManager = new NotificationManager();
		
		$paperDao =& DAORegistry::getDAO('PaperDAO');
		$paper =& $paperDao->getPaper($paperId);
		$notificationUsers = $paper->getAssociatedUserIds(false, false, true, false);
		foreach ($notificationUsers as $userRole) {
			$url = Request::url(null, null, $userRole['role'], 'submissionReview', $paperId, null);
			$notificationManager->createNotification(
				$userRole['id'], 'notification.type.revisionUploaded',
				null, $url, 1, NOTIFICATION_TYPE_GALLEY_MODIFIED
			);
		}

		// Send a notification to conference managers that a file needs to be checked
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$directorsTemp = $roleDao->getUsersByRoleId(ROLE_ID_DIRECTOR, $conference->getId());
		$directors = $directorsTemp->toArray();
		$notificationDirectors = array();
		foreach ($directors as $user) {
			$notificationDirectors[] = array('id' => $user->getId());
		}
		foreach ($notificationDirectors as $manager) {
			$url = Request::url(null, null, 'director', 'submissionReview', $paperId);
			$notificationManager->createNotification(
				$manager['id'], 'notification.type.fileNeedsCheck',
				$paper->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_PAPER_SUBMITTED
			);
		}*/


		// Input the changes as paper comment with own comment type
		$paperCommentDao =& DAORegistry::getDAO('PaperCommentDAO');
		// remove previous change comments connected with this revision file in the current stage (only one active author revision file allowed)
		$paperCommentDao->deletePaperComments($paperId, $authorSubmission->getCurrentStage(), COMMENT_TYPE_AUTHOR_REVISION_CHANGES);
		$paperComment = new PaperComment();
		$paperComment->setCommentType(COMMENT_TYPE_AUTHOR_REVISION_CHANGES);
		$paperComment->setRoleId(ROLE_ID_AUTHOR);
		$paperComment->setPaperId($paperId);
		$paperComment->setAuthorId($authorSubmission->getUserId());
		$paperComment->setCommentTitle("Checklist of adjustments");
		$paperComment->setComments($changes);
		$paperComment->setDatePosted(Core::getCurrentDate());
		$paperComment->setViewable(true);
		$paperComment->setAssocId($authorSubmission->getCurrentStage());
		$paperCommentDao->insertPaperComment($paperComment);

		// Add log entry
		$user =& Request::getUser();
		import('paper.log.PaperLog');
		import('paper.log.PaperEventLogEntry');
		PaperLog::logEvent($authorSubmission->getPaperId(), PAPER_LOG_AUTHOR_REVISION, LOG_TYPE_AUTHOR, $user->getId(), 'log.author.documentRevised', array('authorName' => $user->getFullName(), 'fileId' => $fileId, 'paperId' => $authorSubmission->getPaperId()));
	}

	//
	// Comments
	//

	/**
	 * View review form response.
	 * @param $authorSubmission object
	 * @param $reviewId int
	 */
	function viewReviewFormResponse($authorSubmission, $reviewId) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignmentById($reviewId);
		if (HookRegistry::call('AuthorAction::viewReviewFormResponse', array(&$authorSubmission, &$reviewAssignment, &$reviewId))) return $reviewId;
		if (isset($reviewAssignment) && $reviewAssignment->getPaperId() == $authorSubmission->getPaperId()) {
			$reviewFormId = $reviewAssignment->getReviewFormId();
			if ($reviewFormId != null) {
				import('submission.form.ReviewFormResponseForm');
				$reviewForm = new ReviewFormResponseForm($reviewId, $reviewFormId);
				$reviewForm->initData();
				$reviewForm->display();
			}
		}
	}

	/**
	 * View director decision comments.
	 * @param $paper object
	 */
	function viewDirectorDecisionComments($paper) {
		if (!HookRegistry::call('AuthorAction::viewDirectorDecisionComments', array(&$paper))) {
			import("submission.form.comment.DirectorDecisionCommentForm");

			$commentForm = new DirectorDecisionCommentForm($paper, ROLE_ID_AUTHOR);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Email director decision comment.
	 * @param $authorSubmission object
	 * @param $send boolean
	 */
	function emailDirectorDecisionComment($authorSubmission, $send) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$conference =& Request::getConference();
		$schedConf =& Request::getSchedConf();

		$user =& Request::getUser();
		import('mail.PaperMailTemplate');
		$email = new PaperMailTemplate($authorSubmission);

		$editAssignments = $authorSubmission->getEditAssignments();
		$directors = array();
		foreach ($editAssignments as $editAssignment) {
			array_push($directors, $userDao->getUser($editAssignment->getDirectorId()));
		}

		if ($send && !$email->hasErrors()) {
			HookRegistry::call('AuthorAction::emailDirectorDecisionComment', array(&$authorSubmission, &$email));
			$email->send();

			$paperCommentDao =& DAORegistry::getDAO('PaperCommentDAO');
			$paperComment = new PaperComment();
			$paperComment->setCommentType(COMMENT_TYPE_DIRECTOR_DECISION);
			$paperComment->setRoleId(ROLE_ID_AUTHOR);
			$paperComment->setPaperId($authorSubmission->getPaperId());
			$paperComment->setAuthorId($authorSubmission->getUserId());
			$paperComment->setCommentTitle($email->getSubject());
			$paperComment->setComments($email->getBody());
			$paperComment->setDatePosted(Core::getCurrentDate());
			$paperComment->setViewable(true);
			$paperComment->setAssocId($authorSubmission->getPaperId());
			$paperCommentDao->insertPaperComment($paperComment);

			return true;
		} else {
			if (!Request::getUserVar('continued')) {
				$email->setSubject($authorSubmission->getLocalizedTitle());
				if (!empty($directors)) {
					foreach ($directors as $director) {
						$email->addRecipient($director->getEmail(), $director->getFullName());
					}
				} else {
					$email->addRecipient($schedConf->getSetting('contactEmail'), $schedConf->getSetting('contactName'));
				}
			}

			$email->displayEditForm(Request::url(null, null, null, 'emailDirectorDecisionComment', 'send'), array('paperId' => $authorSubmission->getPaperId()), 'submission/comment/directorDecisionEmail.tpl');

			return false;
		}
	}

	/**
	 * Email co-editors the layout comment or acceptance.
	 * @param $authorSubmission object
	 * @param $comment string
	 * @param $acc boolean
	 */
	function emailLayoutResp($authorSubmission, $acc, $comment = null) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$conference =& Request::getConference();
		$schedConf =& Request::getSchedConf();

		$user =& Request::getUser();
		import('mail.PaperMailTemplate');

		$directors = array();
		$editAssignments = $authorSubmission->getEditAssignments();
		foreach ($editAssignments as $editAssignment) {
			array_push($directors, $userDao->getUser($editAssignment->getDirectorId()));
		}
		$paperUrl = Request::url(null, null, 'trackDirector', 'submissionReview', $authorSubmission->getId());

		if($acc){
			$email = new PaperMailTemplate($authorSubmission, 'AUTHOR_LAYOUT_ACC');
			$paramArray = array( 
				'paperTitle' => $authorSubmission->getLocalizedTitle(),
				'paperId' => $authorSubmission->getId(),
				'paperUrl' => $paperUrl,
				'editorialContactSignature' => $schedConf->getSetting('contactName') . "\n" . $conference->getConferenceTitle(),
			);
		}
		else if($acc == false && !empty($comment)){
			$email = new PaperMailTemplate($authorSubmission, 'AUTHOR_LAYOUT_REJECT');
			$paramArray = array( 
				'paperTitle' => $authorSubmission->getLocalizedTitle(),
				'paperId' => $authorSubmission->getId(),
				'paperUrl' => $paperUrl,
				'editorialContactSignature' => $schedConf->getSetting('contactName') . "\n" . $conference->getConferenceTitle(),
				'comment' => $comment,
			);
		}
		else {
			fatalError("Cannot send e-mail. The comment cannot be empty.");
		}
		
		// set sender as the conference itself
		$email->setFrom($schedConf->getSetting('contactEmail'), $schedConf->getSetting('contactName'));

		// sending to each co-editor separately
		if (!empty($directors)) {
			foreach ($directors as $director) {
				$email->clearRecipients();
				$email->addRecipient($director->getEmail(), $director->getFullName());
				$email->assignParams($paramArray);
				$email->send();
				// Test for the mail being sent
				error_log("Odesílám mail z adresy: " . $email->getFromString());
				error_log("Odesílám mail na adresy: " . $email->getRecipientString());
				error_log("Předmět mailu je: " . $email->getSubject());
				error_log("Tělo mailu: " . $email->getBody());
			}
		}
		else {
			$email->addRecipient($schedConf->getSetting('contactEmail'), $schedConf->getSetting('contactName'));
			$email->assignParams($paramArray);
			$email->send();
			// Test for the mail being sent
			error_log("Odesílám mail z adresy: " . $email->getFromString());
			error_log("Odesílám mail na adresy: " . $email->getRecipientString());
			error_log("Předmět mailu je: " . $email->getSubject());
			error_log("Tělo mailu: " . $email->getBody());
		}
		return true;
	}

	//
	// Misc
	//

	/**
	 * Download a file an author has access to.
	 * @param $paper object
	 * @param $fileId int
	 * @param $revision int
	 * @return boolean
	 * TODO: Complete list of files author has access to
	 */
	function downloadAuthorFile($paper, $fileId, $revision = null) {
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');

		$submission =& $authorSubmissionDao->getAuthorSubmission($paper->getId());

		$canDownload = false;

		// Authors have access to:
		// 1) The original submission file.
		// 2) Any files uploaded by the reviewers that are "viewable",
		//    although only after a decision has been made by the director.
		// 4) Any of the author-revised files.
		// 5) The layout version of the file.
		// 6) Any supplementary file
		// 7) Any galley file
		// 8) All review versions of the file
		// 9) Current director versions of the file
		// THIS LIST SHOULD NOW BE COMPLETE.
		if ($submission->getSubmissionFileId() == $fileId) {
			$canDownload = true;
		} else if ($submission->getRevisedFileId() == $fileId) {
			$canDownload = true;
		} else if ($submission->getLayoutFileId() == $fileId) {
			$canDownload = true;
		} else {
			// Check reviewer files
			foreach ($submission->getReviewAssignments(null) as $stageReviewAssignments) {
				foreach ($stageReviewAssignments as $reviewAssignment) {
					if ($reviewAssignment->getReviewerFileId() == $fileId) {
						$paperFileDao =& DAORegistry::getDAO('PaperFileDAO');

						$paperFile =& $paperFileDao->getPaperFile($fileId, $revision);

						if ($paperFile != null && $paperFile->getViewable()) {
							$canDownload = true;
						}
					}
				}
			}

			// Check supplementary files
			foreach ($submission->getSuppFiles() as $suppFile) {
				if ($suppFile->getFileId() == $fileId) {
					$canDownload = true;
				}
			}

			// Check galley files
			foreach ($submission->getGalleys() as $galleyFile) {
				if ($galleyFile->getFileId() == $fileId) {
					$canDownload = true;
				}
			}

			// Check current review version
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewFilesByStage =& $reviewAssignmentDao->getReviewFilesByStage($paper->getId());
			$reviewFile = @$reviewFilesByStage[$paper->getCurrentStage()];
			if ($reviewFile && $fileId == $reviewFile->getFileId()) {
				$canDownload = true;
			}

			// Check director version
			$directorFiles = $submission->getDirectorFileRevisions($paper->getCurrentStage());
			if (is_array($directorFiles)) foreach ($directorFiles as $directorFile) {
				if ($directorFile->getFileId() == $fileId) {
					$canDownload = true;
				}
			}
		}

		$result = false;
		if (!HookRegistry::call('AuthorAction::downloadAuthorFile', array(&$paper, &$fileId, &$revision, &$canDownload, &$result))) {
			if ($canDownload) {
				return Action::downloadFile($paper->getId(), $fileId, $revision);
			} else {
				return false;
			}
		}
		return $result;
	}

	function mayEditPaper(&$authorSubmission) {
		$schedConf =& Request::getSchedConf();
		if (!$schedConf || $schedConf->getId() != $authorSubmission->getSchedConfId()) {
			unset($schedConf);
			$schedConfDao =& DAORegistry::getDAO('SchedConfDAO');
			$schedConf =& $schedConfDao->getSchedConf($paper->getSchedConfId());
		}
		// Directors acting as Authors can always edit.
		if (Validation::isDirector($schedConf->getConferenceId(), $schedConf->getId())) return true;

		// Incomplete submissions can always be edited.
		if ($authorSubmission->getSubmissionProgress() != 0) return true;

		// Archived or declined submissions can never be edited.
		if ($authorSubmission->getStatus() == STATUS_ARCHIVED ||
			$authorSubmission->getStatus() == STATUS_DECLINED) return false;


		// If the last recorded editorial decision on the current stage
		// was "Revisions Required", the author may edit the submission.
		$decisions = $authorSubmission->getDecisions($authorSubmission->getCurrentStage());
		$lastDecision = end($decisions)['decision'];
		if ($lastDecision == SUBMISSION_DIRECTOR_DECISION_PENDING_REVISIONS ||
		$lastDecision == SUBMISSION_DIRECTOR_DECISION_PENDING_MINOR_REVISIONS ||
		$lastDecision == SUBMISSION_DIRECTOR_DECISION_PENDING_MAJOR_REVISIONS) return true;

		// Submissions in Presentation stage cannot be edited anymore
		if ($authorSubmission->getCurrentStage() >= REVIEW_STAGE_PRESENTATION) return false;

		// After the deadline for Submitting cannot be edited
		$submissionsCloseDate = $schedConf->getSetting('submissionsCloseDate');
		if (time() > $submissionsCloseDate) return false;

		// If there are open reviews for the submission, it may not be edited.
		/*
		$assignments = $authorSubmission->getReviewAssignments(null);
		if (is_array($assignments)) foreach ($assignments as $round => $roundAssignments) {
			if (is_array($roundAssignments)) foreach($roundAssignments as $assignment) {
				if (	!$assignment->getCancelled() &&
					!$assignment->getReplaced() &&
					!$assignment->getDeclined() &&
					$assignment->getDateCompleted() == null &&
					$assignment->getDateNotified() != null
				) {
					return false;
				}
			}
		}*/

		// If the conference isn't closed, the author may edit the submission.
		if (strtotime($schedConf->getEndDate()) > time()) return true;

		// Otherwise, edits are not allowed.
		return false;
	}
}

?>
