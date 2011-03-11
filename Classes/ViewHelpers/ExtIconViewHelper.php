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
		 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
		 */
		protected $configurationManager;

		/**
		 * @var array
		 */
		protected $settings;


		/**
		 * Initialize configuration, will be invoked just before the render method
		 *
		 * @return void
		 */
		public function initialize() {
			parent::initialize();

			// Get TypoScript configuration
			if (empty($this->settings)) {
				if (TYPO3_MODE == 'BE') {
					$setup = Tx_TerFe2_Utility_TypoScript::getSetup();
					$this->settings = Tx_TerFe2_Utility_TypoScript::parse($setup['settings.'], FALSE);
				} else {
					$this->settings = $this->configurationManager->getConfiguration(
						Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
					);
					$this->settings = Tx_TerFe2_Utility_TypoScript::parse($this->settings);
				}
			}
		}


		/**
		 * Inject Configuration Manager
		 *
		 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
		 * @return void
		 */
		public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
			$this->configurationManager = $configurationManager;
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
		 * Renders an extension icon of given extension and version
		 *
		 * @param Tx_TerFe2_Domain_Model_Extension $extension An existing Extension object
		 * @param Tx_TerFe2_Domain_Model_Version $version An existing Version object
		 * @param string $fileType File type
		 * @return string Rendered image tag
		 */
		public function render(Tx_TerFe2_Domain_Model_Extension $extension, Tx_TerFe2_Domain_Model_Version $version, $fileType = 'gif') {
			// Check configuration
			$providerIdent = $version->getExtensionProvider();
			if (empty($providerIdent) || empty($this->settings['extensionProviders'][$providerIdent])) {
				return '';
			}

			// Get className of the Provider
			$providerConf = $this->settings['extensionProviders'][$providerIdent];
			if (empty($providerConf['className'])) {
				return '';
			}

			// Load Extension Provider and get URL to file
			$urlToFile = '';
			$extensionProvider = t3lib_div::makeInstance($providerConf['className']);
			if ($extensionProvider instanceof Tx_TerFe2_ExtensionProvider_AbstractExtensionProvider) {
				$extensionProvider->setConfiguration($providerConf);
				$extKey = $extension->getExtKey();
				$versionString = $version->getVersionString();
				$urlToFile = $extensionProvider->getUrlToIcon($extKey, $versionString, $fileType);
			}
			if (empty($urlToFile)) {
				return '';
			}

			// Build image
			$this->tag->addAttribute('src', $urlToFile);
			return $this->tag->render();
		}

	}
?>