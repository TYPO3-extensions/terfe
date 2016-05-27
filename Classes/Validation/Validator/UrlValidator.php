<?php
	/***************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2013 Thomas Löffler <thomas.loeffler@typo3.org>
	 *  All rights reserved
	 *
	 *  This class is a backport of the corresponding class of FLOW3.
	 *  All credits go to the v5 team.
	 *
	 *  This script is part of the TYPO3 project. The TYPO3 project is
	 *  free software; you can redistribute it and/or modify
	 *  it under the terms of the GNU General Public License as published by
	 *  the Free Software Foundation; either version 2 of the License, or
	 *  (at your option) any later version.
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
	 ***************************************************************/

	/**
	 * Validator for url
	 *
	 * @package TerFe2
	 * @subpackage Validation\Validator
	 * @version $Id$
	 */
	class Tx_Terfe2_Validation_Validator_UrlValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {

		/**
		 * Returns TRUE, if the given property ($propertyValue) is a valid url and has a HTTP(S) scheme.
		 *
		 * If at least one error occurred, the result is FALSE.
		 *
		 * @param mixed $value The value that should be validated
		 * @return boolean TRUE if the value is valid, FALSE if an error occured
		 */
		public function isValid($value) {
			$this->errors = array();
			$value = (string)$value;
			if ($value !== '' && ($value !== filter_var($value, FILTER_SANITIZE_URL) || !$this->hasValidScheme($value))) {
				$this->addError('The given subject was not a valid url.', 1364118054);
				return FALSE;
			}
			return TRUE;
		}

		/**
		 * @param string $url
		 * @return boolean
		 */
		private function hasValidScheme($url) {
			return in_array(parse_url($url, PHP_URL_SCHEME), array('http', 'https'), TRUE);
		}
	}

?>
