<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
	 *
	 *  All rights reserved
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as
	 *  published by the Free Software Foundation; either version 2 of
	 *  the License, or (at your option) any later version.
	 *
	 *  The GNU General Public License can be found at
	 *  http://www.gnu.org/copyleft/gpl.html.
	 *
	 *  This script is distributed in the hope that it will be useful,
	 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *  GNU General Public License for more details.
	 *
	 *  This copyright notice MUST APPEAR in all copies of the script!
	 ******************************************************************/

	/**
	 * Controller for the extension review
	 */
	class Tx_TerFe2_Controller_ReviewController extends Tx_TerFe2_Controller_AbstractTerBasedController {

		/**
		 * @var Tx_Extbase_Persistence_Manager
		 */
		protected $persistenceManager;


		/**
		 * Initializes the controller
		 *
		 * @return void
		 */
		protected function initializeController() {
			$this->persistenceManager = $this->objectManager->get('Tx_Extbase_Persistence_Manager');
		}


		/**
		 * Set unsecure flag of all given versions
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension The extension to update
		 * @param mixed $unsecureVersions Version UIDs or empty string of no version was selected
		 * @return void
		 */
		public function updateAction(Tx_TerFe2_Domain_Model_Extension $extension, $unsecureVersions) {
			$unsecureVersions = (is_array($unsecureVersions) ? $unsecureVersions : array());
			$extensionKey = $extension->getExtKey();
			$versions = $extension->getVersions();
			$persist = FALSE;

			foreach ($versions as $version) {
				$versionString = $version->getVersionString();
				$actionParameters = array('extension' => $extension);

				$reviewState = 0;
				if (in_array($version->getUid(), $unsecureVersions)) {
					$reviewState = -1;
				}

				if ($reviewState === $version->getReviewState()) {
					continue;
				}

				$error = '';
				if ($this->terConnection->setReviewState($extensionKey, $versionString, $reviewState, $error)) {
					$version->setReviewState($reviewState);
					$persist = TRUE;
				} else {
					$message = $this->translate('msg.reviewstate_not_enabled', array($versionString, $error));
					$this->redirectWithMessage($message, 'show', 'Extension', NULL, $actionParameters);
				}
			}

			if ($persist) {
				$this->persistenceManager->persistAll();
				$this->redirectWithMessage($this->translate('msg.reviewstate_enabled'), 'show', 'Extension', NULL, $actionParameters);
			}

			$this->redirectWithMessage($this->translate('msg.reviewstate_not_changed'), 'show', 'Extension', NULL, $actionParameters);
		}

	}
?>