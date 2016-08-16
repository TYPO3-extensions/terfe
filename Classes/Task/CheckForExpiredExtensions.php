<?php
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class Tx_TerFe2_Task_CheckForExpiredExtensions
 */
class Tx_TerFe2_Task_CheckForExpiredExtensions extends tx_scheduler_Task {

	/**
	 * @var array
	 */
	protected $blacklistUsers = array();

	/**
	 * Execute Task
	 *
	 * @return bool
	 */
	public function execute() {
		$this->blacklistUsers = array(
			'abandoned_extensions',
			'typo3v4',
			'docteam'
		);
		$expiringExtensions = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'uid, ext_key, frontend_user',
			'tx_terfe2_domain_model_extension',
			'NOT deleted AND NOT expire AND versions = 0 AND tstamp <= ' . strtotime('-1 year'),
			'',
			'frontend_user'
		);

		// group extensions by owner
		$expiredExtensionsByOwner = array();
		foreach ($expiringExtensions as $expiringExtension) {
			if ($expiringExtension['ext_key'] && $expiringExtension['frontend_user']) {
				$expiredExtensionsByOwner[$expiringExtension['frontend_user']][] = $expiringExtension;
			}
		}

		foreach ($expiredExtensionsByOwner as $username => $extensions) {
			if (in_array($username, $this->blacklistUsers, TRUE)) {
				continue;
			}
			$frontendUser = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
				'uid, email',
				'fe_users',
				'username = ' . $this->getDatabaseConnection()->fullQuoteStr($username, 'fe_users')
				. t3lib_BEfunc::BEenableFields('fe_users') . t3lib_BEfunc::deleteClause('fe_users')
			);
			if (!empty($frontendUser) && t3lib_div::validEmail($frontendUser['email'])) {
				$to = $frontendUser['email'];
				$subject = 'Your extension keys are going to expire!';
				/** @var Tx_Fluid_View_StandaloneView $body */
				$body = t3lib_div::makeInstance('Tx_Fluid_View_StandaloneView');
				$body->setTemplatePathAndFilename(t3lib_div::getFileAbsFileName('EXT:ter_fe2/Resources/Private/Templates/Mail/ExpiredExtensions.html'));
				$body->assign('extensions', $extensions);
				/** @var t3lib_mail_Message $mail */
				$mail = t3lib_div::makeInstance('t3lib_mail_Message');
				$mail->addFrom('maintenance@typo3.org');
				$mail->setTo($to);
				$mail->setSubject($subject);
				$mail->setBody($body->render());
				if ($mail->send()) {
					// set every extension of the owner to expire in 30 days
					foreach ($extensions as $extension) {
						$this->getDatabaseConnection()->exec_UPDATEquery(
							'tx_terfe2_domain_model_extension',
							'uid = ' . (int)$extension['uid'],
							array(
								'expire' => strtotime('+30 days')
							)
						);
					}
				}
			}
		}

		// remove expired extensions
		$expiredExtensions = $this->getDatabaseConnection()->exec_SELECTgetRows(
			'uid, ext_key',
			'tx_terfe2_domain_model_extension',
			'NOT deleted AND expire > 0 AND expire <= ' . time() . ' AND versions = 0'
		);

		foreach ($expiredExtensions as $expiredExtension) {
			// Deleted in ter, then delete the key in the ter_fe2 extension table
			if ($expiredExtension['ext_key'] && $this->deleteExtensionKeyInTer($expiredExtension['ext_key'])) {
				$this->getDatabaseConnection()->exec_DELETEquery(
					'tx_terfe2_domain_model_extension',
					'uid = ' . $expiredExtension['uid']
				);
			}
		}

		return TRUE;
	}

	/**
	 * @param $extensionKey
	 * @return bool|resource
	 */
	protected function deleteExtensionKeyInTer($extensionKey) {
		// check if there are extension versions

		$versions = $this->getDatabaseConnection()->exec_SELECTcountRows(
			'extensionkey',
			'tx_ter_extensions',
			'extensionkey=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($extensionKey, 'tx_ter_extensions')
		);

		if (!$versions || $versions === 0) {
			return $this->getDatabaseConnection()->exec_DELETEquery(
				'tx_ter_extensionkeys',
				'extensionkey=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($extensionKey, 'tx_ter_extensions')
			);
		}

		return FALSE;
	}

	/**
	 * @return t3lib_DB
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}