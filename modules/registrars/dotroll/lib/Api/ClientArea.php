<?php

/**
 * ClientArea, Client Area Output.
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
	Client,
};

class ClientArea extends Module {

	/**
	 * Client Area Output.
	 *
	 * This function renders output to the domain details interface within
	 * the client area. The return should be the HTML to be output.
	 *
	 * @return string|null HTML Output
	 */
	public function output(): ?string {
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
			return '<br /><div class="alert alert-warning">'  . Client::trans('huElectronicRegistrationMissing') . ' <a href="clientarea.php?action=domaindetails&domainid=' . $this->params['domainid'] . '&modop=custom&a=declaration" class="btn btn-warning">'  . Client::trans('huElectronicRegistration') . '</a></div>';
		}
		return null;
	}

}
