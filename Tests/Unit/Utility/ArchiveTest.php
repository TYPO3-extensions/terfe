<?php
/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class Tx_TerFe2_Utility_ArchiveTest
 */
class Tx_TerFe2_Utility_ArchiveTest extends tx_phpunit_testcase {

	/**
	 * @test
	 * @param string $code The code of the em_conf.php file
	 * @param array $expected The expected $EM_CONF array
	 * @dataProvider extractEmConfReturnsFullAndValidDataArrayDataProvider
	 */
	public function extractEmConfReturnsFullAndValidDataArray($code, $expected) {
		$subject = $this->getAccessibleMock(
			'Tx_TerFe2_Utility_Archive',
			array('dummy')
		);
		$emConf = $subject->_call('extractEmConf', $code);

		$this->assertNotNull($emConf);
		$this->assertSame($expected, $emConf);
	}

	/**
	 * Data provider for extractEmConfReturnsFullAndValidDataArray
	 *
	 * @return array
	 */
	public function extractEmConfReturnsFullAndValidDataArrayDataProvider() {
		$testCases = array();
		$pathToFixtures = __DIR__ . '/../Fixtures/EmConfFiles/';

		$files = scandir($pathToFixtures);
		foreach ($files as $file) {
			if ($file === '.' || $file === '..') {
				continue;
			}

			$code = file_get_contents($pathToFixtures . $file);
			$codeWithoutPhp  = str_replace(array('<?php', '<?', '?>'), '', $code);
			unset($EM_CONF);
			eval($codeWithoutPhp);
			$emConf = reset($EM_CONF);
			$testCases['ext_emconf.php of extension ' . substr($file, 0 , -4)] = array(
				$code,
				$emConf,
			);
		}

		return $testCases;
	}

	/**
	 * @test
	 */
	public function extractEmConfReturnsSaveArrayOnlyForInvalidNodeFunction() {
		$code = '<?php $EM_CONF[$_EXTKEY] = array(\'bar\' => \'baz\'); function foo() {} ?>';
		$expected = array('bar' => 'baz');

		$subject = $this->getAccessibleMock(
			'Tx_TerFe2_Utility_Archive',
			array('dummy')
		);
		$emConf = $subject->_call('extractEmConf', $code);

		$this->assertNotNull($emConf);
		$this->assertSame($expected, $emConf);
	}

	/**
	 * @test
	 */
	public function extractEmConfReturnsOnSecondAssignment() {
		$code = '<?php $EM_CONF[$_EXTKEY] = array(\'bar\' => \'baz\'); $foo = TRUE ?>';

		$subject = $this->getAccessibleMock(
			'Tx_TerFe2_Utility_Archive',
			array('dummy')
		);
		$emConf = $subject->_call('extractEmConf', $code);

		$this->assertNull($emConf);
	}

	/**
	 * @test
	 */
	public function extractEmConfReturnsOnAssignmentOtherThanEmConf() {
		$code = '<?php $EM_CONFOTHER[$_EXTKEY] = array(\'bar\' => \'baz\'); ?>';

		$subject = $this->getAccessibleMock(
			'Tx_TerFe2_Utility_Archive',
			array('dummy')
		);
		$emConf = $subject->_call('extractEmConf', $code);

		$this->assertNull($emConf);
	}
}

?>