<?php

/**
 * Module, DotRoll registrar module class from DotRoll
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (https://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2016-07-20
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll;

use WHMCS\Module\Registrar\Dotroll\DotRollException;

/**
 * DotRoll Registrar Module for DotRoll API
 */
abstract class Module {

	/**
	 * Module name
	 *
	 * @var string
	 * @const
	 */
	protected const NAME = 'DotRoll';

	/**
	 * Time Zone
	 *
	 * @var string
	 * @const
	 */
	protected const TZ = 'Europe/Budapest';

	/**
	 * Andmin interface language
	 *
	 * @var string
	 */
	protected $adminLanguage;

	/**
	 * WHMCS params
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * API EP URL
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * API Username
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * API Password
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Domain name (IDN ASCII)
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * DotRoll Registrar Module for DotRoll API
	 * 
	 * @param array $params Common module parameters
	 */
	public function __construct(array $params) {
		\mb_internal_encoding('UTF-8');
		$this->params = $params;
		$this->username = $this->params['Username'];
		$this->password = $this->params['Password'];
		$this->url = $this->params['BaseUrl'];
		if (!empty($this->params['domainname'])) {
			$this->domain = \strtolower(\idn_to_ascii($this->params['domainname']));
		}
	}

	/**
	 * Connect to the API and returns response
	 *
	 * @param string $request URL action
	 * @param array $fields POST fields array
	 * @return array
	 * @throws DotRollException
	 */
	protected function connect(string $request, ?array $fields = []): array {
		try {
			$ch = \curl_init();
			\curl_setopt($ch, \CURLOPT_URL, $this->url . $request);
			\curl_setopt($ch, \CURLOPT_HEADER, false);
			\curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
			\curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);
			\curl_setopt($ch, \CURLOPT_SSL_VERIFYHOST, false);
			\curl_setopt($ch, \CURLOPT_HTTPAUTH, \CURLAUTH_BASIC);
			if (!empty($fields)) {
				\curl_setopt($ch, \CURLOPT_POST, true);
				\curl_setopt($ch, \CURLOPT_POSTFIELDS, $fields);
			}
			\curl_setopt($ch, \CURLOPT_USERPWD, $this->username . ':' . $this->password);
			$result = \curl_exec($ch);
			\logModuleCall(self::NAME, $request, $fields, $result);
			if (\curl_errno($ch)) {
				throw new DotRollException(\curl_error($ch), \curl_errno($ch));
			}
			\curl_close($ch);
			$retval = \json_decode($result, true);
			if (empty($retval['result']['code']) || !\in_array($retval['result']['code'], [200, 201])) {
				if (!empty($retval['result']['errorno']) && !empty($retval['result']['errormsg'])) {
					throw new DotRollException($retval['result']['errormsg'], $retval['result']['errorno']);
				} elseif (!empty($retval['result']['errormsg'])) {
					throw new DotRollException($retval['result']['errormsg']);
				} elseif (!empty($retval['result']['errormessages']) && \is_array($retval['result']['errormessages'])) {
					throw new DotRollException(\implode(\PHP_EOL, $retval['result']['errormessages']));
				} elseif (!empty($retval['result']['errormessages'])) {
					throw new DotRollException($retval['result']['errormessages']);
				} elseif (!empty($retval['result']['code']) && !empty($retval['result']['message'])) {
					throw new DotRollException($retval['result']['message'], $retval['result']['code']);
				} elseif (!empty($retval['result']['message'])) {
					throw new DotRollException($retval['result']['message']);
				}
				throw new DotRollException('Unknown error');
			}
			return $retval;
		} catch (DotRollException | \Exception $e) {
			throw new DotRollException($e->getMessage(), $e->getCode());
		}
	}

}
