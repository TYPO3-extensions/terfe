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
	 * Additional field provider for the update extension list task
	 */
	class Tx_TerFe2_Task_UpdateExtensionListTaskAdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider {

		/**
		 * @var integer Default number of extensions to fetch at once
		 */
		protected $defaultExtensionsPerRun = 10;

		/**
		 * @var string Default provider name
		 */
		protected $defaultProviderName = 'extensionmanager';

		/**
		 * @var string Default clear cache pages
		 */
		protected $defaultClearCachePages = '';

		/**
		 * @var array
		 */
		protected $fields = array(
			'extensionsPerRun' => 'terfe2_updateExtensionList_extensionsPerRun',
			'providerName'     => 'terfe2_updateExtensionList_providerName',
			'clearCachePages'  => 'terfe2_updateExtensionList_clearCachePages',
		);


		/**
		 * Add an integer input field for the number of extensions
		 *
		 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
		 * @param object $task When editing, reference to the current task object. Null when adding.
		 * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
		 * @return array Array containing all the information pertaining to the additional fields
		 */
		public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $parentObject) {
				// Initialize fields
			foreach ($this->fields as $key => $value) {
				if (!isset($taskInfo[$value])) {
					$attribute = 'default' . ucfirst($key);
					$taskInfo[$value] = $this->$attribute;
					if ($parentObject->CMD === 'edit') {
						$taskInfo[$value] = $task->$key;
					}
				}
			}

				// Add html structure for extensions per run field
			$additionalFields[$this->fields['extensionsPerRun']] = array(
				'code'  => '<input type="text" name="tx_scheduler[' . $this->fields['extensionsPerRun'] . ']" value="' . (int) $taskInfo[$this->fields['extensionsPerRun']] . '" />',
				'label' => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang.xml:tx_terfe2_task_updateextensionlisttask.extensionsPerRun',
			);

				// Add html structure for provider name field
			$options = array();
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'] as $key => $configuration) {
					$options[$key] = (!empty($configuration['title']) ? $configuration['title'] : $key);
				}
			}
			$additionalFields[$this->fields['providerName']] = array(
				'code'  => $this->getSelect($this->fields['providerName'], $options, $taskInfo[$this->fields['providerName']]),
				'label' => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang.xml:tx_terfe2_task_updateextensionlisttask.providerName',
			);

				// Add html structure for clear cache pages field
			$additionalFields[$this->fields['clearCachePages']] = array(
				'code'  => '<input type="text" name="tx_scheduler[' . $this->fields['clearCachePages'] . ']" value="' . (int) $taskInfo[$this->fields['clearCachePages']] . '" />',
				'label' => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang.xml:tx_terfe2_task_updateextensionlisttask.clearCachePages',
			);

			return $additionalFields;
		}


		/**
		 * Checks if the given value is an integer
		 *
		 * @param array $submittedData Reference to the array containing the data submitted by the user
		 * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
		 * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
		 */
		public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
			$extensionsPerRun = $submittedData[$this->fields['extensionsPerRun']];
			$providerName = $submittedData[$this->fields['providerName']];
			return (!empty($extensionsPerRun) && is_numeric($extensionsPerRun) && !empty($providerName));
		}


		/**
		 * Saves given integer value in task object
		 *
		 * @param array $submittedData Contains data submitted by the user
		 * @param tx_scheduler_Task $task Reference to the current task object
		 * @return void
		 */
		public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
			$task->extensionsPerRun = (int) $submittedData[$this->fields['extensionsPerRun']];
			$task->providerName = $submittedData[$this->fields['providerName']];
			$task->clearCachePages = $submittedData[$this->fields['clearCachePages']];
		}


		/**
		 * Returns the HTML code of a select field
		 *
		 * @param string $fieldName Field name
		 * @param array $options Select options
		 * @param string $selectedKey Selected option key
		 * @return string
		 */
		protected function getSelect($fieldName, array $options, $selectedKey = '') {
			$html = array('<option></option>');

			foreach ($options as $key => $option) {
				$selected = ($key === $selectedKey ? ' selected="selected"' : '');
				if ($key !== $option) {
					$option = Tx_Extbase_Utility_Localization::translate($option);
				}
				$html[]   = '<option value="' . $key . '"' . $selected . '>' . $option . '</option>';
			}

			return '<select name="tx_scheduler[' . $fieldName . ']">' . implode(PHP_EOL, $html) . '</select>';
		}

	}
?>