<?php

/**
 * Config, Get config parameters
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
	Admin,
	Client,
};

class Config extends Module {

	/**
	 * Get config parameters
	 *
	 * @return array
	 */
	public function toArray(): array {
		return [
			'FriendlyName' => ['Type' => 'System', 'Value' => Admin::trans('friendlyName')],
			'Description' => ['Type' => 'System', 'Value' => Admin::trans('moduleDescription')],
			'Username' => ['FriendlyName' => Admin::trans('usernameText'), 'Type' => 'text', 'Size' => '60', 'Description' => Admin::trans('usernameDescription')],
			'Password' => ['FriendlyName' => Admin::trans('passwordText'), 'Type' => 'password', 'Size' => '60', 'Description' => Admin::trans('passwordDescription')],
			'BaseUrl' => ['FriendlyName' => Admin::trans('baseUrlText'), 'Type' => 'text', 'Size' => '60', 'Description' => Admin::trans('baseUrlDescription')],
		];
	}

	/**
	 * Client Area Custom Button Array.
	 *
	 * Allows you to define additional actions your module supports.
	 *
	 * @return array
	 */
	public function getCustomButtons(): array {
		$data = DomainData::toArray($this->params);
		$retval = [];
		if (
			\substr($this->domain, -3) == '.hu' &&
			\strpos($this->domain, '.extra.hu') === false &&
			$data['domain']['regtype'] == 'Register' &&
			\in_array($data['domain']['status'], ['Active', 'Pending']) &&
			\in_array($data['domain']['infodomain']['stateId'], [2, 3, 9, 23, 34]) &&
			isset($data['domain']['infodomain']['ereg']) &&
			$data['domain']['infodomain']['ereg'] == 'pending'
		) {
			$retval[Client::trans('huElectronicRegistration')] = 'Declaration';
		}
		if (!empty($data['domain']['contacts'])) {
			$retval[\Lang::trans('domaincontactinfo')] = 'Contacts';
		}
		if (!empty($data['domain']['infodomain']['dnssecSupport'])) {
			$retval[Client::trans('dnssec')] = 'Dnssec';
		}
		return $retval;
	}

}
