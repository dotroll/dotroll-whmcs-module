<?php

/**
 * DomainInfo, Get domain information
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-04-29
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll\Api;

use WHMCS\Module\Registrar\Dotroll\{
	Module,
	DotRollException
};
use WHMCS\Domain\Registrar\Domain;
use WHMCS\Carbon;

class DomainInfo extends Module {

	/**
	 * Get domain information
	 *
	 * Attempt to get a domain information.
	 *
	 * This function allows you to build and return an
	 * object representing a given domain. The attributes
	 * of the object can include data including nameservers,
	 * expiry date, registrar and transfer lock status,
	 * domain contact verification status and more.
	 *
	 * @return Domain
	 */
	public function getDomainInfo(): Domain {
		try {
			$domain = new Domain();
			$response = DomainData::toArray($this->params);
			if (!empty($response['domain']['domainname'])) {
				$domain->setDomain($response['domain']['domainname']);
			}
			if (isset($response['domain']['ns']) && \is_array($response['domain']['ns'])) {
				$ns = [];
				foreach ($response['domain']['ns'] as $key => $value) {
					$ns['ns' . ($key + 1)] = $value;
				}
				$domain->setNameservers($ns);
			}
			if (!empty($response['domain']['status']) && \in_array($response['domain']['status'], [
					'Active',
					'Grace',
					'Redemption',
					'Expired',
					'Cancelled',
				])) {
				switch ($response['domain']['status']) {
					case 'Active':
						$domain->setRegistrationStatus(Domain::STATUS_ACTIVE);
						break;
					case 'Grace':
					case 'Redemption':
					case 'Expired':
						$domain->setRegistrationStatus(Domain::STATUS_EXPIRED);
						break;
					case 'Cancelled':
						$domain->setRegistrationStatus(Domain::STATUS_DELETED);
						break;
				}
			}
			if (isset($response['domain']['infodomain']['state']['clientHold'])) {
				$domain->setRegistrationStatus(Domain::STATUS_SUSPENDED);
			}
			if (!empty($response['domain']['lockstatus']) && \in_array($response['domain']['lockstatus'], ['unlocked', 'locked'])) {
				$domain->setTransferLock($response['domain']['lockstatus'] == 'locked' ? true : false);
			} else {
				$domain->setTransferLock(true);
			}
			if (!empty($response['domain']['expirydate'])) {
				$domain->setExpiryDate(Carbon::createFromFormat('Y-m-d', $response['domain']['expirydate'], self::TZ));
			}
			if (!empty($response['domain']['infodomain']['transferlockUntil'])) {
				$domain->setTransferLockExpiryDate(Carbon::createFromFormat('Y-m-d', $response['domain']['infodomain']['transferlockUntil'], self::TZ));
				$domain->setIrtpTransferLockExpiryDate(Carbon::createFromFormat('Y-m-d', $response['domain']['infodomain']['transferlockUntil'], self::TZ));
				$domain->setTransferLock($response['domain']['lockstatus'] == 'locked' ? true : false);
			}
			if (!empty($response['domain']['infodomain']['isIrtpEnabled'])) {
				$domain->setIrtpTransferLock(true);
			}
			if (!empty($response['domain']['contacts']['pending_registrant'])) {
				$domain->setDomainContactChangePending(true);
			}
			if (!empty($response['domain']['infodomain']['domainContactChangeExpiryDate'])) {
				$domain->setDomainContactChangeExpiryDate(Carbon::createFromFormat('Y-m-d H:i:s', $response['domain']['infodomain']['domainContactChangeExpiryDate'], self::TZ));
			}
			if (!empty($response['domain']['contacts']['registrant']['email'])) {
				$domain->setRegistrantEmailAddress($response['domain']['contacts']['registrant']['email']);
			} elseif (!empty($response['domain']['contacts']['owner']['email'])) {
				$domain->setRegistrantEmailAddress($response['domain']['contacts']['owner']['email']);
			}
			return $domain;
		} catch (DotRollException $e) {

		}
		return new Domain();
	}

	/**
	 * Request EPP Code.
	 *
	 * Supports both displaying the EPP Code directly to a user or indicating
	 * that the EPP Code will be emailed to the registrant.
	 *
	 * @return array
	 */
	public function getEpp(): array {
		$response = DomainData::toArray($this->params);
		if (!empty($response['domain']['eppcode'])) {
			return ['eppcode' => \htmlspecialchars_decode($response['domain']['eppcode'])];
		}
		return [];
	}

	/**
	 * Sync Domain Status & Expiration Date.
	 *
	 * Domain syncing is intended to ensure domain status and expiry date
	 * changes made directly at the domain registrar are synced to WHMCS.
	 * It is called periodically for a domain.
	 *
	 * @return array
	 */
	public function sync(): array {
		if (!empty($this->params['domain'])) {
			try {
				$response = $this->connect('/domains/get/' . $this->params['domain']);
				if (empty($response['domain']['status'])) {
					\logModuleCall(self::NAME, __FUNCTION__, $this->params, \array_merge($response, ['sync' => ['cancelled' => true]]));
					return ['cancelled' => true];
				}
				if (empty($response['domain']['expirydate']) || $response['domain']['expirydate'] == '0000-00-00') {
					\logModuleCall(self::NAME, __FUNCTION__, $this->params, \array_merge($response, ['sync' => []]));
					return [];
				}
				$retval = [
					'expirydate' => $response['domain']['expirydate']
				];
				switch ($response['domain']['status']) {
					case 'Transferred Away':
						$retval['transferredAway'] = true;
						break;
					case 'Cancelled':
						$retval['cancelled'] = true;
						break;
					case 'Expired':
					case 'Redemption':
					case 'Grace':
						$retval['expired'] = true;
						break;
					case 'Active':
						$retval['active'] = true;
						break;
				}
				\logModuleCall(self::NAME, __FUNCTION__, $this->params, \array_merge($response, ['sync' => $retval]));
				return $retval;
			} catch (DotRollException $e) {
				if ($e->getCode() == 404) {
					\logModuleCall(self::NAME, __FUNCTION__, $this->params, \array_merge($response, ['sync' => ['cancelled' => true]]));
					return ['cancelled' => true];
				}
				throw new DotRollException($e->getMessage(), $e->getCode());
			}
		}
		return [];
	}

	/**
	 * Incoming Domain Transfer Sync.
	 *
	 * Check status of incoming domain transfers and notify end-user upon
	 * completion. This function is called daily for incoming domains.
	 *
	 * @param array $params common module parameters
	 * @return array
	 */
	public function transferSync() {
		if (!empty($this->params['domain'])) {
			try {
				$response = $this->connect('/domains/get/' . $this->params['domain']);
				if (
					!empty($response['domain']['status']) &&
					$response['domain']['status'] == 'Active' &&
					!empty($response['domain']['expirydate']) &&
					$response['domain']['expirydate'] != '0000-00-00'
				) {
					\logModuleCall(self::NAME, __FUNCTION__, $this->params, \array_merge($response, ['transferSync' => [
							'completed' => true,
							'expirydate' => $response['domain']['expirydate'],
					]]));
					return [
						'completed' => true,
						'expirydate' => $response['domain']['expirydate'],
					];
				}
			} catch (DotRollException $e) {
				throw new DotRollException($e->getMessage(), $e->getCode());
			}
		}
		return [];
	}

}
