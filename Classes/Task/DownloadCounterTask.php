<?php

	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2012 Thomas Löffler <thomas.loeffler@typo3.org>
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


	class Tx_TerFe2_Task_DownloadCounterTask extends tx_scheduler_Task {

		/**
		 * sums up all version downloads and
		 * writes it to the extension
		 *
		 * @return bool
		 */
		public function execute() {
			$extensions = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'uid',
				'tx_terfe2_domain_model_extension',
				'deleted = 0 AND hidden = 0 AND versions > 0'
			);
			if (!empty($extensions)) {
				foreach ($extensions as $ext) {
					$downloads = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
						'SUM(download_counter) AS downloads, SUM(frontend_download_counter) AS fe_downloads',
						'tx_terfe2_domain_model_version',
						'deleted = 0 AND hidden = 0 AND extension = ' . $ext['uid'],
						'extension'
					);
					$update = array(
						'downloads' => $downloads['downloads'] + $downloads['fe_downloads']
					);
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
						'tx_terfe2_domain_model_extension',
						'uid = ' . $ext['uid'],
						$update
					);
				}
			}

				// set state as string
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'state = 0', array('state' => 'alpha'));
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'state = 1', array('state' => 'beta'));
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'state = 2', array('state' => 'stable'));
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'state = 3', array('state' => 'experimental'));
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'state = 4', array('state' => 'test'));
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'state = 5', array('state' => 'obsolete'));
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'state = 6', array('state' => 'excludeFromUpdates'));
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_terfe2_domain_model_version', 'state = 999', array('state' => 'n/a'));

			return TRUE;
		}
	}

?>