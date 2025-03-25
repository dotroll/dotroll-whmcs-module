<?php

/**
 * Contacts, Get and update the current doamin Contact Informations.
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
	DotRollException,
	Client,
};

class Contacts extends Module {

	/**
	 * Get the current Contact Information.
	 *
	 * Should return a multi-level array of the contacts and name/address
	 * fields that be modified.
	 *
	 * @return array
	 */
	public function get(): array {
		try {
			$data = DomainData::toArray($this->params);
			if (isset($data['domain']['contacts']) && \is_array($data['domain']['contacts'])) {
				$contacts = [];
				foreach ($data['domain']['contacts'] as $type => $contact) {
					if (!\in_array($type, ['zone'])) {
						foreach ($contact as $field => $value) {
							if (!\in_array($field, ['id', 'type', 'vatnumber'])) {
								$contacts[$type][$field] = $value;
							}
						}
					}
				}

				if (!empty($_POST['contactdetails'])) {
					$errors = [];
					foreach ($_POST['contactdetails'] as $type => $contact) {
						$contacts[$type] = \array_merge($contacts[$type], $contact);
						try {
							$this->connect("/domains/contact/{$this->domain}/$type", $contact);
						} catch (DotRollException $e) {
							if ($e->getCode() != 10233) {
								$errors[] = Client::trans($type) . ': ' . $e->toString();
							}
						}
					}
				}

				return [
					'templatefile' => 'contacts',
					'breadcrumb' => [
						'clientarea.php?action=domaindetails&domainid=' . $this->params['domainid'] . '&modop=custom&a=Contacts' => \Lang::trans('domaincontactinfo'),
					],
					'vars' => [
						'contactdetails' => $contacts,
						'translation' => Client::get(),
						'clientcountries' => Client::getCountries(),
						'error' => empty($errors) ? '' : \implode("<br />", $errors),
						'successful' => empty($errors) && !empty($_POST['contactdetails']) ? true : false,
					],
				];
			}
			return [];
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

}
