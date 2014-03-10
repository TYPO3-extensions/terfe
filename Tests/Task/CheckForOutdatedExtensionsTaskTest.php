<?php

/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Thomas Löffler <thomas.loeffler@typo3.org>
 *  (c) 2014 Philipp Gampe <philipp.gampe@typo3.org>
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

class Tx_TerFe2_Test_Task_CheckForOutdatedExtensionsTest extends tx_phpunit_testcase {

	/**
	 * @var Tx_TerFe2_Task_CheckForOutdatedExtensions
	 */
	protected $subject = NULL;


	/**
	 * @return void
	 */
	public function setUp() {
		$this->subject = $this->getAccessibleMock(
			'Tx_TerFe2_Task_CheckForOutdatedExtensions',
			array('dummy')
		);
	}

	/**
	 * @test
	 * @return void
	 */
	public function subjectExists() {
		$this->assertInstanceOf(
			'Tx_TerFe2_Task_CheckForOutdatedExtensions',
			$this->subject
		);
	}

	/**
	 * @test
	 * @param Tx_TerFe2_Domain_Model_Relation $dependency
	 * @param array $supportedCoreVersions
	 * @dataProvider isVersionDependingOnAnActiveSupportedTypo3VersionReturnsTrueForSupportedVersionsDataProvider
	 * @return void
	 */
	public function isVersionDependingOnAnActiveSupportedTypo3VersionReturnsTrueForSupportedVersions($dependency, $supportedCoreVersions) {
		$this->subject->_set('supportedCoreVersions', $supportedCoreVersions);
		$this->assertTrue(
			$this->subject->isVersionDependingOnAnActiveSupportedTypo3Version($dependency)
		);
	}

	/**
	 * Data provider for isVersionDependingOnAnActiveSupportedTypo3VersionReturnsTrueForSupportedVersions
	 *
	 * @return array
	 */
	public function isVersionDependingOnAnActiveSupportedTypo3VersionReturnsTrueForSupportedVersionsDataProvider() {

		$supportedCoreVersions = array(
			'latest' => '6.2.1',
			'oldest' => '4.5.0beta1',
			'all' => array(
				'4.5',
				'4.7',
				'6.0',
				'6.1',
				'6.2'
			)
		);

		return array(
			'Extension version 4.5 only is valid' => array(
				$this->buildRelation('4.5.0', '4.5.99'),
				$supportedCoreVersions
			),
			'Extension version 4.3 - 4.6 is valid because of supported 4.5' => array(
				$this->buildRelation('4.3.0', '4.6.99'),
				$supportedCoreVersions
			),
			'Extension version 4.7 only is valid' => array(
				$this->buildRelation('4.7.0', '4.7.99'),
				$supportedCoreVersions
			),
			'Extension version 6.0 only is valid' => array(
				$this->buildRelation('6.0.0', '6.0.99'),
				$supportedCoreVersions
			),
			'Extension version 6.1 only is valid' => array(
				$this->buildRelation('6.1.0', '6.1.99'),
				$supportedCoreVersions
			),
			'Extension version 6.2 only is valid' => array(
				$this->buildRelation('6.2.0', '6.2.99'),
				$supportedCoreVersions
			),
		);
	}

	/**
	 * @test
	 * @param Tx_TerFe2_Domain_Model_Relation $dependency
	 * @param array $supportedCoreVersions
	 * @dataProvider isVersionDependingOnAnActiveSupportedTypo3VersionReturnsFalseForUnsupportedVersionsDataProvider
	 * @return void
	 */
	public function isVersionDependingOnAnActiveSupportedTypo3VersionReturnsFalseForUnsupportedVersions($dependency, $supportedCoreVersions) {
		$this->subject->_set('supportedCoreVersions', $supportedCoreVersions);
		$this->assertFalse(
			$this->subject->isVersionDependingOnAnActiveSupportedTypo3Version($dependency)
		);
	}

	/**
	 * Data provider for isVersionDependingOnAnActiveSupportedTypo3VersionReturnsTrueForSupportedVersions
	 *
	 * @return array
	 */
	public function isVersionDependingOnAnActiveSupportedTypo3VersionReturnsFalseForUnsupportedVersionsDataProvider() {

		$supportedCoreVersions = array(
			'latest' => '6.2.1',
			'oldest' => '4.5.0beta1',
			'all' => array(
				'4.5',
				'4.7',
				'6.0',
				'6.1',
				'6.2'
			)
		);

		return array(
			'Extension version 4.3 only is invalid' => array(
				$this->buildRelation('4.3.0', '4.3.99'),
				$supportedCoreVersions
			),
			'Extension version 4.6 only is invalid' => array(
				$this->buildRelation('4.6.0', '4.6.99'),
				$supportedCoreVersions
			),
		);
	}

	/**
	 * @param string $minVersion
	 * @param string $maxVersion
	 *
	 * @return Tx_TerFe2_Domain_Model_Relation
	 */
	protected function buildRelation($minVersion, $maxVersion) {
		$relation = new Tx_TerFe2_Domain_Model_Relation();
		$relation->setMinimumVersion(t3lib_utility_VersionNumber::convertVersionNumberToInteger($minVersion));
		$relation->setMaximumVersion(t3lib_utility_VersionNumber::convertVersionNumberToInteger($maxVersion));

		return $relation;
	}
}