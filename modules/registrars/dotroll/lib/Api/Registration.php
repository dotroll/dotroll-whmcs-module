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
			if ($action == 'transfer' && isset($this->params['transfersecret'])) {
				$this->params['transfersecret'] = \trim($this->params['transfersecret']);
			}
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
				'tax_id' => !empty($this->params['companyname']) || empty($this->params['additionalfields']['Date of Birth']) ? $this->params['tax_id'] : $this->params['additionalfields']['Date of Birth'],
			]);
			$request['owner'] = (int) $owner['contact']['contactid'];
			if (\substr($this->domain, -3) != '.hu') {
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
				$request['admin'] = (int) $admin['contact']['contactid'];
			}
			if (\substr($this->domain, -3) != '.hu' && \substr($this->domain, -3) != '.eu') {
				$request['tech'] = $request['admin'];
			}
			if (\substr($this->domain, -3) == '.hu') {
				$request['type'] = !empty($this->params['additionalfields']['Type of the .hu domain']) && $this->params['additionalfields']['Type of the .hu domain'] == '2f' ? '2f' : '1f';
				if (!empty($this->params['additionalfields']['Agree to HU Terms'])) {
					$request['declarations'] = 'I declare that I am the Domain Applicant for this domain request or am authorized to act on behalf of the Applicant. I warrant that the data provided in my domain application are accurate, and I acknowledge that if the provided data are not truthful or if I fail to report any changes to the data, it may result in the revocation of the domain. I understand that special attention must be paid to maintaining control over the factor data. By clicking the "Confirm" button I confirm the domain registration. I acknowledge and accept the provisions of the [Domain Registration Policy](https://www.domain.hu/domain-registration-policy/) and consider them binding for the entire duration of the domain application and registration. By maintaining the registration, I submit to the decisions of the [Alternative Dispute Resolution Forum](https://www.domain.hu/handling-of-complaints/). I have read and understood the contents of the [Data Privacy Notice](https://www.domain.hu/data-privacy/) and I grant my consent to the processing of my personal data in the records, as well as I accept that the granting of this consent is lawful according to the national law applicable to me. I have reviewed all the data, and the information is correct.';
				}
			}
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
