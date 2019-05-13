<?php

/**
 * Renew, Renew a domain.
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (https://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2016-07-20
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll\Api;

use WHMCS\Module\Registrar\Dotroll\{
	Module,
	DotRollException
};

class Renew extends Module {

	/**
	 * Renew a domain.
	 *
	 * Attempt to renew/extend a domain for a given number of years.
	 *
	 * This is triggered when the following events occur:
	 * * Payment received for a domain renewal order
	 * * When a pending domain renewal order is accepted
	 * * Upon manual request by an admin user
	 *
	 * @return array
	 */
	public function doRenew(): array {
		try {
			$this->connect('/domains/renew/' . $this->domain, ['years' => (int) $this->params['regperiod']]);
			return [];
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

}
