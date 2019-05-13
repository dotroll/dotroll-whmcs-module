<?php

/**
 * Dnssec, Manage Domain Dnssec
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-13
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

class Dnssec extends Module {

	/**
	 * Manage Domain Dnssec
	 *
	 * @return array
	 */
	public function output(): array {
		$error = '';
		try {
			$domainData = DomainData::toArray($this->params);
			if (empty($domainData['domain']['infodomain']['dnssecSupport'])) {
				return [];
			}
			$dnssec = $this->connect('/domains/dnssec/' . $this->domain . '/get')['dnssec'];

			if (
				!empty($_POST['autoconfig']) &&
				!empty($domainData['domain']['infodomain']['dnssecWizzard']) &&
				empty($dnssec)
			) {
				$dnssec = $this->connect('/domains/dnssec/' . $this->domain . '/update', ['operation' => 'enable'])['dnssec'];
			} elseif (
				!empty($_POST['autoconfig']) &&
				!empty($domainData['domain']['infodomain']['dnssecWizzard'])
			) {
				$dnssec = $this->connect('/domains/dnssec/' . $this->domain . '/update', ['operation' => 'disable'])['dnssec'];
			} elseif (!empty($_POST['delrecords']) || !empty($_POST['newrecords'])) {
				$request = ['operation' => 'update'];
				if ($domainData['domain']['infodomain']['dnssecType'] == 'publickey') {
					if (!empty($_POST['newrecords'])) {
						foreach ($_POST['newrecords'] as $idx => $newrecord) {
							if (
								!empty($newrecord['flags']) &&
								!empty($newrecord['protocol']) &&
								!empty($newrecord['algorithm']) &&
								!empty($newrecord['publicKey'])
							) {
								$request["add_{$idx}_flags"] = $newrecord['flags'];
								$request["add_{$idx}_protocol"] = $newrecord['protocol'];
								$request["add_{$idx}_algorithm"] = $newrecord['algorithm'];
								$request["add_{$idx}_publicKey"] = $newrecord['publicKey'];
							}
						}
					}
					if (!empty($_POST['delrecords'])) {
						foreach ($_POST['delrecords'] as $idx => $delrecord) {
							if (
								!empty($dnssec[$delrecord]['flags']) &&
								!empty($dnssec[$delrecord]['protocol']) &&
								!empty($dnssec[$delrecord]['algorithm']) &&
								!empty($dnssec[$delrecord]['publicKey'])
							) {
								$request["rem_{$idx}_flags"] = $dnssec[$delrecord]['flags'];
								$request["rem_{$idx}_protocol"] = $dnssec[$delrecord]['protocol'];
								$request["rem_{$idx}_algorithm"] = $dnssec[$delrecord]['algorithm'];
								$request["rem_{$idx}_publicKey"] = $dnssec[$delrecord]['publicKey'];
							}
						}
					}
				} else {
					if (!empty($_POST['newrecords'])) {
						foreach ($_POST['newrecords'] as $idx => $newrecord) {
							if (
								!empty($newrecord['keytag']) &&
								!empty($newrecord['algorithm']) &&
								!empty($newrecord['digesttype']) &&
								!empty($newrecord['digest'])
							) {
								$request["add_{$idx}_keytag"] = $newrecord['keytag'];
								$request["add_{$idx}_algorithm"] = $newrecord['algorithm'];
								$request["add_{$idx}_digesttype"] = $newrecord['digesttype'];
								$request["add_{$idx}_digest"] = $newrecord['digest'];
							}
						}
					}
					if (!empty($_POST['delrecords'])) {
						foreach ($_POST['delrecords'] as $idx => $delrecord) {
							if (
								!empty($dnssec[$delrecord]['keytag']) &&
								!empty($dnssec[$delrecord]['algorithm']) &&
								!empty($dnssec[$delrecord]['digesttype']) &&
								!empty($dnssec[$delrecord]['digest'])
							) {
								$request["rem_{$idx}_keytag"] = $dnssec[$delrecord]['keytag'];
								$request["rem_{$idx}_algorithm"] = $dnssec[$delrecord]['algorithm'];
								$request["rem_{$idx}_digesttype"] = $dnssec[$delrecord]['digesttype'];
								$request["rem_{$idx}_digest"] = $dnssec[$delrecord]['digest'];
							}
						}
					}
				}
				$dnssec = $this->connect('/domains/dnssec/' . $this->domain . '/update', $request)['dnssec'];
			}
		} catch (DotRollException $e) {
			$error = $e->toString();
		}
		return [
			'templatefile' => 'dnssec',
			'breadcrumb' => [
				'clientarea.php?action=domaindetails&domainid=' . $this->params['domainid'] . '&modop=custom&a=Dnssec' => Client::trans('dnssec'),
			],
			'vars' => [
				'dnssec' => $dnssec,
				'wizzard' => $domainData['domain']['infodomain']['dnssecWizzard'],
				'type' => !empty($domainData['domain']['infodomain']['dnssecType']) ? $domainData['domain']['infodomain']['dnssecType'] : 'digest',
				'translation' => Client::get(),
				'algorithms' => $this->getAlgorithms(),
				'digestTypes' => $this->getDigestTypes(),
				'flags' => [
					257 => '257 - KSK',
					256 => '256 - ZSK'
				],
				'protocols' => [
					3 => '3'
				],
				'error' => $error,
				'successful' => empty($error) && !empty($_POST) ? true : false,
			],
		];
	}

	/**
	 * Get available algorithms
	 *
	 * @return array
	 */
	private function getAlgorithms(): array {
		if (\substr($this->domain, -3) == '.eu') {
			return [
				3 => '3 - DSA_SHA1_NSEC',
				5 => '5 - RSA_SHA1_NSEC',
				6 => '6 - DSA_SHA1_NSEC3',
				7 => '7 - RSA_SHA1_NSEC3',
				8 => '8 - RSA_SHA256',
				10 => '10 - RSA_SHA512',
				13 => '13 - ECDSA_P256_SHA256',
				14 => '14 - ECDSA_P384_SHA384'
			];
		}
		return [
			254 => '254 - PRIVATE ALGORITHMS - OID',
			1 => '1 - RSA/MD5',
			2 => '2 - DIFFIE-HELLMAN',
			253 => '253 - PRIVATE ALGORITHMS - DOMAIN NAME',
			3 => '3 - DSA/SHA1',
			5 => '5 - RSA/SHA-1',
			6 => '6 - DSA-NSEC3-SHA1',
			7 => '7 - RSASHA1-NSEC3-SHA1',
			8 => '8 - RSA/SHA-256',
			10 => '10 - RSA/SHA-512',
			12 => '12 - GOST R 34.10-2001',
			13 => '13 - ECDSA CURVE P-256 WITH SHA-256',
			14 => '14 - ECDSA CURVE P-384 WITH SHA-384'
		];
	}

	/**
	 * Get available digest types
	 * 
	 * @return array
	 */
	private function getDigestTypes(): array {
		if (\substr($this->domain, -4) == '.org') {
			return [
				1 => '1 - SHA-1',
				2 => '2 - SHA-256'
			];
		}
		return [
			1 => '1 - SHA-1',
			2 => '2 - SHA-256',
			3 => '3 - GOST R 34.11-94',
			4 => '4 - SHA-384'
		];
	}

}
