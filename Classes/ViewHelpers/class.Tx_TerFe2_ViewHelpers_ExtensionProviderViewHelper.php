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

	class Tx_TerFe2_ViewHelpers_ExtensionProviderViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

		/**
		 * @var Tx_TerFe2_ExtensionProvider_FileProvider
		 */
		protected $extensionProvider;

		/**
		 * @var array
		 */
		protected $configuration;


		/**
		 * @param array $configuration
		 * @return void
		 */
		public function injectConfiguration(array $configuration) {
			$this->configuration = $configuration;
		}


		/**
		 * Renders the path, basename and file type out of given extension key, version string and file type
		 *
		 * @param string $extKey Extension key
		 * @param string $versionString Version string
		 * @param string $fileType File type
		 *
		 * @return string Rendered path, basename and file type.
		 */
		public function render($extKey, $versionString, $fileType) {
			if (empty($this->extensionProvider)) {
				$this->extensionProvider = t3lib_div::makeInstance('Tx_TerFe2_ExtensionProvider_FileProvider');
				#$this->extensionProvider->injectConfiguration($this->configuration);
			}

			$pathAndBaseName = $this->extensionProvider->getPathAndBasename($extKey, $versionString);
			$pathAndBaseName = rtrim($pathAndBaseName, '. ') . '.' . $fileType;

			return $pathAndBaseName;
		}

	}
?>