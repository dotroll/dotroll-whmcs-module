<?php

namespace WHMCS\Module\Registrar\Dotroll;

/**
 * DotrollRegistrarModuleTest, WHMCS DotRoll Registrar Module Test
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-02
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */
class DotrollRegistrarModuleTest extends \PHPUnit\Framework\TestCase {

	public static function providerCoreFunctionNames(): array {
		return [
			['getConfigArray'],
			['RegisterDomain'],
			['TransferDomain'],
			['RenewDomain'],
			['GetDomainInformation'],
			['GetNameservers'],
			['SaveNameservers'],
			['GetContactDetails'],
			['SaveContactDetails'],
		];
	}

	/**
	 * Test Core Module Functions Exist
	 *
	 * This test confirms that the functions we recommend for all registrar
	 * modules are defined for the sample module
	 *
	 * @param $moduleName
	 *
	 * @dataProvider providerCoreFunctionNames
	 */
	public function testCoreModuleFunctionsExist(string $moduleName) {
		$this->assertTrue(\function_exists('dotroll_' . $moduleName));
	}

}
