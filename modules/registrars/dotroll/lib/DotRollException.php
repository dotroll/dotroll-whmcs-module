<?php

/**
 * DotRollException
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (https://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2016-07-20
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll;

/**
 * DotRoll EHMCS module exception
 */
class DotRollException extends \Exception {

	/**
	 * Send an exception back to a WHMCS as WHMCS specific array
	 *
	 * @return array
	 */
	public function toArray(): array {
		return ['error' => $this->toString()];
	}

	/**
	 * Send an exception back to as string
	 *
	 * @return array
	 */
	public function toString(): string {
		if (!empty($this->getCode()) && Client::hasTrans('error.' . $this->getCode()) === true) {
			return $this->getCode() . ': ' . Client::trans('error.' . $this->getCode());
		}
		if (!empty($this->getCode()) && Admin::hasTrans('error.' . $this->getCode()) === true) {
			return $this->getCode() . ': ' . Admin::trans('error.' . $this->getCode());
		}
		if (!empty($this->getCode()) && !empty($this->getMessage())) {
			return $this->getCode() . ': ' . $this->getMessage();
		}
		if (!empty($this->getCode())) {
			return $this->getCode() . ': Anonymous error';
		}
		if (!empty($this->getMessage())) {
			return $this->getMessage();
		}
		return 'Anonymous error';
	}

}
