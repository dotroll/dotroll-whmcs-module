<?php

/**
 * _bootstrap, This is the bootstrap for PHPUnit testing
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-02
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */
if (!\defined('WHMCS')) {
	\define('WHMCS', true);
}

// Include the WHMCS module.
require_once __DIR__ . '/../modules/registrars/dotroll/dotroll.php';

/**
 * Mock logModuleCall function for testing purposes.
 *
 * Inside of WHMCS, this function provides logging of module calls for debugging
 * purposes. The module log is accessed via Utilities > Logs.
 *
 * @param string $module
 * @param string $action
 * @param string|array $request
 * @param string|array $response
 * @param string|array $data
 * @param array $variablesToMask
 *
 * @return void|false
 */
function logModuleCall(
	$module,
	$action,
	$request,
	$response,
	$data = '',
	$variablesToMask = array()
) {
	// do nothing during tests
}

function add_hook(string $hookPointName, int $priority, $function) {
	// do nothing during tests
}
