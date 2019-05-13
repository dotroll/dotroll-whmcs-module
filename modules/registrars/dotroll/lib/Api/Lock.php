<?php

/**
 * Lock, Manage transfer lock status.
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

class Lock extends Module {

	/**
	 * Get registrar lock status.
	 *
	 * Also known as Domain Lock or Transfer Lock status.
	 *
	 * @return string Lock status
	 */
	public function get(): string {
		try {
			$response = DomainData::toArray($this->params);
			if (!empty($response['domain']['lockstatus']) && \in_array($response['domain']['lockstatus'], ['locked', 'unlocked'])) {
				return $response['domain']['lockstatus'];
			}
			return '';
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

	/**
	 * Set registrar lock status.
	 *
	 * @return array
	 */
	public function save(): array {
		try {
			$this->connect("/domains/lock/{$this->domain}", ['lock' => ($this->params['lockenabled'] == 'locked' ? 'yes' : 'no')]);
			return [];
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

}
