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
	 * Abstract controller
	 */
	abstract class Tx_TerFe2_Controller_AbstractController extends Tx_Extbase_MVC_Controller_ActionController {

		/**
		 * Pre-parse TypoScript setup
		 *
		 * @return void
		 */
		public function initializeAction() {
				// Pre-parse settings
			$this->settings = Tx_TerFe2_Utility_TypoScript::parse($this->settings);

				// Initialize the controller
			$this->initializeController();
		}


		/**
		 * Override in concrete controller to initialize it
		 *
		 * @return void
		 */
		protected function initializeController() {

		}


		/**
		 * Translate a label
		 *
		 * @param string $label Label to translate
		 * @param array $arguments Optional arguments array
		 * @return string Translated label
		 */
		protected function translate($label, array $arguments = array()) {
			$extensionKey = $this->request->getControllerExtensionKey();
			return Tx_Extbase_Utility_Localization::translate($label, $extensionKey, $arguments);
		}


		/**
		 * Send flash message and redirect to given action
		 *
		 * @param string $message Identifier of the message to send
		 * @param string $action Name of the action
		 * @param string $controller Unqualified object name of the controller
		 * @param string $extension Name of the extension containing the controller
		 * @param array $arguments Arguments to pass to the target action
		 * @return void
		 */
		protected function redirectWithMessage($message, $action, $controller = NULL, $extension = NULL, array $arguments = NULL) {
			$this->flashMessageContainer->add($message);
			$this->clearPageCache($GLOBALS['TSFE']->id);
			$this->redirect($action, $controller, $extension, $arguments);
		}


		/**
		 * Send flash message and forward to given action
		 *
		 * @param string $message Identifier of the message to send
		 * @param string $action Name of the action
		 * @param string $controller Unqualified object name of the controller
		 * @param string $extension Name of the extension containing the controller
		 * @param array $arguments Arguments to pass to the target action
		 * @return void
		 */
		protected function forwardWithMessage($message, $action, $controller = NULL, $extension = NULL, array $arguments = NULL) {
			$this->flashMessageContainer->add($message);
			$this->clearPageCache($GLOBALS['TSFE']->id);
			$this->forward($action, $controller, $extension, $arguments);
		}


		/**
		 * Clear cache of given pages
		 *
		 * @param string $pages List of page ids
		 * @return void
		 */
		protected function clearPageCache($pages) {
			if (!empty($pages)) {
				$pages = t3lib_div::intExplode(',', $pages, TRUE);
				Tx_Extbase_Utility_Cache::clearPageCache($pages);
			}
		}

	}
?>