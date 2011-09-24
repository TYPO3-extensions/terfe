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
	 * Interface for extension providers
	 */
	interface Tx_TerFe2_Provider_ProviderInterface extends t3lib_Singleton {

		/*
		 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
		 * @return void
		 */
		public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager);


		/**
		 * @param Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory
		 * @return void
		 */
		public function injectDataMapFactory(Tx_Extbase_Persistence_Mapper_DataMapFactory $dataMapFactory);


		/**
		 * @param Tx_Extbase_Reflection_Service $reflectionService
		 * @return void
		 */
		public function injectReflectionService(Tx_Extbase_Reflection_Service $reflectionService);


		/**
		 * Set configuration for the DataProvider
		 *
		 * @param array $configuration TypoScript configuration
		 * @return void
		 */
		public function setConfiguration(array $configuration);


		/**
		 * Returns all extensions since last run
		 *
		 * @param integer $lastRun Timestamp of last update
		 * @param integer $offset Offset to start with
		 * @param integer $count Extension count to load
		 * @return array Extension rows
		 */
		public function getExtensions($lastRun, $offset, $count);


		/**
		 * Returns the url to an extension related icon
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @param string $fileType File type
		 * @return string Url to icon file
		 */
		public function getIconUrl(Tx_TerFe2_Domain_Model_Version $version, $fileType);


		/**
		 * Returns the url to an extension related file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @param string $fileType File type
		 * @return string Url to file
		 */
		public function getFileUrl(Tx_TerFe2_Domain_Model_Version $version, $fileType);


		/**
		 * Returns name of an extension related file
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @param string $fileType File type
		 * @return string File name
		 */
		public function getFileName(Tx_TerFe2_Domain_Model_Version $version, $fileType);

		/**
		 * Returns the download count for given version
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @return integer Download count
		 */
		public function getDownloadCount(Tx_TerFe2_Domain_Model_Version $version);

	}
?>