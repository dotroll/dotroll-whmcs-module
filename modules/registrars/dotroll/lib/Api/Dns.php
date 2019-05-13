<?php

/**
 * Dns, DNS Host Record Management.
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-10
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll\Api;

use WHMCS\Module\Registrar\Dotroll\{
	Module,
	DotRollException
};

class Dns extends Module {

	/**
	 * Default TTL
	 *
	 * @var int
	 * @const
	 */
	protected const TTL = 3600;

	/**
	 * Default MX priority
	 *
	 * @var int
	 * @const
	 */
	protected const PRIO = 10;

	/**
	 * DNS records
	 * 
	 * @var array
	 */
	private $records = [
		'compatible' => [],
		'original' => [],
	];

	/**
	 * Get DNS Records for DNS Host Record Management.
	 *
	 * @return array DNS Host Records
	 */
	public function get(): array {
		try {
			return $this->getRecords()['compatible'];
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

	/**
	 * Update DNS Host Records.
	 *
	 * @return array
	 */
	public function save(): array {
		try {
			if (!empty($this->params['dnsrecords'])) {
				$this->doModification($this->params['dnsrecords']);
			}
			return [];
		} catch (DotRollException $e) {
			return $e->toArray();
		}
	}

	/**
	 * Do modification
	 *
	 * @param array $postRecords POsted DNS records
	 */
	private function doModification(array $postRecords) {
		$prevRecords = $this->getRecords()['compatible'];
		$newRecords = [];
		$delRecords = [];
		$modRecords = [];
		foreach ($postRecords as $postRecord) {
			if (empty($postRecord['recid']) && !empty($postRecord['hostname'])) {
				$newRecords[] = $this->extend($postRecord);
			} elseif (
				!empty($postRecord['recid']) &&
				!empty($prevRecords[$postRecord['recid']]) &&
				!empty(\array_diff($prevRecords[$postRecord['recid']], $postRecord))
			) {
				if (empty($postRecord['hostname']) || empty($postRecord['address'])) {
					$delRecords[] = $postRecord['recid'];
				} else {
					$modRecords[] = $this->extend($postRecord);
				}
			}
		}
		$this->connect('/domains/zone/' . $this->domain . '/modify', $this->mergeModification($newRecords, $delRecords, $modRecords));
	}

	/**
	 * Merge posted records to original
	 *
	 * @param array $newRecords New records
	 * @param array $delRecords Deleted records
	 * @param array $modRecords Modified records
	 * @return array
	 */
	private function mergeModification(array $newRecords, array $delRecords, array $modRecords): array {
		foreach ($modRecords as $modRecord) {
			if (
				($this->records['original'][$modRecord['recid']]['type'] == $modRecord['type']) ||
				($this->records['original'][$modRecord['recid']]['type'] == 'A' && $modRecord['type'] == 'MXE') ||
				($this->records['original'][$modRecord['recid']]['type'] == 'Redirect' && \in_array($modRecord['type'], ['URL', 'FRAME'])) ||
				(\in_array($this->records['original'][$modRecord['recid']]['type'], ['DMARC', 'SPF', 'DKIM']) && $modRecord['type'] == 'TXT')
			) {
				switch ($modRecord['type']) {
					case 'A':
					case 'AAAA':
						$this->records['original'][$modRecord['recid']]['address'] = $modRecord['address'];
						break;
					case 'CNAME':
						$this->records['original'][$modRecord['recid']]['cname'] = $modRecord['address'];
						break;
					case 'MX':
						$this->records['original'][$modRecord['recid']]['exchange'] = $modRecord['address'];
						$this->records['original'][$modRecord['recid']]['preference'] = (int) $modRecord['priority'];
						break;
					case 'URL':
					case 'FRAME':
						$this->records['original'][$modRecord['recid']]['redirecttarget'] = (\strpos($modRecord['address'], '://') === false ? 'http://' : '') . $modRecord['address'];
						$this->records['original'][$modRecord['recid']]['redirecttype'] = $modRecord['type'] == 'FRAME' ? 'frame' : 'http';
						break;
					case 'TXT':
						$this->records['original'][$modRecord['recid']]['txtdata'] = $modRecord['address'];
						break;
					case 'MXE':
						$this->records['original'][$modRecord['recid']]['address'] = $modRecord['address'];
						break;
				}
			} else {
				$delRecords[] = $modRecord['recid'];
				$newRecords[] = $modRecord;
			}
		}
		foreach ($delRecords as $delRecord) {
			unset($this->records['original'][$delRecord]);
		}
		$pushNewRecords = [];
		foreach ($newRecords as $idx => $newRecord) {
			if ($newRecord['type'] == 'MXE') {
				$newRecords[] = $this->extend([
					'hostname' => 'mail',
					'type' => 'A',
					'address' => $newRecord['address'],
				]);
				$newRecords[] = $this->extend([
					'hostname' => '@',
					'type' => 'MX',
					'address' => "mail.{$this->domain}",
					'priority' => self::PRIO,
				]);
			}
			unset($newRecords[$idx]);
		}
		foreach ($newRecords as $newRecord) {
			$pushNewRecord = [
				'name' => $newRecord['hostname'],
				'type' => $newRecord['type'],
				'ttl' => self::TTL,
			];
			switch ($newRecord['type']) {
				case 'A':
				case 'AAAA':
					$pushNewRecord['address'] = $newRecord['address'];
					break;
				case 'CNAME':
					$pushNewRecord['cname'] = $newRecord['address'];
					break;
				case 'MX':
					$pushNewRecord['exchange'] = $newRecord['address'];
					$pushNewRecord['preference'] = (int) $newRecord['priority'];
					break;
				case 'URL':
				case 'FRAME':
					$pushNewRecord['redirecttarget'] = (\strpos($modRecord['address'], '://') === false ? 'http://' : '') . $newRecord['address'];
					$pushNewRecord['redirecttype'] = $newRecord['type'] == 'FRAME' ? 'frame' : 'http';
					break;
				default:
					$pushNewRecord['txtdata'] = $modRecord['address'];
					break;
			}
			$pushNewRecords[] = $pushNewRecord;
		}
		$retval = [
			'modify' => \json_encode($this->records['original'])
		];
		if (!empty($newRecords)) {
			$retval['new'] = \json_encode($pushNewRecords);
		}
		return $retval;
	}

	/**
	 * Get DNS Records
	 *
	 * @return array DNS Host Records
	 */
	private function getRecords(): array {
		$zone = $this->connect('/domains/zone/' . $this->domain . '/get');
		if (!empty($zone['domain'])) {
			$this->records['original'] = $zone['domain'];
			foreach ($zone['domain'] as $idx => $record) {
				switch ($record['type']) {
					case 'A':
					case 'AAAA':
					case 'CNAME':
					case 'MX':
					case 'Redirect':
					case 'TXT':
					case 'DMARC':
					case 'SPF':
					case 'DKIM':
						$whmcsRecord = [
							'hostname' => $record['name'],
							'type' => $record['type'],
							'recid' => $idx,
						];
						switch ($record['type']) {
							case 'A':
							case 'AAAA':
								$whmcsRecord['address'] = $record['address'];
								break;
							case 'CNAME':
								$whmcsRecord['address'] = $record['cname'];
								break;
							case 'MX':
								$whmcsRecord['address'] = $record['exchange'];
								$whmcsRecord['priority'] = $record['preference'];
								break;
							case 'Redirect':
								$whmcsRecord['address'] = $record['redirecttarget'];
								$whmcsRecord['type'] = $record['redirecttype'] == 'frame' ? 'FRAME' : 'URL';
								break;
							default:
								$whmcsRecord['address'] = $record['txtdata'];
								$whmcsRecord['type'] = 'TXT';
								break;
						}
						$this->records['compatible'][$idx] = $whmcsRecord;
						break;
				}
			}
		}
		$this->reducing();
		return $this->records;
	}

	/**
	 * Get extended and dotted endings record
	 * 
	 * @param array $record WHMCS DNS record
	 * @return array
	 */
	private function extend(array $record): array {
		if ($record['hostname'] == '@') {
			$record['hostname'] = $this->domain;
		} elseif ($record['hostname'] != $this->domain) {
			$record['hostname'] .= ".{$this->domain}";
		}
		if (\in_array($record['type'], ['MX', 'CNAME']) && $record['address'] == '@') {
			$record['address'] = $this->domain;
		} elseif (\in_array($record['type'], ['MX', 'CNAME']) && $record['address'][-1] != '.') {
			$record['address'] .= '.';
		}
		if (\in_array($record['type'], ['MX', 'CNAME'])) {
			$record['address'] = \idn_to_utf8($record['address']);
		}
		if ($record['hostname'][-1] != '.') {
			$record['hostname'] .= '.';
		}
		$record['hostname'] = \idn_to_utf8($record['hostname']);
		return $record;
	}

	/**
	 * Reduce Domain Names
	 */
	private function reducing() {
		$mxeMx = null;
		$mxeA = null;
		$mxeAAAA = null;
		foreach ($this->records['compatible'] as &$record) {
			$record['hostname'] = \idn_to_ascii($record['hostname']);
			if ($record['hostname'][-1] == '.') {
				$record['hostname'] = \substr($record['hostname'], 0, -1);
			}
			if (\in_array($record['type'], ['MX', 'CNAME'])) {
				$record['address'] = \idn_to_ascii($record['address']);
			}
			if (\in_array($record['type'], ['MX', 'CNAME']) && $record['address'][-1] == '.') {
				$record['address'] = \substr($record['address'], 0, -1);
			}
			if ($record['hostname'] != $this->domain) {
				$pos = \strrpos($record['hostname'], ".{$this->domain}");
				if ($pos !== false) {
					$record['hostname'] = \substr_replace($record['hostname'], '', $pos, \strlen(".{$this->domain}"));
				}
			}
			if ($record['hostname'] == 'mail' && $record['type'] == 'A') {
				$mxeA = $record['recid'];
			}
			if ($record['hostname'] == 'mail' && $record['type'] == 'AAAA') {
				$mxeAAAA = $record['recid'];
			}
			if ($record['hostname'] == $this->domain && $record['type'] == 'MX' && $record['address'] == "mail.{$this->domain}") {
				$mxeMx = $record['recid'];
			}
		}
		if ($mxeMx !== null && $mxeA !== null && $mxeAAAA === null) {
			$this->records['compatible'][$mxeA] = [
				'hostname' => $this->domain,
				'type' => 'MXE',
				'address' => $this->records['compatible'][$mxeA]['address'],
				'recid' => $mxeA,
			];
			unset($this->records['compatible'][$mxeMx]);
		}
	}

}
