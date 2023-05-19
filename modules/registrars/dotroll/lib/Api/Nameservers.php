<?php

/**
 * Nameservers, Save nameserver changes and fetch current nameservers.
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-03
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll\Api;

use WHMCS\Module\Registrar\Dotroll\{
	Module,
	DotRollException
};

class Nameservers extends Module {

	/**
	 * Fetch current nameservers.
	 *
	 * This function should return an array of nameservers for a given domain.
	 * 
	 * @return array
	 */
	public function get(): array {
		try {
			$response = DomainData::toArray($this->params);
			if (isset($response['domain']['ns']) && \is_array($response['domain']['ns'])) {
				$retval = [];
				foreach ($response['domain']['ns'] as $key => $value) {
					$retval['ns' . ($key + 1)] = $value;
				}
				return $retval;
			}
			return [];
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

	/**
	 * Save nameserver changes.
	 *
	 * This function should submit a change of nameservers request via DotRoll API.
	 * 
	 * @return array
	 */
	public function save(): array {
		try {
			$nameservers = [];
			for ($i = 1; $i <= 5; $i++) {
				if (isset($this->params["ns$i"])) {
					$nameservers["ns$i"] = $this->params["ns$i"];
				}
			}
			$this->connect('/domains/updatens/' . $this->domain, $nameservers);
			return [];
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

}
