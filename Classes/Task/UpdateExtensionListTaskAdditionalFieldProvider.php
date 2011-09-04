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
		 * @var array
		 */
		protected $fields = array(
			'providerName'     => 'extensionmanager',
			'extensionsPerRun' => 10,
			'clearCachePages'  => 0,
		);

		/**
		 * @var string
		 */
		protected $prefix = 'terfe2_updateExtensionList_';


		/**
		 * Add some input fields to configure the task
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
					$taskInfo[$value] = $this->defaults[$key];
					if ($parentObject->CMD === 'edit') {
						$taskInfo[$value] = $this->fields[$key] = $task->$key;
					}
				}
			}

				// Get providers
			$providers = array();
			if (!empty($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ter_fe2']['extensionProviders'] as $key => $configuration) {
					$providers[$key] = (!empty($configuration['title']) ? $configuration['title'] : $key);
				}
			}

				// Build html structure
			$fields = array();
			$this->addSelect($fields, 'providerName', $providers);
			$this->addField($fields, 'extensionsPerRun');
			$this->addField($fields, 'clearCachePages');

			return $fields;
		}


		/**
		 * Checks the given values
		 *
		 * @param array $submittedData Reference to the array containing the data submitted by the user
		 * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
		 * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
		 */
		public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
			$providerName = $submittedData[$this->prefix . 'providerName'];
			$extensionsPerRun = $submittedData[$this->prefix . 'extensionsPerRun'];
			return (!empty($providerName) && !empty($extensionsPerRun) && is_numeric($extensionsPerRun));
		}


		/**
		 * Saves given values into task object
		 *
		 * @param array $submittedData Contains data submitted by the user
		 * @param tx_scheduler_Task $task Reference to the current task object
		 * @return void
		 */
		public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
			foreach ($this->fields as $key => $value) {
				$task->$key = $submittedData[$this->prefix . $key];
			}
		}


		/**
		 * Adds the structure of a field to fields array
		 * 
		 * @param $fields Field structure
		 * @param string $fieldName Name of the field
		 * @param string $code Optional code to use
		 * @return void
		 */
		protected function addField(array &$fields, $fieldName, $code = '') {
			if (empty($code)) {
				$code = '<input type="text" name="tx_scheduler[' . $this->prefix . $fieldName . ']" value="' . htmlspecialchars($this->fields[$fieldName]) . '" />';
			}

			$fields[$this->prefix . $fieldName] = array(
				'code'  => $code,
				'label' => 'LLL:EXT:ter_fe2/Resources/Private/Language/locallang.xml:tx_terfe2_task_updateextensionlisttask.' . $fieldName,
			);
		}


		/**
		 * Adds the structure of a select to fields array
		 *
		 * @param $fields Field structure
		 * @param string $fieldName Name of the field
		 * @param array $options Select options
		 * @return void
		 */
		protected function addSelect(array &$fields, $fieldName, array $options) {
			$html = array('<option></option>');

			foreach ($options as $key => $option) {
				$selected = ($key === $this->fields[$fieldName] ? ' selected="selected"' : '');
				if ($key !== $option) {
					$option = Tx_Extbase_Utility_Localization::translate($option);
				}
				$html[]   = '<option value="' . $key . '"' . $selected . '>' . $option . '</option>';
			}

			$code = '<select name="tx_scheduler[' . $this->prefix . $fieldName . ']">' . implode(PHP_EOL, $html) . '</select>';

			$this->addField($fields, $fieldName, $code);
		}

	}
?>