<?php

/**
 * RequestDelete, Delete Domain.
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-09
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll\Api;

use WHMCS\Module\Registrar\Dotroll\{
	Module,
	DotRollException
};

class RequestDelete extends Module {

	/**
	 * Delete Domain.
	 *
	 * @return array
	 */
	public function save(): array {
		try {
			$this->connect('/domains/autorenew/' . $this->domain, ['autorenewal' => 'no']);
			return [];
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

}
