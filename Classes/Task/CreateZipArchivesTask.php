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
	 * Create zip archives from t3x files
	 */
	class Tx_TerFe2_Task_CreateZipArchivesTask extends Tx_TerFe2_Task_AbstractTask {

		/**
		 * @var Tx_TerFe2_Domain_Repository_VersionRepository
		 */
		protected $versionRepository;


		/**
		 * Initialize task
		 *
		 * @return void
		 */
		public function initializeTask() {
			$this->versionRepository = $this->objectManager->get('Tx_TerFe2_Domain_Repository_VersionRepository');
		}


		/**
		 * Execute the task
		 * 
		 * @param integer $lastRun Timestamp of the last run
		 * @param integer $offset Starting point
		 * @param integer $count Element count to process at once
		 * @return boolean TRUE on success
		 */
		protected function executeTask($lastRun, $offset, $count) {
				// TODO: Remove testing values
			$lastRun = 1306920788;
			$offset  = 0;

				// TODO: Implement functionality
			return TRUE;
		}

	}
?>