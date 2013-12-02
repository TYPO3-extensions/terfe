<?php
/*******************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Helmut Hummel <helmut.hummel@typo3.org>
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
 * Tests for the Extension Model
 */
class Tx_TerFe2_Domain_Model_ExtensionTest extends Tx_Phpunit_TestCase {

	/**
	 * @var Tx_TerFe2_Domain_Model_Extension
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_TerFe2_Domain_Model_Extension();
		$this->addVersionsToExtension($this->fixture);
	}

	public function tearDown() {
		unset($this->fixture);
	}

	protected function addVersionsToExtension($extension, $amount = 4){
		for ($index=1; $index <= $amount; $index++) {
			$version = new Tx_TerFe2_Domain_Model_Version();
			$version->setExtension($this->fixture);
			$version->setVersionNumber($index);
			$version->setVersionString('0.0.' . (string)$index);
			$extension->addVersion($version);
		}
	}

	/**
	 * @test
	 */
	public function removeLastVersionOfExtensionSetsTheNextExtensionAsLastVersion() {
		$lastVersion = $this->fixture->getLastVersion();
		$allVersions = $this->fixture->getReverseVersionsByVersionNumber();
		array_shift($allVersions);
		$previousLastVersion = array_shift($allVersions);

		$this->fixture->removeVersion($lastVersion);

		$this->assertSame($previousLastVersion, $this->fixture->getLastVersion());
	}

	/**
	 * @test
	 */
	public function removeNotLastVersionOfExtensionDoesNotAffectLastVersion() {
		$lastVersion = $this->fixture->getLastVersion();
		$allVersions = $this->fixture->getReverseVersionsByVersionNumber();
		array_shift($allVersions);
		$previousLastVersion = array_shift($allVersions);

		$this->fixture->removeVersion($previousLastVersion);

		$this->assertSame($lastVersion, $this->fixture->getLastVersion());
	}

	/**
	 * @test
	 * @expectedException UnexpectedValueException
	 */
	public function tryingToRemoveAVersionWhichDoesNotBelongToTheExtensionThrowsException() {
		$version = new Tx_TerFe2_Domain_Model_Version();
		$this->fixture->removeVersion($version);
	}

	/**
	 * @test
	 */
	public function removingTheLastVersionSetsLastVersionToNull() {
		$extension = new Tx_TerFe2_Domain_Model_Extension();
		$this->addVersionsToExtension($extension, 1);
		$extension->removeVersion($extension->getLastVersion());

		$this->assertNull($extension->getLastVersion());
	}
}

?>