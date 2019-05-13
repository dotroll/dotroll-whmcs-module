<?php

/**
 * Client, Client interface methods
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2019-05-08
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll;

use \WHMCS\User\Client as User;
use \WHMCS\Session;

/**
 * WHMCS client inteface methods
 */
class Client {

	/**
	 * Default client language
	 *
	 * @var string
	 * @const
	 */
	private const DEFAULT_LANGUAGE = 'english';

	/**
	 * Loaded translation
	 *
	 * @var array
	 */
	protected static $translation = [];

	/**
	 * Used language
	 *
	 * @var string
	 */
	protected static $language;

	/**
	 * Client countries
	 *
	 * @var string
	 */
	protected static $countries;

	/**
	 * Get a translated string from key
	 *
	 * @param string $key Key
	 * @param array|null $variables Replaceable variable(s)
	 * @return string
	 */
	public static function trans(string $key, ?array $variables = null): string {
		self::loadTranslation();
		if (!isset(static::$translation[$key])) {
			if (empty($variables)) {
				return $key;
			}
			return \str_replace(\preg_filter('/^/', ':', \array_keys($variables)), \array_values($variables), $key);
		}
		if (empty($variables)) {
			return static::$translation[$key];
		}
		return \str_replace(\preg_filter('/^/', ':', \array_keys($variables)), \array_values($variables), static::$translation[$key]);
	}

	/**
	 * Get all translation
	 *
	 * @return array
	 */
	public static function get(): array {
		self::loadTranslation();
		return static::$translation;
	}

	/**
	 * Check availabily a translated string from key
	 *
	 * @param string $key Key
	 * @return bool
	 */
	public static function hasTrans(string $key): bool {
		self::loadTranslation();
		if (!isset(static::$translation[$key])) {
			return false;
		}
		return true;
	}

	/**
	 * Load translation
	 *
	 * @global array $_ADDONLANG
	 */
	private function loadTranslation() {
		if (empty(static::$translation)) {
			include \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'lang' . \DIRECTORY_SEPARATOR . static::getLanguage() . '.php';
			static::$translation = $_ADDONLANG;
		}
	}

	/**
	 * Get active client language
	 *
	 * @return string
	 * @throws DotRollException
	 */
	private static function getLanguage(): string {
		try {
			if (!\is_null(static::$language)) {
				return static::$language;
			}
			if (!empty(Session::get('Language'))) {
				if (\in_array(Session::get('Language'), static::getAvailableLanguages())) {
					return static::$language = Session::get('Language');
				}
			}
			if (!empty(Session::get('uid'))) {
				$userLanguage = User::find(Session::get('uid'))->language;
				if (\in_array($userLanguage, static::getAvailableLanguages())) {
					return static::$language = $userLanguage;
				}
			}
			if (\in_array(self::DEFAULT_LANGUAGE, static::getAvailableLanguages())) {
				return static::$language = self::DEFAULT_LANGUAGE;
			}
			return static::$language = \reset(static::getAvailableLanguages());
		} catch (DotRollException | \Exception $e) {
			throw new DotRollException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Get avaliable client languages
	 *
	 * @staticvar array $languagesCache Languages cache
	 * @return array
	 * @throws DotRollException
	 */
	private static function getAvailableLanguages(): array {
		$languages = [];
		static $languagesCache = null;
		$languageDirectory = \ROOTDIR . \DIRECTORY_SEPARATOR . 'lang';
		if (!\is_null($languagesCache) && isset($languagesCache[$languageDirectory])) {
			return $languagesCache[$languageDirectory];
		}
		$glob = \glob($languageDirectory . \DIRECTORY_SEPARATOR . '*.php');
		if ($glob === false) {
			throw new DotRollException('Unable to read client language directory.');
		}
		foreach ($glob as $languageFile) {
			$languageName = \pathinfo($languageFile, \PATHINFO_FILENAME);
			if (
				\preg_match("/^[a-z0-9@_\\.\\-]*\$/i", $languageName) &&
				$languageName != 'index' &&
				\is_file(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'lang' . \DIRECTORY_SEPARATOR . $languageName . '.php')
			) {
				$languages[] = $languageName;
			}
		}
		if (\count($languages) == 0) {
			throw new DotRollException('Could not find any client language files.');
		}
		$languagesCache[$languageDirectory] = $languages;
		return $languages;
	}

	/**
	 * Get countries array
	 *
	 * @return array
	 */
	public static function getCountries(): array {
		if (!\is_null(static::$countries)) {
			return static::$countries;
		}
		static::$countries = \json_decode(\file_get_contents(\ROOTDIR . '/resources/country/dist.countries.json'), true);
		if (\is_file(\ROOTDIR . '/resources/country/countries.json')) {
			static::$countries = \array_merge($countries, \json_decode(\file_get_contents(\ROOTDIR . '/resources/country/countries.json'), true));
		}
		foreach (static::$countries as $code => &$country) {
			if (isset($country['name'])) {
				$country = static::getLanguage() == 'english' ? $country['name'] : (static::hasTrans("countries.$code") ? static::trans("countries.$code") : $country['name']);
			} else {
				unset(static::$countries[$code]);
			}
		}
		if (static::getLanguage() == 'english') {
			\uasort(static::$countries, function ($s1, $s2) {
				\mb_internal_encoding('UTF-8');
				static $chr = array('á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ö' => 'oz', 'ő' => 'oz', 'ú' => 'u', 'ü' => 'uz', 'ű' => 'uz', 'cs' => 'cz', 'zs' => 'zz', 'ccs' => 'czcz', 'ggy' => 'gzgz', 'lly' => 'lzlz', 'nny' => 'nznz', 'ssz' => 'szsz', 'tty' => 'tztz', 'zzs' => 'zzzz');
				return \strcmp(\strtr(\mb_strtolower($s1), $chr), \strtr(\mb_strtolower($s2), $chr));
			});
		}
		return static::$countries;
	}

}
