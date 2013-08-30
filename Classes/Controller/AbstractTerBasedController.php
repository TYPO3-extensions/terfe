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
	abstract class Tx_TerFe2_Controller_AbstractTerBasedController extends Tx_TerFe2_Controller_AbstractController {

		/**
		 * @var array
		 */
		protected $frontendUser = array();

		/**
		 * @var Tx_TerFe2_Service_Ter
		 */
		protected $terConnection;

		/**
		 * @var array
		 */
		protected $terSettings = array();

		/**
		 * @var array
		 */
		protected $terAccount = array();


		/**
		 * Initialize action
		 *
		 * @return void
		 */
		public function initializeAction() {
			parent::initializeAction();
			$this->frontendUser  = (!empty($GLOBALS['TSFE']->fe_user->user) ? $GLOBALS['TSFE']->fe_user->user : array());
			$this->terSettings   = (!empty($this->settings['terConnection']) ? $this->settings['terConnection'] : array());
			$this->terAccount    = $this->getTerAccount();
			$this->terConnection = $this->getTerConnection();
		}


		/**
		 * Initializes the view, add login state to template variables
		 *
		 * @return void
		 */
		public function initializeView() {
			$this->view->assign('loggedIn', FALSE);
			if (!empty($this->frontendUser)) {
				$this->view->assign('loggedIn', TRUE);
				$this->view->assign('userName', $this->frontendUser['username']);
				$this->view->assign('userId',   $this->frontendUser['uid']);
			}
		}


		/**
		 * Returns the account data from current frontend user
		 *
		 * @return array Frontend user account data
		 */
		protected function getTerAccount() {
			$username = $this->frontendUser['username'];
			$password = $this->frontendUser['password'];
			if (!empty($this->terSettings['username']) && !empty($this->terSettings['password'])) {
				$username = $this->terSettings['username'];
				$password = $this->terSettings['password'];
			}
			return array(
				'username' => $username,
				'password' => $password,
			);
		}


		/**
		 * Create a connection to the TER server
		 *
		 * @return Tx_TerFe2_Service_Ter The TER connection
		 */
		protected function getTerConnection() {
				// Check the wsdl uri
			if (empty($this->terSettings['wsdl'])) {
				throw new Exception('No wsdl set to connect to TER server');
			}

				// Create connection
			$wsdl = $this->terSettings['wsdl'];
			$wsdl = 'http://t3org.dev/wsdl/tx_ter_wsdl.php';
			return $this->objectManager->get('Tx_TerFe2_Service_Ter', $wsdl, $this->terAccount['username'], $this->terAccount['password']);
		}

	}
?>