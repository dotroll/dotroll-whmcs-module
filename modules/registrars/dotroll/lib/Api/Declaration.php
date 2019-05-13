<?php

/**
 * Declaration, Manage .HU Domain Contact Declaration.
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-08
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll\Api;

use WHMCS\Module\Registrar\Dotroll\{
	Module,
	DotRollException,
	Client,
};

class Declaration extends Module {

	/**
	 * Manage .HU Domain Contact Declaration.
	 *
	 * @return array
	 */
	public function get(): array {
		try {
			$data = DomainData::toArray($this->params);
			if (
				\substr($this->domain, -3) == '.hu' &&
				\strpos($this->domain, '.extra.hu') === false &&
				$data['domain']['regtype'] == 'Register' &&
				\in_array($data['domain']['status'], ['Active', 'Pending']) &&
				\in_array($data['domain']['infodomain']['stateId'], [2, 3, 9, 23, 34]) &&
				isset($data['domain']['infodomain']['ereg']) &&
				$data['domain']['infodomain']['ereg'] == 'pending'
			) {
				$declaration = [];
				$declareError = null;
				$declareSuccess = null;
				if (
					isset($_POST['accept']) &&
					$_POST['accept'] == 'accept' &&
					isset($_POST['captcha'])
				) {
					try {
						$add = $this->connect("/domains/declaration/{$this->domain}/add", ['captcha' => $_POST['captcha']]);
						if (isset($add['domain']['error'])) {
							if (!empty($add['domain']['errorno'])) {
								throw new DotRollException($add['domain']['error'], $add['domain']['errorno']);
							} else {
								throw new DotRollException($add['domain']['error']);
							}
						} else {
							$declareSuccess = true;
						}
					} catch (DotRollException $e) {
						$declareError = $e->toString();
					}
				} else {
					$get = $this->connect("/domains/declaration/{$this->domain}/get");
					$declaration = $get['domain'];
				}

				return [
					'pagetitle' => 'Addon Module',
					'templatefile' => 'declaration',
					'breadcrumb' => [
						'clientarea.php?action=domaindetails&domainid=' . $this->params['domainid'] . '&modop=custom&a=Declaration' => Client::trans('huElectronicRegistration'),
					],
					'vars' => [
						'declaration' => $declaration,
						'translation' => Client::get(),
						'declareError' => $declareError,
						'declareSuccess' => $declareSuccess,
					],
				];
			}
		} catch (DotRollException $e) {
			return $e->toArray();
		}
		return [];
	}

}
