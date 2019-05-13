<?php

/**
 * Admin, Admin interface methods
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (https://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2016-07-20
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll;

use \WHMCS\User\Admin as AdminUser;
use \WHMCS\Session;

/**
 * WHMCS admin inteface methods
 */
class Admin {

	/**
	 * Default admin folder name
	 *
	 * @var string
	 * @const
	 */
	private const DEFAULT_FOLDER = 'admin';

	/**
	 * Default admin language
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
	 * Get active admin language
	 *
	 * @return string
	 * @throws DotRollException
	 */
	private static function getLanguage(): string {
		try {
			if (!\is_null(static::$language)) {
				return static::$language;
			}
			if (!empty(Session::get('adminid'))) {
				$adminUserLanguage = AdminUser::find(Session::get('adminid'))->language;
				if (\in_array($adminUserLanguage, static::getAvailableLanguages())) {
					return static::$language = $adminUserLanguage;
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
	 * Get avaliable admin languages
	 *
	 * @staticvar array $languagesCache Languages cache
	 * @return array
	 * @throws DotRollException
	 */
	private static function getAvailableLanguages(): array {
		$languages = [];
		static $languagesCache = null;
		$languageDirectory = \ROOTDIR . \DIRECTORY_SEPARATOR . static::getFolderName() . \DIRECTORY_SEPARATOR . 'lang';
		if (!\is_null($languagesCache) && isset($languagesCache[$languageDirectory])) {
			return $languagesCache[$languageDirectory];
		}
		$glob = \glob($languageDirectory . \DIRECTORY_SEPARATOR . '*.php');
		if ($glob === false) {
			throw new DotRollException('Unable to read admin language directory.');
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
			throw new DotRollException('Could not find any admin language files.');
		}
		$languagesCache[$languageDirectory] = $languages;
		return $languages;
	}

	/**
	 * Get admin folder name
	 * 
	 * @return string
	 */
	private static function getFolderName(): string {
		if (!empty($GLOBALS['customadminpath']) && \is_string($GLOBALS['customadminpath'])) {
			return $GLOBALS['customadminpath'];
		}
		return self::DEFAULT_FOLDER;
	}

}
