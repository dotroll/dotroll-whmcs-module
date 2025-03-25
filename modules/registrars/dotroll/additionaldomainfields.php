<?php

/**
 * additionaldomainfields, Additional Domain Fields (aka Extended Attributes)
 *
 * @copyright Copyright (c) 2007 DotRoll Kft. (http://www.dotroll.com)
 * @author Zoltán Istvanovszki <zoltan.istvanovszki@dotroll.com>
 * @since 2025-03-20
 * @package dotroll-whmcs-module
 * @version 2.0.0
 * @license https://www.gnu.org/licenses/gpl.txt GNU General Public License, version 3
 */

namespace WHMCS\Module\Registrar\Dotroll;

use WHMCS\Module\Registrar\Dotroll\Client;

if (!\defined('WHMCS')) {
	die('This file cannot be accessed directly');
}

foreach (Client::getDefaultAdditionalDomainFields('.hu') as $dist) {
	$additionaldomainfields['.hu'][] = [
		'Name' => $dist['Name'],
		'Remove' => true,
	];
}

$additionaldomainfields['.hu'][] = [
	'Name' => 'Date of Birth',
	'LangVar' => 'huBirthdate',
	'Type' => 'text',
	'Size' => 10,
	'Default' => '',
	'Description' => 'Natural person\'s date of birth is required. Format: YYYY-mm-dd',
];

$additionaldomainfields['.hu'][] = [
	'Name' => 'Type of the .hu domain',
	'LangVar' => 'huType',
	'Type' => 'dropdown',
	'Options' => '1f|Single factor,2f|Two-factor',
	'Default' => '1f',
];

$additionaldomainfields['.hu'][] = array(
	'Name' => 'Agree to HU Terms',
	'LangVar' => 'huTLDTermsAgree',
	'Type' => 'tickbox',
	'Description' => "I declare that I am the Domain Applicant for this domain request or am authorized to act on behalf of the Applicant. I warrant that the data provided in my domain application are accurate, and I acknowledge that if the provided data are not truthful or if I fail to report any changes to the data, it may result in the revocation of the domain. I understand that special attention must be paid to maintaining control over the factor data.<br /><br />By clicking the \"Complete Order\" button I confirm the domain registration.<br /><br />I acknowledge and accept the provisions of the <a href='https://www.domain.hu/domain-registration-policy/' target='_blank'>Domain Registration Policy</a> and consider them binding for the entire duration of the domain application and registration.<br /><br />By maintaining the registration, I submit to the decisions of the <a href='https://www.domain.hu/handling-of-complaints/' target='_blank'>Alternative Dispute Resolution Forum</a>.<br /><br />I have read and understood the contents of the <a href='https://www.domain.hu/data-privacy/' target='_blank'>Data Privacy Notice</a> and I grant my consent to the processing of my personal data in the records, as well as I accept that the granting of this consent is lawful according to the national law applicable to me.<br /><br />I have reviewed all the data, and the information is correct.",
	'Required' => true,
);

foreach (['.co.hu', '.2000.hu', '.erotika.hu', '.jogasz.hu', '.sex.hu',
 '.video.hu', '.info.hu', '.agrar.hu', '.film.hu', '.konyvelo.hu', '.shop.hu',
 '.org.hu', '.bolt.hu', '.forum.hu', '.lakas.hu', '.suli.hu', '.priv.hu',
 '.casino.hu', '.games.hu', '.media.hu', '.szex.hu', '.sport.hu', '.city.hu',
 '.hotel.hu', '.news.hu', '.tozsde.hu', '.tm.hu', '.erotica.hu',
 '.ingatlan.hu', '.reklam.hu', '.utazas.hu'] as $sld) {
	$additionaldomainfields[$sld] = $additionaldomainfields['.hu'];
}
