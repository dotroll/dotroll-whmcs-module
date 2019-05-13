<?php

/**
 * dotroll, DotRoll registrar module caller from DotRoll
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (https://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2016-07-20
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */
if (!\defined('WHMCS')) {
	die('This file cannot be accessed directly');
}

use WHMCS\Module\Registrar\Dotroll\Api\{
	ClientArea,
	Config,
	Contacts,
	Declaration,
	Dnssec,
	Dns,
	DomainInfo,
	Lock,
	Nameservers,
	Registration,
	Renew,
	RequestDelete,
};
use WHMCS\Domain\Registrar\Domain;

/**
 * Client Area Output.
 *
 * This function renders output to the domain details interface within
 * the client area. The return should be the HTML to be output.
 *
 * @param array $params common module parameters
 * @return string|null HTML Output
 */
function dotroll_ClientArea(array $params): ?string {
	return (new ClientArea($params))->output();
}

/**
 * Client Area Custom Button Array.
 *
 * Allows you to define additional actions your module supports.
 *
 * @return array
 */
function dotroll_ClientAreaCustomButtonArray(array $params): array {
	return (new Config($params))->getCustomButtons();
}

/**
 * Manage Domain Contact Information.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_Contacts(array $params): array {
	return (new Contacts($params))->get();
}

/**
 * Manage .HU Domain Contact Declaration.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_Declaration(array $params): array {
	return (new Declaration($params))->get();
}

/**
 * Manage Domain Dnssec
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_Dnssec(array $params): array {
	return (new Dnssec($params))->output();
}

/**
 * Get config parameters
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_GetConfigArray(array $params): array {
	return (new Config($params))->toArray();
}

/**
 * Get DNS Records for DNS Host Record Management.
 *
 * @param array $params common module parameters
 * @return array DNS Host Records
 */
function dotroll_GetDNS(array $params): array {
	return (new Dns($params))->get();
}

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
 * @param array $params common module parameters
 * @return Domain
 */
function dotroll_GetDomainInformation(array $params): Domain {
	return (new DomainInfo($params))->getDomainInfo();
}

/**
 * Request EPP Code.
 *
 * Supports both displaying the EPP Code directly to a user or indicating
 * that the EPP Code will be emailed to the registrant.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_GetEPPCode(array $params): array {
	return (new DomainInfo($params))->getEpp();
}

/**
 * Fetch current nameservers.
 *
 * This function should return an array of nameservers for a given domain.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_GetNameservers(array $params): array {
	return (new Nameservers($params))->get();
}

/**
 * Get registrar lock status.
 *
 * Also known as Domain Lock or Transfer Lock status.
 *
 * @param array $params common module parameters
 * @return string Lock status
 */
function dotroll_GetRegistrarLock(array $params): string {
	return (new Lock($params))->get();
}

/**
 * Register a domain.
 *
 * Attempt to register a domain with DotRoll.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain registration order
 * * When a pending domain registration order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_RegisterDomain(array $params): array {
	return (new Registration($params))->doRegistration();
}

/**
 * Renew a domain.
 *
 * Attempt to renew/extend a domain for a given number of years.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain renewal order
 * * When a pending domain renewal order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_RenewDomain(array $params): array {
	return (new Renew($params))->doRenew();
}

/**
 * Delete Domain.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_RequestDelete(array $params): array {
	return (new RequestDelete($params))->save();
}

/**
 * Update DNS Host Records.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_SaveDNS(array $params): array {
	return (new Dns($params))->save();
}

/**
 * Save nameserver changes.
 *
 * This function should submit a change of nameservers request via DotRoll API.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_SaveNameservers(array $params): array {
	return (new Nameservers($params))->save();
}

/**
 * Set registrar lock status.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_SaveRegistrarLock(array $params): array {
	return (new Lock($params))->save();
}

/**
 * Sync Domain Status & Expiration Date.
 *
 * Domain syncing is intended to ensure domain status and expiry date
 * changes made directly at the domain registrar are synced to WHMCS.
 * It is called periodically for a domain.
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_Sync(array $params): array {
	return (new DomainInfo($params))->sync();
}

/**
 * Initiate domain transfer.
 *
 * Attempt to create a domain transfer request for a given domain.
 *
 * This is triggered when the following events occur:
 * * Payment received for a domain transfer order
 * * When a pending domain transfer order is accepted
 * * Upon manual request by an admin user
 *
 * @param array $params common module parameters
 * @return array
 */
function dotroll_TransferDomain(array $params): array {
	return (new Registration($params))->doRegistration('transfer');
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
function dotroll_TransferSync(array $params): array {
	return (new DomainInfo($params))->transferSync();
}
