<?php

/**
 * @file UserRegistrationForm.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserRegistrationForm
 * @ingroup registration_form
 *
 * @brief Form for users to self-register.
 */

//$Id$

import('form.Form');

define('REGISTRATION_SUCCESSFUL',	1);
define('REGISTRATION_FAILED',		2);
define('REGISTRATION_NO_PAYMENT',	3);
define('REGISTRATION_FREE',		4);

class UserRegistrationForm extends Form {
	/** @var captchaEnabled boolean whether or not captcha is enabled for this form */
	var $captchaEnabled;

	/** @var $typeId int The registration type ID for this registration */
	var $typeId;

	/**
	 * Constructor
	 * @param $typeId int Registration type to use
	 */
	function UserRegistrationForm($typeId) {
		$schedConf =& Request::getSchedConf();
		$this->typeId = (int) $typeId;

		parent::Form('registration/userRegistrationForm.tpl');

		// check that the user is registered; meaningless
		//$this->addCheck(new FormValidatorCustom($this, 'registrationTypeId', 'required', 'manager.registration.form.typeIdValid', create_function('$registrationTypeId, $schedConfId', '$registrationTypeDao =& DAORegistry::getDAO(\'RegistrationTypeDAO\'); $confRegTypes =& $registrationTypeDao->getRegistrationTypesBySchedConfId($schedConfId); error_log(print_r($confRegTypes->toArray())); return $registrationTypeDao->openRegistrationTypeExistsByTypeId($registrationTypeId, $schedConfId);'), array($schedConf->getId())));

		import('captcha.CaptchaManager');
		$captchaManager = new CaptchaManager();
		$this->captchaEnabled = ($captchaManager->isEnabled() && Config::getVar('captcha', 'captcha_on_register'))?true:false;

		$user =& Request::getUser();
		if (!$user) {
			$site =& Request::getSite();
			$this->addCheck(new FormValidator($this, 'username', 'required', 'user.profile.form.usernameRequired'));
			$this->addCheck(new FormValidator($this, 'password', 'required', 'user.profile.form.passwordRequired'));

			$this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'user.account.form.usernameExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByUsername'), array(), true));
			$this->addCheck(new FormValidatorAlphaNum($this, 'username', 'required', 'user.account.form.usernameAlphaNumeric'));
			$this->addCheck(new FormValidatorLength($this, 'password', 'required', 'user.account.form.passwordLengthTooShort', '>=', $site->getMinPasswordLength()));
			$this->addCheck(new FormValidatorCustom($this, 'password', 'required', 'user.account.form.passwordsDoNotMatch', create_function('$password,$form', 'return $password == $form->getData(\'password2\');'), array(&$this)));
			$this->addCheck(new FormValidator($this, 'firstName', 'required', 'user.profile.form.firstNameRequired'));
			$this->addCheck(new FormValidator($this, 'lastName', 'required', 'user.profile.form.lastNameRequired'));
			$this->addCheck(new FormValidator($this, 'country', 'required', 'user.profile.form.countryRequired'));
			$this->addCheck(new FormValidator($this, 'mailingAddress', 'required', 'user.profile.form.mailingAddressRequired'));
			$this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
			$this->addCheck(new FormValidator($this, 'affiliation', 'required', 'user.profile.form.affiliationRequired'));
			$this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.account.form.emailExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'), array(), true));
			if ($this->captchaEnabled) {
				$this->addCheck(new FormValidatorCaptcha($this, 'captcha', 'captchaId', 'common.captchaField.badCaptcha'));
			}

			$authDao =& DAORegistry::getDAO('AuthSourceDAO');
			$this->defaultAuth =& $authDao->getDefaultPlugin();
			if (isset($this->defaultAuth)) {
				$this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'user.account.form.usernameExists', create_function('$username,$form,$auth', 'return (!$auth->userExists($username) || $auth->authenticate($username, $form->getData(\'password\')));'), array(&$this, $this->defaultAuth)));
			}
		}

		$this->addCheck(new FormValidatorPost($this));
	}

	function validate() {
		$schedConf =& Request::getSchedConf();
		$registrationTypeDao =& DAORegistry::getDAO('RegistrationTypeDAO');
		$registrationType =& $registrationTypeDao->getRegistrationType($this->getData('registrationTypeId'));
		if ($registrationType && $registrationType->getCode() != '') {
			$this->addCheck(new FormValidatorCustom($this, 'feeCode', 'required', 'manager.registration.form.feeCodeValid', create_function('$feeCode, $schedConfId, $form', '$registrationTypeDao =& DAORegistry::getDAO(\'RegistrationTypeDAO\'); return $registrationTypeDao->checkCode($form->getData(\'registrationTypeId\'), $schedConfId, $feeCode);'), array($schedConf->getId(), $this)));
		}
		return parent::validate();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$user =& Request::getUser();
		$schedConf =& Request::getSchedConf();
		$conference =& Request::getConference();
		$site =& Request::getSite();

		// Conference logo variable is not setup by default for some reason
		$templateMgr->assign('displayPageHeaderLogo', $conference->getPageHeaderLogo());

		// find out registrationType for this user
		$registrationDao =& DAORegistry::getDAO('RegistrationDAO');
		$registrationId = $registrationDao->getRegistrationIdByUser($user->getId(), $schedConf->getId());
		$registration =& $registrationDao->getRegistration($registrationId);
		if(!empty($registration)){
			$registrationTypeId = (int) $registration->getTypeId();
			$templateMgr->assign('registrationTypeId', $registrationTypeId);
		}
		

		$registrationOptionDao =& DAORegistry::getDAO('RegistrationOptionDAO');
		$registrationOptions =& $registrationOptionDao->getRegistrationOptionsBySchedConfId($schedConf->getId());
		$templateMgr->assign_by_ref('registrationOptions', $registrationOptions);
		
		//$templateMgr->assign('typeId', (int) Request::getUserVar('registrationTypeId'));

		$registrationTypeDao =& DAORegistry::getDAO('RegistrationTypeDAO');
		$registrationTypes =& $registrationTypeDao->getRegistrationTypesBySchedConfId($schedConf->getId());
		$templateMgr->assign_by_ref('registrationTypes', $registrationTypes);

		$templateMgr->assign('userLoggedIn', $user?true:false);
		$templateMgr->assign('requestUri', $_SERVER['REQUEST_URI']);
		if ($user) {
			$templateMgr->assign('userFullName', $user->getFullName());

		}

		if ($this->captchaEnabled) {
			import('captcha.CaptchaManager');
			$captchaManager = new CaptchaManager();
			$captcha =& $captchaManager->createCaptcha();
			if ($captcha) {
				$templateMgr->assign('captchaEnabled', $this->captchaEnabled);
				$this->setData('captchaId', $captcha->getId());
			}
		}

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		$registrationTypeDao =& DAORegistry::getDAO('RegistrationTypeDAO');
		$registrationOptionCosts = $registrationTypeDao->getRegistrationOptionCosts($this->typeId);
		$templateMgr->assign('registrationOptionCosts', $registrationOptionCosts);

		$registrationType =& $registrationTypeDao->getRegistrationType($this->typeId);
		$templateMgr->assign_by_ref('registrationType', $registrationType);

		$templateMgr->assign('minPasswordLength', $site->getMinPasswordLength());

		$templateMgr->assign_by_ref('user', $user);
		parent::display();
	}

	function getLocaleFieldNames() {
		$userDao =& DAORegistry::getDAO('UserDAO');
		return $userDao->getLocaleFieldNames();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$userVars = array('registrationTypeId', 'specialRequests', 'feeCode', 'registrationOptionId');

		$user =& Request::getUser();
		if (!$user) {
			$userVars[] = 'username';
			$userVars[] = 'salutation';
			$userVars[] = 'password';
			$userVars[] = 'password2';
			$userVars[] = 'firstName';
			$userVars[] = 'middleName';
			$userVars[] = 'captcha';
			$userVars[] = 'lastName';
			$userVars[] = 'initials';
			$userVars[] = 'affiliation';
			$userVars[] = 'signature';
			$userVars[] = 'email';
			$userVars[] = 'userUrl';
			$userVars[] = 'phone';
			$userVars[] = 'fax';
			$userVars[] = 'mailingAddress';
			$userVars[] = 'country';
			$userVars[] = 'biography';
			$userVars[] = 'userLocales';
		}

		if ($this->captchaEnabled) {
			$userVars[] = 'captchaId';
			$userVars[] = 'captcha';
		}

		$this->readUserVars($userVars);

		// If registration type requires it, membership is provided
		$registrationTypeDao =& DAORegistry::getDAO('RegistrationTypeDAO');
		$needMembership = $registrationTypeDao->getRegistrationTypeMembership($this->getData('typeId'));
	}

	/**
	 * Save registration.
	 */
	function execute() {
		$schedConf =& Request::getSchedConf();
		$user =& Request::getUser();

		if (!$user) {
			// New user
			$user = new User();

			$user->setUsername($this->getData('username'));
			$user->setSalutation($this->getData('salutation'));
			$user->setFirstName($this->getData('firstName'));
			$user->setMiddleName($this->getData('middleName'));
			$user->setInitials($this->getData('initials'));
			$user->setLastName($this->getData('lastName'));
			$user->setAffiliation($this->getData('affiliation'));
			$user->setSignature($this->getData('signature'), null); // Localized
			$user->setEmail($this->getData('email'));
			$user->setUrl($this->getData('userUrl'));
			$user->setPhone($this->getData('phone'));
			$user->setFax($this->getData('fax'));
			$user->setMailingAddress($this->getData('mailingAddress'));
			$user->setBiography($this->getData('biography'), null); // Localized
			$user->setInterests($this->getData('interests'), null); // Localized
			$user->setDateRegistered(Core::getCurrentDate());
			$user->setCountry($this->getData('country'));

			$user->setPassword(Validation::encryptCredentials($this->getData('username'), $this->getData('password')));

			$userDao =& DAORegistry::getDAO('UserDAO');
			$userId = $userDao->insertUser($user);
			if (!$userId) {
				return REGISTRATION_FAILED;
			}

			$conference =& Request::getConference();
			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$role = new Role();
			$role->setRoleId(ROLE_ID_READER);
			$role->setSchedConfId($schedConf->getId());
			$role->setConferenceId($conference->getId());
			$role->setUserId($user->getId());
			$roleDao->insertRole($role);

			$sessionManager =& SessionManager::getManager();
			$session =& $sessionManager->getUserSession();
			$session->setSessionVar('username', $user->getUsername());

			// Make sure subsequent requests to Request::getUser work
			Validation::login($this->getData('username'), $this->getData('password'), $reason);

			import('user.form.CreateAccountForm');
			CreateAccountForm::sendConfirmationEmail($user, $this->getData('password'), true);
		}

		// Get the registration type
		$registrationTypeDao =& DAORegistry::getDAO('RegistrationTypeDAO');
		$registrationType =& $registrationTypeDao->getRegistrationType($this->getData('registrationTypeId'));
		if (!$registrationType || $registrationType->getSchedConfId() != $schedConf->getId()) {
			Request::redirect('index');
		}

		import('payment.ocs.OCSPaymentManager');
		$paymentManager =& OCSPaymentManager::getManager();

		if (!$paymentManager->isConfigured()) return REGISTRATION_NO_PAYMENT;

		import('registration.Registration');
		$registration = new Registration();

		$registration->setSchedConfId($schedConf->getId());
		$registration->setUserId($user->getId());
		$registration->setTypeId($this->getData('registrationTypeId'));
		$registration->setSpecialRequests($this->getData('specialRequests') ? $this->getData('specialRequests') : null);
		$registration->setDateRegistered(time());

		$registrationDao =& DAORegistry::getDAO('RegistrationDAO');
		$registrationId = $registrationDao->insertRegistration($registration);

		$registrationOptionDao =& DAORegistry::getDAO('RegistrationOptionDAO');
		$registrationOptions =& $registrationOptionDao->getRegistrationOptionsBySchedConfId($schedConf->getId());
		$registrationOptionIds = (array) $this->getData('registrationOptionId');

		$cost = $registrationType->getCost();
		$registrationOptionCosts = $registrationTypeDao->getRegistrationOptionCosts($this->getData('registrationTypeId'));

		while ($registrationOption =& $registrationOptions->next()) {
			if (
				in_array($registrationOption->getOptionId(), $registrationOptionIds) &&
				strtotime($registrationOption->getOpeningDate()) < time() &&
				strtotime($registrationOption->getClosingDate()) > time() &&
				$registrationOption->getPublic()
			) {
				$registrationOptionDao->insertRegistrationOptionAssoc($registrationId, $registrationOption->getOptionId());
				$cost += $registrationOptionCosts[$registrationOption->getOptionId()];
			}
			unset($registrationOption);
		}

		$queuedPayment =& $paymentManager->createQueuedPayment($schedConf->getConferenceId(), $schedConf->getId(), QUEUED_PAYMENT_TYPE_REGISTRATION, $user->getId(), $registrationId, $cost, $registrationType->getCurrencyCodeAlpha());
		$queuedPaymentId = $paymentManager->queuePayment($queuedPayment); // No limit
		//$queuedPaymentId = $paymentManager->queuePayment($queuedPayment, time() + (60 * 60 * 24 * 30)); // 30 days to complete

		// Notify the user that the registration type was changed
		list($registrationEmail, $registrationName, $registrationContactSignature) = $this->getRegistrationContactInformation($schedConf->getId());

		$paramArray = array(
			'registrantName' => $user->getFullName(),
			'schedConfName' => $schedConf->getSchedConfTitle(),
			'registrationType' => $registrationType->getSummaryString(),
			'username' => $user->getUsername(),
			'registrationContactSignature' => $registrationContactSignature
		);

		import('mail.MailTemplate');
		$mail = new MailTemplate('REGISTRATION_NOTIFY', null, null, null, null, false);
		$mail->setFrom($registrationEmail, $registrationName);
		$mail->assignParams($paramArray);
		$mail->addRecipient($user->getEmail(), $user->getFullName());
		$mail->send();

		if ($cost == 0) {
			//$paymentManager->fulfillQueuedPayment($queuedPaymentId, $queuedPayment);
			return REGISTRATION_FREE;
		} else {
			$paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
		}

		return REGISTRATION_SUCCESSFUL;
	}

	/**
	 * Get the scheduled conference's contact information
	 * @param $schedConfId int
	 * @return array
	 */
	function getRegistrationContactInformation($schedConfId) {
		$schedConfSettingsDao =& DAORegistry::getDAO('SchedConfSettingsDAO');

		$registrationName = $schedConfSettingsDao->getSetting($schedConfId, 'registrationName');
		$registrationEmail = $schedConfSettingsDao->getSetting($schedConfId, 'registrationEmail');
		$registrationPhone = $schedConfSettingsDao->getSetting($schedConfId, 'registrationPhone');
		$registrationFax = $schedConfSettingsDao->getSetting($schedConfId, 'registrationFax');
		$registrationMailingAddress = $schedConfSettingsDao->getSetting($schedConfId, 'registrationMailingAddress');
		$registrationContactSignature = $registrationName;

		if ($registrationMailingAddress != '') $registrationContactSignature .= "\n" . $registrationMailingAddress;
		if ($registrationPhone != '') $registrationContactSignature .= "\n" . AppLocale::Translate('user.phone') . ': ' . $registrationPhone;
		if ($registrationFax != '')	$registrationContactSignature .= "\n" . AppLocale::Translate('user.fax') . ': ' . $registrationFax;

		$registrationContactSignature .= "\n" . AppLocale::Translate('user.email') . ': ' . $registrationEmail;

		return array($registrationEmail, $registrationName, $registrationContactSignature);
	}
}

?>
