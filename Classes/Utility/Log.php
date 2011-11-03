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
	 * Utilities to manage logging
	 */
	class Tx_TerFe2_Utility_Log {

		/**
		 * Add message to developer log
		 *
		 * @param string $message The log message
		 * @param string $extensionKey The extension
		 * @param integer $severity The severity of the event
		 * @param string $target Target of the logging
		 * @return void
		 */
		public static function addMessage($message, $extensionKey, $severity = 0) {
			if (empty($message) || empty($extensionKey)) {
				return;
			}

			$method = self::getCallingMethod();
			t3lib_div::devLog($message . ' [' . $method . ']', $extensionKey, $severity);
		}


		/**
		 * Add message to system log
		 *
		 * @param string $message The log message
		 * @param string $extensionKey The extension
		 * @param integer $severity The severity of the event
		 * @param string $target Target of the logging
		 * @return void
		 */
		public static function addSystemMessage($message, $extensionKey, $severity = 0) {
			if (empty($message) || empty($extensionKey)) {
				return;
			}

			$method = self::getCallingMethod();
			t3lib_div::sysLog($message . ' [' . $method . ']', $extensionKey, $severity);
		}


		/**
		 * Add message to BE user log
		 *
		 * @param string $message The log message
		 * @param string $extensionKey The extension
		 * @param integer $severity The severity of the event
		 * @return void
		 */
		public static function addUserMessage($message, $extensionKey = '', $severity = 0) {
			if (empty($message) || empty($GLOBALS['BE_USER'])) {
				return;
			}

			$method = self::getCallingMethod();
			$GLOBALS['BE_USER']->simplelog($message . ' [' . $method . ']', $extensionKey, $severity);
		}


		/**
		 * Returns the class and method firing the log event
		 *
		 * @return string Class and method
		 */
		protected static function getCallingMethod() {
			$backtrace = debug_backtrace();
			if (count($backtrace) < 3) {
				return '';
			}

				// Remove internal method calls
			array_shift($backtrace);
			array_shift($backtrace);

				// Get calling class and method
			$event = array_shift($backtrace);
			if (!empty($event['class']) && !empty($event['function'])) {
				return $event['class'] . '::' . $event['function'];
			}

			return '';
		}

	}
?>