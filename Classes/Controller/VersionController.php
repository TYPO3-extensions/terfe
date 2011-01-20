<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2011 Thomas Loeffler <loeffler@spooner-web.de>, Spooner Web
	 *           Kai Vogel <kai.vogel@speedprogs.de>, Speedprogs.de
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
	 * Controller for the Version object
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Controller_VersionController extends Tx_Extbase_MVC_Controller_ActionController {

		/**
		 * @var Tx_TerFe2_Domain_Repository_VersionRepository
		 */
		protected $versionRepository;


		/**
		 * Initializes the current action
		 *
		 * @return void
		 */
		protected function initializeAction() {
			$this->versionRepository = t3lib_div::makeInstance('Tx_TerFe2_Domain_Repository_VersionRepository');
		}


		/**
		 * List action for this controller. Displays all Versions.
		 * 
		 * @param Tx_TerFe2_Domain_Model_Extension $extension An existing Extension object
		 */
		public function indexAction(Tx_TerFe2_Domain_Model_Extension $extension) {
			$versions = $this->versionRepository->findByExtension($extension);
			$this->view->assign('versions', $versions);
		}


		/**
		 * Action that displays a single Version
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version The Version to display
		 */
		public function showAction(Tx_TerFe2_Domain_Model_Version $version) {
			$this->view->assign('version', $version);
		}


		/**
		 * Displays a form for creating a new Version
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension An existing Extension object
		 * @param Tx_TerFe2_Domain_Model_Version $newVersion A fresh Version object taken as a basis for the rendering
		 * @dontvalidate $newVersion
		 */
		public function newAction(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Version $newVersion = NULL) {
			$this->view->assign('extension', $extension);
			$this->view->assign('newVersion', $newVersion);
		}


		/**
		 * Creates a new Version and forwards to the index action.
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension An existing Extension object
		 * @param Tx_TerFe2_Domain_Model_Version $newVersion A fresh Version object which has not yet been added to the repository
		 */
		public function createAction(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Version $newVersion) {
			$extension->addVersion($newVersion);
			$extension->setLastUpdate(new DateTime());
			$newVersion->setExtension($extension);
			$this->redirect('index', 'Extension');
		}

	}
?>