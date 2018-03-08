<?php

/**
 * @file AuthorSubmitStep3Form.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep3Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 2 of author paper submission.
 */

//$Id$

import("author.form.submit.AuthorSubmitForm");
//import('classes.submission.common.JELCodes');

class AuthorSubmitStep3Form extends AuthorSubmitForm {
	/**
	 * Constructor.
	 */
	function AuthorSubmitStep3Form($paper) {
		parent::AuthorSubmitForm($paper, 3);

		// Validation checks for this form
		$this->addCheck(new FormValidatorCustom($this, 'authors', 'required', 'author.submit.form.authorRequired', create_function('$authors', 'return count($authors) > 0;')));
		$this->addCheck(new FormValidatorArray($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', array('firstName', 'lastName', 'affiliation_select', 'affiliation', 'country')));
		$this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'author.submit.form.authorRequiredFields', create_function('$email, $regExp', 'return String::regexp_match($regExp, $email);'), array(ValidatorEmail::getRegexp()), false, array('email')));
		$this->addCheck(new FormValidatorArrayCustom($this, 'authors', 'required', 'user.profile.form.urlInvalid', create_function('$url, $regExp', 'return empty($url) ? true : String::regexp_match($regExp, $url);'), array(ValidatorUrl::getRegexp()), false, array('url')));
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'author.submit.form.titleRequired'));

		$schedConf =& Request::getSchedConf();

		if ($schedConf->getSetting('metaSubjectClass')){
			$this->addCheck(new FormValidatorCustom($this, 'subjectClass', 'required', 'author.submit.form.subjectClassRequired', create_function('$subjectClass', 'foreach ($subjectClass as $oneSubClass) { if($oneSubClass === "")  return false;} return true;')));
		}

		$reviewMode = $paper->getReviewMode();
		$formLocale = Request::getUserVar('formLocale');
		if ($reviewMode != REVIEW_MODE_PRESENTATIONS_ALONE) {
			
			$this->addCheck(new FormValidatorLocale($this, 'abstract', 'required', 'author.submit.form.abstractRequired'));

			$trackDao =& DAORegistry::getDAO('TrackDAO');
			$track = $trackDao->getTrack($paper->getTrackId());
			$abstractWordCount = $track->getAbstractWordCount();
			if (isset($abstractWordCount) && $abstractWordCount > 0) {
				$this->addCheck(new FormValidatorCustom($this, 'abstract', 'required', 'author.submit.form.wordCountAlert', create_function('$abstract, $wordCount', 'foreach ($abstract as $localizedAbstract) {return count(explode(" ",strip_tags($localizedAbstract))) < $wordCount; }'), array($abstractWordCount)));
			}
		}
	}

	/**
	 * Initialize form data from current paper.
	 */
	function initData() {
		$trackDao =& DAORegistry::getDAO('TrackDAO');
		//$JEL = new JELCodes();
		$paperId = $this->paper->getID();

		if (isset($this->paper)) {
			$paper =& $this->paper;
			$this->_data = array(
				'authors' => array(),
				'title' => $paper->getTitle(null), // Localized
				'abstract' => $paper->getAbstract(null), // Localized
				'discipline' => $paper->getDiscipline(null), // Localized
				//'subjectClass' => $JEL->getCodes($paperId),
				'subjectClass' => $paper->getSubjectClass(null), // Localized
				'subject' => $paper->getSubject(null), // Localized
				'coverageGeo' => $paper->getCoverageGeo(null), // Localized
				'coverageChron' => $paper->getCoverageChron(null), // Localized
				'coverageSample' => $paper->getCoverageSample(null), // Localized
				'type' => $paper->getType(null), // Localized
				'language' => $paper->getLanguage(),
				'sponsor' => $paper->getSponsor(null), // Localized
				'citations' => $paper->getCitations(),
				'track' => $trackDao->getTrack($paper->getTrackId())
			);

			$authors =& $paper->getAuthors();
			for ($i=0, $count=count($authors); $i < $count; $i++) {
				array_push(
					$this->_data['authors'],
					array(
						'authorId' => $authors[$i]->getId(),
						'firstName' => $authors[$i]->getFirstName(),
						'middleName' => $authors[$i]->getMiddleName(),
						'lastName' => $authors[$i]->getLastName(),
						'affiliation_select' => $authors[$i]->getAffiliationSelect(),
						'affiliation' => $authors[$i]->getAffiliation(),
						'country' => $authors[$i]->getCountry(),
						'email' => $authors[$i]->getEmail(),
						'url' => $authors[$i]->getUrl(),
						'biography' => $authors[$i]->getBiography(null)
					)
				);
				if ($authors[$i]->getPrimaryContact()) {
					$this->setData('primaryContact', $i);
				}
			}
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$userVars = array(
			'authors',
			'deletedAuthors',
			'primaryContact',
			'title',
			'discipline',
			'subjectClass',
			'subject',
			'coverageGeo',
			'coverageChron',
			'coverageSample',
			'type',
			'language',
			'sponsor',
			'citations'
		);

		$schedConf =& Request::getSchedConf();
		$reviewMode = $this->paper->getReviewMode();
		if ($reviewMode != REVIEW_MODE_PRESENTATIONS_ALONE) {
			$userVars[] = 'abstract';
		}
		$this->readUserVars($userVars);

		// Load the track. This is used in the step 2 form to
		// determine whether or not to display indexing options.
		$trackDao =& DAORegistry::getDAO('TrackDAO');
		$this->_data['track'] =& $trackDao->getTrack($this->paper->getTrackId());
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		$returner = array('title', 'subject', 'coverageGeo', 'coverageChron', 'coverageSample', 'type', 'sponsor');
		$schedConf =& Request::getSchedConf();
		$reviewMode = $this->paper->getReviewMode();
		if ($reviewMode != REVIEW_MODE_PRESENTATIONS_ALONE) {
			$returner[] = 'abstract';
		}
		return $returner;
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);
		$formLocale = Request::getUserVar('formLocale');

		if (Request::getUserVar('addAuthor') || Request::getUserVar('delAuthor')  || Request::getUserVar('moveAuthor')) {
			$templateMgr->assign('scrollToAuthor', true);
		}
		elseif (Request::getUserVar('addClassification')){
			$templateMgr->assign('scrollToIndexing', true);
		}

		// Initialization of Affiliation options and addresses
		import('user.form.Affiliations');
		$affil = new Affiliations();
		$templateMgr->assign('affiliations', $affil->getAffiliations());
		$templateMgr->assign('affiliationsEn', $affil->getAffiliations('en_US'));
		$templateMgr->assign('suffixes', $affil->getSuffixes());

		// Initialization of the JEL codes class
		/*$JEL = new JELCodes();
		$paperId = $this->paper->getID();
		$templateMgr->assign('JELClassification', $JEL->getClassification());*/

		$schedConf =& Request::getSchedConf();
		$reviewMode = $this->paper->getReviewMode();
		$templateMgr->assign('collectAbstracts', $reviewMode != REVIEW_MODE_PRESENTATIONS_ALONE);
		parent::display();
	}

	/**
	 * Save changes to paper.
	 * @return int the paper ID
	 */
	function execute() {
		$paperDao =& DAORegistry::getDAO('PaperDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$paper =& $this->paper;
		$conference =& Request::getConference();
		$schedConf =& Request::getSchedConf();
		$user =& Request::getUser();

		//$JEL = new JELCodes();
		$paperId = $this->paper->getID();

		// Update paper
		$paper->setTitle($this->getData('title'), null); // Localized

		$reviewMode = $this->paper->getReviewMode();
		if ($reviewMode != REVIEW_MODE_PRESENTATIONS_ALONE) {
			 $paper->setAbstract($this->getData('abstract'), null); // Localized
		}

		// Set up JEL codes
		//$JELCodes = $JEL->getCodes();
		/*foreach ($this->getData('subjectClass') as $key => $value) {
				$JEL->setCode($paperId, $value, $JEL->getKeyword($value));
		}*/



		$paper->setDiscipline($this->getData('discipline'), null); // Localized
		$paper->setSubjectClass($this->getData('subjectClass'), null); // Localized
		$paper->setSubject($this->getData('subject'), null); // Localized
		$paper->setCoverageGeo($this->getData('coverageGeo'), null); // Localized
		$paper->setCoverageChron($this->getData('coverageChron'), null); // Localized
		$paper->setCoverageSample($this->getData('coverageSample'), null); // Localized
		$paper->setType($this->getData('type'), null); // Localized
		$paper->setLanguage($this->getData('language')); // Localized
		$paper->setSponsor($this->getData('sponsor'), null); // Localized
		$paper->setCitations($this->getData('citations'));

		// Update the submission progress if necessary.
		if ($paper->getSubmissionProgress() <= $this->step) {
			$paper->stampStatusModified();

			// If we aren't about to collect the paper, the submission is complete
			// (for now)
			$reviewMode = $this->paper->getReviewMode();
			if($reviewMode == REVIEW_MODE_BOTH_SIMULTANEOUS || $reviewMode == REVIEW_MODE_PRESENTATIONS_ALONE) {
				if (!$schedConf->getSetting('acceptSupplementaryReviewMaterials')) $paper->setSubmissionProgress($this->step + 2); // Skip supp files
				else $paper->setSubmissionProgress($this->step + 1);
				// The line below is necessary to ensure that
				// the paper upload goes in with the correct
				// stage number (i.e. paper).
				$paper->setCurrentStage(REVIEW_STAGE_PRESENTATION);
			} else {
				$paper->setDateSubmitted(Core::getCurrentDate());
				$paper->stampStatusModified();
				$paper->setCurrentStage(REVIEW_STAGE_ABSTRACT);
				$this->assignDirectors($paper);

				if ($schedConf->getSetting('acceptSupplementaryReviewMaterials')) {
					$paper->setSubmissionProgress($this->step + 2);
				} else {
					$paper->setSubmissionProgress(0);
					$this->confirmSubmission($paper, $user, $schedConf, $conference, 'SUBMISSION_ACK');
				}
			}
		}

		// Update authors
		$authors = $this->getData('authors');
		for ($i=0, $count=count($authors); $i < $count; $i++) {
			if ($authors[$i]['authorId'] > 0) {
				// Update an existing author
				$author =& $paper->getAuthor($authors[$i]['authorId']);
				$isExistingAuthor = true;

			} else {
				// Create a new author
				$author = new Author();
				$isExistingAuthor = false;
			}

			if ($author != null) {
				$author->setFirstName($authors[$i]['firstName']);
				$author->setMiddleName($authors[$i]['middleName']);
				$author->setLastName($authors[$i]['lastName']);
				$author->setAffiliationSelect($authors[$i]['affiliation_select']);
				$author->setAffiliation($authors[$i]['affiliation']);
				$author->setCountry($authors[$i]['country']);
				$author->setEmail($authors[$i]['email']);
				$author->setUrl($authors[$i]['url']);
				$author->setBiography($authors[$i]['biography'], null); // Localized
				$author->setPrimaryContact($this->getData('primaryContact') == $i ? 1 : 0);
				$author->setSequence($authors[$i]['seq']);

				if ($isExistingAuthor == false) {
					$paper->addAuthor($author);
				}
			}
			unset($author);
		}

		// Remove deleted authors
		$deletedAuthors = explode(':', $this->getData('deletedAuthors'));
		for ($i=0, $count=count($deletedAuthors); $i < $count; $i++) {
			$paper->removeAuthor($deletedAuthors[$i]);
		}



		// Save the paper
		$paperDao->updatePaper($paper);

		// Log the submission, even though it may not be "complete"
		// at this step. This is important because we don't otherwise
		// capture changes in review process.
		import('paper.log.PaperLog');
		import('paper.log.PaperEventLogEntry');
		// not logging authors submission because we don't want track director to see authors
		PaperLog::logEvent($this->paperId, PAPER_LOG_ABSTRACT_SUBMIT, LOG_TYPE_AUTHOR, $user->getId(), 'log.author.abstractSubmitted', array('submissionId' => $paper->getId(), 'authorName' => $user->getFullName()));
		return $this->paperId;
	}
}

?>
