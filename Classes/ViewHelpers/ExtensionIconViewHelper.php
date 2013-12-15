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
	 * Extension icon view helper
	 */
	class Tx_TerFe2_ViewHelpers_ExtensionIconViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {

		/**
		* @var string
		*/
		protected $tagName = 'img';

		/**
		 * @var Tx_TerFe2_Provider_ProviderManager
		 */
		protected $providerManager;


		/**
		 * Inject provider manager
		 *
		 * @param Tx_TerFe2_Provider_ProviderManager $providerManager
		 * @return void
		 */
		public function injectProviderManager(Tx_TerFe2_Provider_ProviderManager $providerManager) {
			$this->providerManager = $providerManager;
		}


		/**
		 * Initialize arguments
		 *
		 * @return void
		 */
		public function initializeArguments() {
			parent::initializeArguments();
			$this->registerUniversalTagAttributes();
			$this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', TRUE);
		}


		/**
		 * Renders an extension icon for given version object
		 *
		 * @param Tx_TerFe2_Domain_Model_Version $version Version object
		 * @param string $fileType File type
		 * @return string Rendered image tag
		 */
		public function render(Tx_TerFe2_Domain_Model_Version $version = NULL, $fileType = 'png') {
			if ($version === NULL) {
				$version = $this->renderChildren();
			}

			$imageUrl = '';
			$provider = $version->getExtensionProvider();
			if (!empty($provider)) {
				$imageUrl = $this->providerManager->getProvider($provider)->getIconUrl($version, $fileType);
				if (empty($imageUrl) || !file_exists(Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($imageUrl))) {
					$imageUrl = $this->providerManager->getProvider($provider)->getIconUrl($version, 'gif');
				}
			}

			if (empty($imageUrl) || !file_exists(Tx_TerFe2_Utility_File::getAbsolutePathFromUrl($imageUrl))) {
				$imageUrl = t3lib_div::locationHeaderUrl('typo3/clear.gif');
			}

			$this->tag->addAttribute('src', $imageUrl);
			$this->tag->addAttribute('height', 16);
			$this->tag->addAttribute('width', 16);
			return $this->tag->render();
		}

	}
?>