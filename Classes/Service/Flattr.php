<?php
	/*******************************************************************
	 *  Copyright notice
	 *
	 *  (c) 2012 Thomas Loeffler <loeffler@spooner-web.de>
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
	 * Service for flattr buttons
	 */
	class Tx_TerFe2_Service_Flattr implements t3lib_Singleton {

		/**
		 * url for checking if a thing exists for an url
		 *
		 * @var string
		 */
		protected $flattrThingCheck = 'https://api.flattr.com/rest/v2/things/lookup/?url=';


		/**
		 * checks if a flattrable thing exists on given url
		 *
		 * @param $url
		 * @return bool|mixed
		 */
		public function checkForThing($url) {
			$jsonResult = t3lib_div::getURL($this->flattrThingCheck.urlencode($url));
			$result = json_decode($jsonResult);
			if ($result->type == 'thing' and $result->id != 0) {
				return $result;
			}
			return FALSE;
		}

	}
?>