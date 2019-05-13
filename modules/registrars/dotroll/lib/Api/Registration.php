<?php

/**
 * Registration, Register a domain or initiate domain transfer.
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

class Registration extends Module {

	/**
	 * Register a domain or initiate domain transfer.
	 *
	 * Attempt to register  a domain or initiate a domain transfer with DotRoll.
	 *
	 * This is triggered when the following events occur:
	 * * Payment received for a domain registration/transfer order
	 * * When a pending domain registration/transfer order is accepted
	 * * Upon manual request by an admin user
	 *
	 * @param string|null $action Registration or transfer
	 * @return array
	 */
	public function doRegistration(?string $action = 'register'): array {
		try {
			$request = [
				'domain' => $this->domain,
				'years' => $this->params['regperiod']
			];
			if ($action == 'transfer' && !empty($this->params['transfersecret'])) {
				$request['eppcode'] = $this->params['transfersecret'];
			}
			for ($i = 1; $i < 6; $i++) {
				if (!empty($this->params["ns$i"])) {
					$request["ns$i"] = $this->params["ns$i"];
				}
			}
			if (!empty($this->params['additionalfields'])) {
				$request['domainfields'] = \json_encode($this->params['additionalfields']);
			}
			$owner = $this->connect('/contacts/add', [
				'firstname' => $this->params['firstname'],
				'lastname' => $this->params['lastname'],
				'companyname' => $this->params['companyname'],
				'email' => $this->params['email'],
				'address1' => $this->params['address1'],
				'address2' => $this->params['address2'],
				'city' => $this->params['city'],
				'state' => $this->params['state'],
				'postcode' => $this->params['postcode'],
				'country' => $this->params['country'],
				'phonenumber' => $this->params['telephoneNumber'],
				'tax_id' => $this->params['tax_id'],
			]);
			$request['owner'] = (int) $owner['contact']['contactid'];
			$admin = $this->connect('/contacts/add', [
				'firstname' => $this->params['adminfirstname'],
				'lastname' => $this->params['adminlastname'],
				'companyname' => $this->params['admincompanyname'],
				'email' => $this->params['adminemail'],
				'address1' => $this->params['adminaddress1'],
				'address2' => $this->params['adminaddress2'],
				'city' => $this->params['admincity'],
				'state' => $this->params['adminstate'],
				'postcode' => $this->params['adminpostcode'],
				'country' => $this->params['admincountry'],
				'phonenumber' => $this->params['adminfullphonenumber'],
			]);
			$request['tech'] = $request['admin'] = (int) $admin['contact']['contactid'];
			$this->connect("/domains/$action", $request);
			return [];
		} catch (DotRollException $e) {
			return $e->toArray();
		} finally {
			try {
				if (!empty($request['owner'])) {
					$this->connect("/contacts/delete/{$request['owner']}");
				}
			} catch (DotRollException $e) {
				
			}
			try {
				if (!empty($request['admin'])) {
					$this->connect("/contacts/delete/{$request['admin']}");
				}
			} catch (DotRollException $e) {
			
			}
		}
	}

}
