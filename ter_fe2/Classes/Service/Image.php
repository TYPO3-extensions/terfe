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
	 * Service for gallery images
	 */
	class Tx_TerFe2_Service_Image implements t3lib_Singleton {

		/**
		 * @var tslib_cObj
		 */
		protected $contentObject;


		/**
		 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
			$this->contentObject = $configurationManager->getContentObject();
		}


		/**
		 * Converts all images with given settings
		 *
		 * @param array $files All files to process
		 * @param array $settings Image configuration
		 * @param boolean $createTag Returns images with complete tag
		 * @return array Relative image paths
		 */
		public function processImages(array $files, array $settings = array(), $createTag = FALSE) {
			if (empty($files) || empty($settings)) {
				return array();
			}

			$images = array();
			foreach ($files as $key => $file) {
				$file = str_replace(PATH_site, '', $file);
				if ($createTag) {
					$images[$key] = $this->contentObject->cImage($file, array('file.' => $settings));
				} else {
					$info = $this->contentObject->getImgResource($file, $settings);
					$images[$key] = (!empty($info[3]) ? $info[3] : $file);
				}
			}

			return $images;
		}

	}
?>