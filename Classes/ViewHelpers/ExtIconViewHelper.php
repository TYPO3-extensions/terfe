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
	 * Extension Icon View Helper
	 *
	 * @version $Id$
	 * @copyright Copyright belongs to the respective authors
	 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
	 */
	class Tx_TerFe2_ViewHelpers_ExtIconViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {

		/**
		* @var string
		*/
		protected $tagName = 'img';

		/**
		 * @var Tx_TerFe2_ExtensionProvider_ExtensionProvider
		 */
		protected $extensionProvider;


		/**
		 * Inject Extension Provider
		 *
		 * @param Tx_TerFe2_ExtensionProvider_ExtensionProvider $extensionProvider
		 * @return void
		 */
		public function injectExtensionProvider(Tx_TerFe2_ExtensionProvider_ExtensionProvider $extensionProvider) {
			$this->extensionProvider = $extensionProvider;
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
		 * Renders an extension icon for given Version object
		 *
		 * @param $version Version object
		 * @param string $fileType File type
		 * @return string Rendered image tag
		 */
		public function render($version = NULL, $fileType = 'gif') {
			if ($version === NULL) {
				$version = $this->renderChildren();
			}

			if (!$version instanceof Tx_TerFe2_Domain_Model_Version) {
				throw new Exception('No valid Version object given');
			}

			$urlToFile = $this->extensionProvider->getExtensionIcon($version, $fileType);
			$this->tag->addAttribute('src', $urlToFile);
			return $this->tag->render();
		}

	}
?>