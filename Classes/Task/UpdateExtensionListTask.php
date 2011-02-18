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

	require_once(PATH_typo3conf.'ext/ter_fe2/Classes/Service/FileHandlerService.php');

	/**
	 * Update extension list task
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_Task_UpdateExtensionListTask extends tx_scheduler_Task {

		/**
		 * Public method, usually called by scheduler.
		 *
		 * @return boolean True on success
		 */
		public function execute() {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ter_fe2']);
			$basicDirectory = $extConf['ter_directory']?$extConf['ter_directory']:'fileadmin/ter/';

			$this->fileHandlerService = t3lib_div::makeInstance('Tx_TerFe2_Service_FileHandlerService');
			$this->registry = t3lib_div::makeInstance('t3lib_Registry');
			$lastRun = $this->registry->get('tx_scheduler', 'lastRun');

			// get all t3x files in the target directory changed since the last run
			$filesFound = $this->fileHandlerService->getFilesByTypeAndByLastChange($basicDirectory, 't3x', 99999, TRUE);

			if (!empty($filesFound)) {
				foreach ($filesFound as $key => $t3xFile) {
					$extensionDetails = $this->fileHandlerService->unpackT3xFile($t3xFile);
					t3lib_div::debug($extensionDetails['EM_CONF']);
					unset($filesFound[$key]);
				}
			}

		}

	}
?>