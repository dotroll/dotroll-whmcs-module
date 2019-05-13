<?php

/**
 * DomainData
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-08
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll\Api;

use WHMCS\Module\Registrar\Dotroll\Module;
use WHMCS\Module\Registrar\Dotroll\DotRollException;

class DomainData extends Module {

	/**
	 * Domain data
	 *
	 * @var array
	 */
	private static $data = [];

	/**
	 * Get domain data from static
	 * 
	 * @param array $params Common module parameters
	 * @return array
	 * @throws DotRollException
	 */
	public static function toArray(array $params): array {
		try {
			if (isset(static::$data[$params['domainname']])) {
				return static::$data[$params['domainname']];
			}
			return static::$data[$params['domainname']] = (new DomainData($params))->get();
		} catch (DotRollException | \Exception $e) {
			throw new DotRollException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Get domain data from API
	 *
	 * @return array
	 * @throws DotRollException
	 */
	public function get(): array {
		try {
			return $this->connect('/domains/get/' . $this->domain);
		} catch (DotRollException | \Exception $e) {
			throw new DotRollException($e->getMessage(), $e->getCode());
		}
		
	}

}
