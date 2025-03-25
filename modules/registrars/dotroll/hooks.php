<?php

/**
 * hooks
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2025-03-25
 * @package dotroll_dotroll-whmcs-module
 */
use WHMCS\Database\Capsule;

\add_hook('ShoppingCartValidateCheckout', 1, function (array $vars) {
	if (empty($_SESSION['cart']['domains'])) {
		return;
	}
	if (empty($vars['contact'])) {
		$registrant = [
			'tax_id' => !empty($vars['tax_id']) ? $vars['tax_id'] : '',
			'companyname' => $vars['companyname'],
			'country' => $vars['country'],
			'birthdate' => '',
		];
	} elseif ($vars['contact'] == 'addingnew') {
		$registrant = [
			'tax_id' => !empty($vars['domaincontacttax_id']) ? $vars['domaincontacttax_id'] : '',
			'companyname' => $vars['domaincontactcompanyname'],
			'country' => $vars['domaincontactcountry'],
			'birthdate' => '',
		];
	} elseif ($contact = Capsule::table('tblcontacts')->where('id', $vars['contact'])->first()) {
		$registrant = [
			'tax_id' => $contact->tax_id,
			'companyname' => $contact->companyname,
			'country' => $contact->country,
			'birthdate' => '',
		];
	} else {
		$registrant = [
			'tax_id' => '',
			'companyname' => '',
			'country' => '',
			'birthdate' => '',
		];
	}

	$controlledTlds = [];
	$error = [];
	foreach ($_SESSION['cart']['domains'] as &$domain) {
		$domain['domain'] = \mb_strtolower($domain['domain'], 'UTF-8');
		if (\substr($domain['domain'], -3) == '.hu') {
			$controlledTlds[] = '.hu';
			if (!empty($domain['fields'][3])) {
				$registrant['birthdate'] = $domain['fields'][3];
			}
			if ($domain['type'] == 'transfer') {
				$check = \str_replace('-', '', \strtoupper($domain['eppcode']));
				if (empty($check) || !\preg_match('/^[A-Z0-9]{16}$/', $check)) {
					$error[] = 'Wrong Auth Code';
				}
			}
		} elseif (\substr($domain['domain'], -3) == '.eu') {
			$controlledTlds[] = '.eu';
		}
	}
	if (!empty($vars['country-calling-code-phonenumber']) && !empty($vars['phonenumber'])) {
		$vars['phonenumber'] = '+' . $vars['country-calling-code-phonenumber'] . '.' . \preg_replace('/[^0-9]+/', '', $vars['phonenumber']);
	}
	$eu = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'ES', 'EE', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK', 'AX', 'GF', 'GI', 'GP', 'MF', 'MQ', 'RE', 'YT', 'IS', 'LI', 'NO'];

	
	if (\in_array('.hu', $controlledTlds)) {
		// .hu rules
		if (empty($registrant['companyname'])) {
			if (empty($registrant['birthdate'])) {
				// If person need identity
				$error[] = 'You did not enter birth date';
			} else {
				$birthdate = \DateTime::createFromFormat('Y-m-d', $registrant['birthdate'], new \DateTimeZone('Europe/Budapest'));
				$min = (new \DateTime('now'))->modify('-100 year')->format('Y-m-d');
				$max = (new \DateTime('now'))->modify('-18 year')->format('Y-m-d');
				if (empty($birthdate) || $registrant['birthdate'] != $birthdate->format('Y-m-d') || $registrant['birthdate'] < $min || $registrant['birthdate'] > $max) {
					$error[] = 'The date of birth is incorrect. Format: 1950-02-28, year-month-day';
				}
			}
		} elseif ($registrant['country'] != 'HU' && empty($registrant['tax_id']) && \in_array($registrant['country'], $eu)) {
			// if company and not hungarian need vat-eu
			$error[] = 'You did not enter EU vat number';
		} elseif (empty($registrant['tax_id']) && $registrant['country'] == 'HU') {
			// If company and not eu or hungarian need vat
			$error[] = 'You did not enter your vat number';
		}
	}
	// .eu rules
	if (\in_array('.eu', $controlledTlds)) {
		if (!\in_array($registrant['country'], $eu)) {
			$error[] = 'The domain registrant may only be a person or organsation registered in the EU';
		}
	}

	if (!empty($error)) {
		return $error;
	}
	return;
});

\add_hook('ShoppingCartValidateDomainsConfig', 1, function (array $vars) {
	$retval = [];
	if (!empty($vars['epp'])) {
		foreach ($vars['epp'] as $epp) {
			$check = \str_replace('-', '', \strtoupper($epp));
			if (empty($check) || !\preg_match('/^[A-Z0-9]{16}$/', $check)) {
				$retval[] = 'Wrong Auth Code';
			}
		}
	}
	if (!empty($retval)) {
		return $retval;
	}
	return;
});
