<?php
/**
 * Class for working with BibTex data
 *
 * A class which provides common methods to access and
 * create Strings in BibTex format
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Structures
 * @package    Structures_BibTex
 * @author     Elmar Pitschke <elmar.pitschke@gmx.de>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: BibTex.php 304756 2010-10-25 10:19:43Z clockwerx $
 * @link       http://pear.php.net/package/Structures_BibTex
 */

class Structures_BibTex {
	public
		$data,
		$content,
		$warnings,
		$rtfstring,
		$htmlstring,
		$allowedEntryTypes,
		$authorstring;

	private
		$_delimiters,
		$_options;

	public function __construct ($options = array()) {
		$this->escapedChars = array (
			'#' => '\#',
			'$' => '\textdollar',
			'%' => '\%',
			'&' => '\&',
			'*' => '\ast',
			'^' => '\^{}',
			'_' => '\_',
			'¡' => '\textexclamdown',
			'¢' => '\textcent',
			'£' => '\textsterling',
			'¤' => '\textcurrency',
			'¥' => '\textyen',
			'¦' => '\textbrokenbar',
			'§' => '\textsection',
			'¨' => '\textasciidieresis',
			'©' => '\textcopyright',
			'ª' => '\textordfeminine',
			'«' => '\guillemotleft',
			'¬' => '\lnot',
			'­' => '\-',
			'®' => '\textregistered',
			'¯' => '\textasciimacron',
			'°' => '\textdegree',
			'±' => '\pm',
			'²' => '{^2}',
			'³' => '{^3}',
			'µ' => '\mathrm{\mu}',
			'¶' => '\textparagraph',
			'·' => '\cdot',
			'¸' => '\c{}',
			'¹' => '{^1}',
			'º' => '\textordmasculine',
			'»' => '\guillemotright',
			'¼' => '\textonequarter',
			'½' => '\textonehalf',
			'¾' => '\textthreequarters',
			'¿' => '\textquestiondown',
			'À' => '\`{A}',
			'Á' => '\\\'{A}',
			'Â' => '\^{A}',
			'Ã' => '\~{A}',
			'Ä' => '\"{A}',
			'Å' => '\AA',
			'Æ' => '\AE',
			'Ç' => '\c{C}',
			'È' => '\`{E}',
			'É' => '\\\'{E}',
			'Ê' => '\^{E}',
			'Ë' => '\"{E}',
			'Ì' => '\`{I}',
			'Í' => '\\\'{I}',
			'Î' => '\^{I}',
			'Ï' => '\"{I}',
			'Ð' => '\DH',
			'Ñ' => '\~{N}',
			'Ò' => '\`{O}',
			'Ó' => '\\\'{O}',
			'Ô' => '\^{O}',
			'Õ' => '\~{O}',
			'Ö' => '\"{O}',
			'×' => '\texttimes',
			'Ø' => '\O',
			'Ù' => '\`{U}',
			'Ú' => '\\\'{U}',
			'Û' => '\^{U}',
			'Ü' => '\"{U}',
			'Ý' => '\\\'{Y}',
			'Þ' => '\TH',
			'ß' => '\ss',
			'à' => '\`{a}',
			'á' => '\\\'{a}',
			'â' => '\^{a}',
			'ã' => '\~{a}',
			'ä' => '\"{a}',
			'å' => '\aa',
			'æ' => '\ae',
			'ç' => '\c{c}',
			'è' => '\`{e}',
			'é' => '\\\'{e}',
			'ê' => '\^{e}',
			'ë' => '\"{e}',
			'ì' => '\`{i}',
			'í' => '\\\'{i}',
			'î' => '\^{i}',
			'ï' => '\"{i}',
			'ð' => '\dh',
			'ñ' => '\~{n}',
			'ò' => '\`{o}',
			'ó' => '\\\'{o}',
			'ô' => '\^{o}',
			'õ' => '\~{o}',
			'ö' => '\"{o}',
			'÷' => '\div',
			'ø' => '\o',
			'ù' => '\`{u}',
			'ú' => '\\\'{u}',
			'û' => '\^{u}',
			'ü' => '\"{u}',
			'ý' => '\\\'{y}',
			'þ' => '\th',
			'ÿ' => '\"{y}',
			'Ā' => '\={A}',
			'ā' => '\={a}',
			'Ă' => '\u{A}',
			'ă' => '\u{a}',
			'Ą' => '\k{A}',
			'ą' => '\k{a}',
			'Ć' => '\\\'{C}',
			'ć' => '\\\'{c}',
			'Ĉ' => '\^{C}',
			'ĉ' => '\^{c}',
			'Ċ' => '\.{C}',
			'ċ' => '\.{c}',
			'Č' => '\v{C}',
			'č' => '\v{c}',
			'Ď' => '\v{D}',
			'ď' => '\v{d}',
			'Đ' => '\DJ',
			'đ' => '\dj',
			'Ē' => '\={E}',
			'ē' => '\={e}',
			'Ĕ' => '\u{E}',
			'ĕ' => '\u{e}',
			'Ė' => '\.{E}',
			'ė' => '\.{e}',
			'Ę' => '\k{E}',
			'ę' => '\k{e}',
			'Ě' => '\v{E}',
			'ě' => '\v{e}',
			'Ĝ' => '\^{G}',
			'ĝ' => '\^{g}',
			'Ğ' => '\u{G}',
			'ğ' => '\u{g}',
			'Ġ' => '\.{G}',
			'ġ' => '\.{g}',
			'Ģ' => '\c{G}',
			'ģ' => '\c{g}',
			'Ĥ' => '\^{H}',
			'ĥ' => '\^{h}',
			'ħ' => '\Elzxh',
			'Ĩ' => '\~{I}',
			'ĩ' => '\~{i}',
			'Ī' => '\={I}',
			'ī' => '\={i}',
			'Ĭ' => '\u{I}',
			'ĭ' => '\u{i}',
			'Į' => '\k{I}',
			'į' => '\k{i}',
			'İ' => '\.{I}',
			'ı' => '\i',
			'Ĳ' => 'IJ',
			'ĳ' => 'ij',
			'Ĵ' => '\^{J}',
			'ĵ' => '\^{j}',
			'Ķ' => '\c{K}',
			'ķ' => '\c{k}',
			'Ĺ' => '\\\'{L}',
			'ĺ' => '\\\'{l}',
			'Ļ' => '\c{L}',
			'ļ' => '\c{l}',
			'Ľ' => '\v{L}',
			'ľ' => '\v{l}',
			'Ł' => '\L',
			'ł' => '\l',
			'Ń' => '\\\'{N}',
			'ń' => '\\\'{n}',
			'Ņ' => '\c{N}',
			'ņ' => '\c{n}',
			'Ň' => '\v{N}',
			'ň' => '\v{n}',
			'ŉ' => '\\\'n',
			'Ŋ' => '\NG',
			'ŋ' => '\ng',
			'Ō' => '\={O}',
			'ō' => '\={o}',
			'Ŏ' => '\u{O}',
			'ŏ' => '\u{o}',
			'Ő' => '\H{O}',
			'ő' => '\H{o}',
			'Œ' => '\OE',
			'œ' => '\oe',
			'Ŕ' => '\\\'{R}',
			'ŕ' => '\\\'{r}',
			'Ŗ' => '\c{R}',
			'ŗ' => '\c{r}',
			'Ř' => '\v{R}',
			'ř' => '\v{r}',
			'Ś' => '\\\'{S}',
			'ś' => '\\\'{s}',
			'Ŝ' => '\^{S}',
			'ŝ' => '\^{s}',
			'Ş' => '\c{S}',
			'ş' => '\c{s}',
			'Š' => '\v{S}',
			'š' => '\v{s}',
			'Ţ' => '\c{T}',
			'ţ' => '\c{t}',
			'Ť' => '\v{T}',
			'ť' => '\v{t}',
			'Ũ' => '\~{U}',
			'ũ' => '\~{u}',
			'Ū' => '\={U}',
			'ū' => '\={u}',
			'Ŭ' => '\u{U}',
			'ŭ' => '\u{u}',
			'Ů' => '\r{U}',
			'ů' => '\r{u}',
			'Ű' => '\H{U}',
			'ű' => '\H{u}',
			'Ų' => '\k{U}',
			'ų' => '\k{u}',
			'Ŵ' => '\^{W}',
			'ŵ' => '\^{w}',
			'Ŷ' => '\^{Y}',
			'ŷ' => '\^{y}',
			'Ÿ' => '\"{Y}',
			'Ź' => '\\\'{Z}',
			'ź' => '\\\'{z}',
			'Ż' => '\.{Z}',
			'ż' => '\.{z}',
			'Ž' => '\v{Z}',
			'ž' => '\v{z}',
			'ɐ' => '\Elztrna',
			'ɒ' => '\Elztrnsa',
			'ɔ' => '\Elzopeno',
			'ɖ' => '\Elzrtld',
			'ə' => '\Elzschwa',
			'ɛ' => '\varepsilon',
			'ɡ' => 'g',
			'ɣ' => '\Elzpgamma',
			'ɤ' => '\Elzpbgam',
			'ɥ' => '\Elztrnh',
			'ɬ' => '\Elzbtdl',
			'ɭ' => '\Elzrtll',
			'ɯ' => '\Elztrnm',
			'ɰ' => '\Elztrnmlr',
			'ɱ' => '\Elzltlmr',
			'ɲ' => '\Elzltln',
			'ɳ' => '\Elzrtln',
			'ɷ' => '\Elzclomeg',
			'ɸ' => '\textphi',
			'ɹ' => '\Elztrnr',
			'ɺ' => '\Elztrnrl',
			'ɻ' => '\Elzrttrnr',
			'ɼ' => '\Elzrl',
			'ɽ' => '\Elzrtlr',
			'ɾ' => '\Elzfhr',
			'ʂ' => '\Elzrtls',
			'ʃ' => '\Elzesh',
			'ʇ' => '\Elztrnt',
			'ʈ' => '\Elzrtlt',
			'ʊ' => '\Elzpupsil',
			'ʋ' => '\Elzpscrv',
			'ʌ' => '\Elzinvv',
			'ʍ' => '\Elzinvw',
			'ʎ' => '\Elztrny',
			'ʐ' => '\Elzrtlz',
			'ʒ' => '\Elzyogh',
			'ʔ' => '\Elzglst',
			'ʕ' => '\Elzreglst',
			'ʖ' => '\Elzinglst',
			'ʞ' => '\textturnk',
			'ʤ' => '\Elzdyogh',
			'ʧ' => '\Elztesh',
			'ʼ' => '\'',
			'ˇ' => '\textasciicaron',
			'ˈ' => '\Elzverts',
			'ˌ' => '\Elzverti',
			'ː' => '\Elzlmrk',
			'ˑ' => '\Elzhlmrk',
			'˒' => '\Elzsbrhr',
			'˓' => '\Elzsblhr',
			'˔' => '\Elzrais',
			'˕' => '\Elzlow',
			'˘' => '\textasciibreve',
			'˙' => '\textperiodcentered',
			'˚' => '\r{}',
			'˛' => '\k{}',
			'˜' => '\texttildelow',
			'˝' => '\H{}',
			'˥' => '\tone{55}',
			'˦' => '\tone{44}',
			'˧' => '\tone{33}',
			'˨' => '\tone{22}',
			'˩' => '\tone{11}',
			'̀' => '\`',
			'́' => '\\\'',
			'̂' => '\^',
			'̃' => '\~',
			'̄' => '\=',
			'̆' => '\u',
			'̇' => '\.',
			'̈' => '\"',
			'̊' => '\r',
			'̋' => '\H',
			'̌' => '\v',
			'̏' => '\cyrchar\C',
			'̡' => '\Elzpalh',
			'̢' => '\Elzrh',
			'̧' => '\c',
			'̨' => '\k',
			'̪' => '\Elzsbbrg',
			'̵' => '\Elzxl',
			'̶' => '\Elzbar',
			'Ά' => '\\\'{A}',
			'Έ' => '\\\'{E}',
			'Ή' => '\\\'{H}',
			'Ί' => '\\\'{}{I}',
			'Ό' => '\\\'{}O',
			'Ύ' => '\mathrm{\'Y}',
			'Ώ' => '\mathrm{\'\Omega}',
			'ΐ' => '\acute{\ddot{\iota}}',
			'Α' => '\Alpha',
			'Β' => '\Beta',
			'Γ' => '\Gamma',
			'Δ' => '\Delta',
			'Ε' => '\Epsilon',
			'Ζ' => '\Zeta',
			'Η' => '\Eta',
			'Θ' => '\Theta',
			'Ι' => '\Iota',
			'Κ' => '\Kappa',
			'Λ' => '\Lambda',
			'Μ' => 'M',
			'Ν' => 'N',
			'Ξ' => '\Xi',
			'Ο' => 'O',
			'Π' => '\Pi',
			'Ρ' => '\Rho',
			'Σ' => '\Sigma',
			'Τ' => '\Tau',
			'Υ' => '\Upsilon',
			'Φ' => '\Phi',
			'Χ' => '\Chi',
			'Ψ' => '\Psi',
			'Ω' => '\Omega',
			'Ϊ' => '\mathrm{\ddot{I}}',
			'Ϋ' => '\mathrm{\ddot{Y}}',
			'ά' => '\\\'{$\alpha$}',
			'έ' => '\acute{\epsilon}',
			'ή' => '\acute{\eta}',
			'ί' => '\acute{\iota}',
			'ΰ' => '\acute{\ddot{\upsilon}}',
			'α' => '\alpha',
			'β' => '\beta',
			'γ' => '\gamma',
			'δ' => '\delta',
			'ε' => '\epsilon',
			'ζ' => '\zeta',
			'η' => '\eta',
			'θ' => '\texttheta',
			'ι' => '\iota',
			'κ' => '\kappa',
			'λ' => '\lambda',
			'μ' => '\mu',
			'ν' => '\nu',
			'ξ' => '\xi',
			'ο' => 'o',
			'π' => '\pi',
			'ρ' => '\rho',
			'ς' => '\varsigma',
			'σ' => '\sigma',
			'τ' => '\tau',
			'υ' => '\upsilon',
			'φ' => '\varphi',
			'χ' => '\chi',
			'ψ' => '\psi',
			'ω' => '\omega',
			'ϊ' => '\ddot{\iota}',
			'ϋ' => '\ddot{\upsilon}',
			'ό' => '\\\'{o}',
			'ύ' => '\acute{\upsilon}',
			'ώ' => '\acute{\omega}',
			'ϐ' => '\Pisymbol{ppi022}{87}',
			'ϑ' => '\textvartheta',
			'ϒ' => '\Upsilon',
			'ϕ' => '\phi',
			'ϖ' => '\varpi',
			'Ϛ' => '\Stigma',
			'Ϝ' => '\Digamma',
			'Ϟ' => '\Koppa',
			'Ϡ' => '\Sampi',
			'ϰ' => '\varkappa',
			'ϱ' => '\varrho',
			'Ё' => '\cyrchar\CYRYO',
			'Ђ' => '\cyrchar\CYRDJE',
			'Ѓ' => '\cyrchar{\\\'\CYRG}',
			'Є' => '\cyrchar\CYRIE',
			'Ѕ' => '\cyrchar\CYRDZE',
			'І' => '\cyrchar\CYRII',
			'Ї' => '\cyrchar\CYRYI',
			'Ј' => '\cyrchar\CYRJE',
			'Љ' => '\cyrchar\CYRLJE',
			'Њ' => '\cyrchar\CYRNJE',
			'Ћ' => '\cyrchar\CYRTSHE',
			'Ќ' => '\cyrchar{\\\'\CYRK}',
			'Ў' => '\cyrchar\CYRUSHRT',
			'Џ' => '\cyrchar\CYRDZHE',
			'А' => '\cyrchar\CYRA',
			'Б' => '\cyrchar\CYRB',
			'В' => '\cyrchar\CYRV',
			'Г' => '\cyrchar\CYRG',
			'Д' => '\cyrchar\CYRD',
			'Е' => '\cyrchar\CYRE',
			'Ж' => '\cyrchar\CYRZH',
			'З' => '\cyrchar\CYRZ',
			'И' => '\cyrchar\CYRI',
			'Й' => '\cyrchar\CYRISHRT',
			'К' => '\cyrchar\CYRK',
			'Л' => '\cyrchar\CYRL',
			'М' => '\cyrchar\CYRM',
			'Н' => '\cyrchar\CYRN',
			'О' => '\cyrchar\CYRO',
			'П' => '\cyrchar\CYRP',
			'Р' => '\cyrchar\CYRR',
			'С' => '\cyrchar\CYRS',
			'Т' => '\cyrchar\CYRT',
			'У' => '\cyrchar\CYRU',
			'Ф' => '\cyrchar\CYRF',
			'Х' => '\cyrchar\CYRH',
			'Ц' => '\cyrchar\CYRC',
			'Ч' => '\cyrchar\CYRCH',
			'Ш' => '\cyrchar\CYRSH',
			'Щ' => '\cyrchar\CYRSHCH',
			'Ъ' => '\cyrchar\CYRHRDSN',
			'Ы' => '\cyrchar\CYRERY',
			'Ь' => '\cyrchar\CYRSFTSN',
			'Э' => '\cyrchar\CYREREV',
			'Ю' => '\cyrchar\CYRYU',
			'Я' => '\cyrchar\CYRYA',
			'а' => '\cyrchar\cyra',
			'б' => '\cyrchar\cyrb',
			'в' => '\cyrchar\cyrv',
			'г' => '\cyrchar\cyrg',
			'д' => '\cyrchar\cyrd',
			'е' => '\cyrchar\cyre',
			'ж' => '\cyrchar\cyrzh',
			'з' => '\cyrchar\cyrz',
			'и' => '\cyrchar\cyri',
			'й' => '\cyrchar\cyrishrt',
			'к' => '\cyrchar\cyrk',
			'л' => '\cyrchar\cyrl',
			'м' => '\cyrchar\cyrm',
			'н' => '\cyrchar\cyrn',
			'о' => '\cyrchar\cyro',
			'п' => '\cyrchar\cyrp',
			'р' => '\cyrchar\cyrr',
			'с' => '\cyrchar\cyrs',
			'т' => '\cyrchar\cyrt',
			'у' => '\cyrchar\cyru',
			'ф' => '\cyrchar\cyrf',
			'х' => '\cyrchar\cyrh',
			'ц' => '\cyrchar\cyrc',
			'ч' => '\cyrchar\cyrch',
			'ш' => '\cyrchar\cyrsh',
			'щ' => '\cyrchar\cyrshch',
			'ъ' => '\cyrchar\cyrhrdsn',
			'ы' => '\cyrchar\cyrery',
			'ь' => '\cyrchar\cyrsftsn',
			'э' => '\cyrchar\cyrerev',
			'ю' => '\cyrchar\cyryu',
			'я' => '\cyrchar\cyrya',
			'ё' => '\cyrchar\cyryo',
			'ђ' => '\cyrchar\cyrdje',
			'ѓ' => '\cyrchar{\\\'\cyrg}',
			'є' => '\cyrchar\cyrie',
			'ѕ' => '\cyrchar\cyrdze',
			'і' => '\cyrchar\cyrii',
			'ї' => '\cyrchar\cyryi',
			'ј' => '\cyrchar\cyrje',
			'љ' => '\cyrchar\cyrlje',
			'њ' => '\cyrchar\cyrnje',
			'ћ' => '\cyrchar\cyrtshe',
			'ќ' => '\cyrchar{\\\'\cyrk}',
			'ў' => '\cyrchar\cyrushrt',
			'џ' => '\cyrchar\cyrdzhe',
			'Ѡ' => '\cyrchar\CYROMEGA',
			'ѡ' => '\cyrchar\cyromega',
			'Ѣ' => '\cyrchar\CYRYAT',
			'Ѥ' => '\cyrchar\CYRIOTE',
			'ѥ' => '\cyrchar\cyriote',
			'Ѧ' => '\cyrchar\CYRLYUS',
			'ѧ' => '\cyrchar\cyrlyus',
			'Ѩ' => '\cyrchar\CYRIOTLYUS',
			'ѩ' => '\cyrchar\cyriotlyus',
			'Ѫ' => '\cyrchar\CYRBYUS',
			'Ѭ' => '\cyrchar\CYRIOTBYUS',
			'ѭ' => '\cyrchar\cyriotbyus',
			'Ѯ' => '\cyrchar\CYRKSI',
			'ѯ' => '\cyrchar\cyrksi',
			'Ѱ' => '\cyrchar\CYRPSI',
			'ѱ' => '\cyrchar\cyrpsi',
			'Ѳ' => '\cyrchar\CYRFITA',
			'Ѵ' => '\cyrchar\CYRIZH',
			'Ѹ' => '\cyrchar\CYRUK',
			'ѹ' => '\cyrchar\cyruk',
			'Ѻ' => '\cyrchar\CYROMEGARND',
			'ѻ' => '\cyrchar\cyromegarnd',
			'Ѽ' => '\cyrchar\CYROMEGATITLO',
			'ѽ' => '\cyrchar\cyromegatitlo',
			'Ѿ' => '\cyrchar\CYROT',
			'ѿ' => '\cyrchar\cyrot',
			'Ҁ' => '\cyrchar\CYRKOPPA',
			'ҁ' => '\cyrchar\cyrkoppa',
			'҂' => '\cyrchar\cyrthousands',
			'Ґ' => '\cyrchar\CYRGUP',
			'ґ' => '\cyrchar\cyrgup',
			'Ғ' => '\cyrchar\CYRGHCRS',
			'ғ' => '\cyrchar\cyrghcrs',
			'Ҕ' => '\cyrchar\CYRGHK',
			'ҕ' => '\cyrchar\cyrghk',
			'Җ' => '\cyrchar\CYRZHDSC',
			'җ' => '\cyrchar\cyrzhdsc',
			'Ҙ' => '\cyrchar\CYRZDSC',
			'ҙ' => '\cyrchar\cyrzdsc',
			'Қ' => '\cyrchar\CYRKDSC',
			'қ' => '\cyrchar\cyrkdsc',
			'Ҝ' => '\cyrchar\CYRKVCRS',
			'ҝ' => '\cyrchar\cyrkvcrs',
			'Ҟ' => '\cyrchar\CYRKHCRS',
			'ҟ' => '\cyrchar\cyrkhcrs',
			'Ҡ' => '\cyrchar\CYRKBEAK',
			'ҡ' => '\cyrchar\cyrkbeak',
			'Ң' => '\cyrchar\CYRNDSC',
			'ң' => '\cyrchar\cyrndsc',
			'Ҥ' => '\cyrchar\CYRNG',
			'ҥ' => '\cyrchar\cyrng',
			'Ҧ' => '\cyrchar\CYRPHK',
			'ҧ' => '\cyrchar\cyrphk',
			'Ҩ' => '\cyrchar\CYRABHHA',
			'ҩ' => '\cyrchar\cyrabhha',
			'Ҫ' => '\cyrchar\CYRSDSC',
			'ҫ' => '\cyrchar\cyrsdsc',
			'Ҭ' => '\cyrchar\CYRTDSC',
			'ҭ' => '\cyrchar\cyrtdsc',
			'Ү' => '\cyrchar\CYRY',
			'ү' => '\cyrchar\cyry',
			'Ұ' => '\cyrchar\CYRYHCRS',
			'ұ' => '\cyrchar\cyryhcrs',
			'Ҳ' => '\cyrchar\CYRHDSC',
			'ҳ' => '\cyrchar\cyrhdsc',
			'Ҵ' => '\cyrchar\CYRTETSE',
			'ҵ' => '\cyrchar\cyrtetse',
			'Ҷ' => '\cyrchar\CYRCHRDSC',
			'ҷ' => '\cyrchar\cyrchrdsc',
			'Ҹ' => '\cyrchar\CYRCHVCRS',
			'ҹ' => '\cyrchar\cyrchvcrs',
			'Һ' => '\cyrchar\CYRSHHA',
			'һ' => '\cyrchar\cyrshha',
			'Ҽ' => '\cyrchar\CYRABHCH',
			'ҽ' => '\cyrchar\cyrabhch',
			'Ҿ' => '\cyrchar\CYRABHCHDSC',
			'ҿ' => '\cyrchar\cyrabhchdsc',
			'Ӏ' => '\cyrchar\CYRpalochka',
			'Ӄ' => '\cyrchar\CYRKHK',
			'ӄ' => '\cyrchar\cyrkhk',
			'Ӈ' => '\cyrchar\CYRNHK',
			'ӈ' => '\cyrchar\cyrnhk',
			'Ӌ' => '\cyrchar\CYRCHLDSC',
			'ӌ' => '\cyrchar\cyrchldsc',
			'Ӕ' => '\cyrchar\CYRAE',
			'ӕ' => '\cyrchar\cyrae',
			'Ә' => '\cyrchar\CYRSCHWA',
			'ә' => '\cyrchar\cyrschwa',
			'Ӡ' => '\cyrchar\CYRABHDZE',
			'ӡ' => '\cyrchar\cyrabhdze',
			'Ө' => '\cyrchar\CYROTLD',
			'ө' => '\cyrchar\cyrotld',
			' ' => '\hspace{0.6em}',
			' ' => '\hspace{1em}',
			' ' => '\hspace{0.33em}',
			' ' => '\hspace{0.25em}',
			' ' => '\hspace{0.166em}',
			' ' => '\hphantom{0}',
			' ' => '\hphantom{,}',
			' ' => '\hspace{0.167em}',
			' ' => '\;',
			' ' => '\mkern1mu',
			'‐' => '-',
			'–' => '\textendash',
			'—' => '\textemdash',
			'―' => '\rule{1em}{1pt}',
			'‖' => '\Vert',
			'‘' => '`',
			'’' => '\'',
			'‚' => ',',
			'‛' => '\Elzreapos',
			'“' => '\textquotedblleft',
			'”' => '\textquotedblright',
			'„' => ',,',
			'†' => '\textdagger',
			'‡' => '\textdaggerdbl',
			'•' => '\textbullet',
			'․' => '.',
			'‥' => '..',
			'…' => '\ldots',
			'‰' => '\textperthousand',
			'‱' => '\textpertenthousand',
			'′' => '{\'}',
			'″' => '{\'\'}',
			'‴' => '{\'\'\'}',
			'‵' => '\backprime',
			'‹' => '\guilsinglleft',
			'›' => '\guilsinglright',
			'₧' => '\ensuremath{\Elzpes}',
			'€' => '\mbox{\texteuro}',
			'ℂ' => '\mathbb{C}',
			'ℊ' => '\mathscr{g}',
			'ℋ' => '\mathscr{H}',
			'ℌ' => '\mathfrak{H}',
			'ℍ' => '\mathbb{H}',
			'ℏ' => '\hslash',
			'ℐ' => '\mathscr{I}',
			'ℑ' => '\mathfrak{I}',
			'ℒ' => '\mathscr{L}',
			'ℓ' => '\mathscr{l}',
			'ℕ' => '\mathbb{N}',
			'№' => '\cyrchar\textnumero',
			'℘' => '\wp',
			'ℙ' => '\mathbb{P}',
			'ℚ' => '\mathbb{Q}',
			'ℛ' => '\mathscr{R}',
			'ℜ' => '\mathfrak{R}',
			'ℝ' => '\mathbb{R}',
			'℞' => '\Elzxrat',
			'™' => '\texttrademark',
			'ℤ' => '\mathbb{Z}',
			'Ω' => '\Omega',
			'℧' => '\mho',
			'ℨ' => '\mathfrak{Z}',
			'℩' => '\ElsevierGlyph{2129}',
			'Å' => '\AA',
			'ℬ' => '\mathscr{B}',
			'ℭ' => '\mathfrak{C}',
			'ℯ' => '\mathscr{e}',
			'ℰ' => '\mathscr{E}',
			'ℱ' => '\mathscr{F}',
			'ℳ' => '\mathscr{M}',
			'ℴ' => '\mathscr{o}',
			'ℵ' => '\aleph',
			'ℶ' => '\beth',
			'ℷ' => '\gimel',
			'ℸ' => '\daleth',
			'⅓' => '\textfrac{1}{3}',
			'⅔' => '\textfrac{2}{3}',
			'⅕' => '\textfrac{1}{5}',
			'⅖' => '\textfrac{2}{5}',
			'⅗' => '\textfrac{3}{5}',
			'⅘' => '\textfrac{4}{5}',
			'⅙' => '\textfrac{1}{6}',
			'⅚' => '\textfrac{5}{6}',
			'⅛' => '\textfrac{1}{8}',
			'⅜' => '\textfrac{3}{8}',
			'⅝' => '\textfrac{5}{8}',
			'⅞' => '\textfrac{7}{8}',
			'←' => '\leftarrow',
			'↑' => '\uparrow',
			'→' => '\rightarrow',
			'↓' => '\downarrow',
			'↔' => '\leftrightarrow',
			'↕' => '\updownarrow',
			'↖' => '\nwarrow',
			'↗' => '\nearrow',
			'↘' => '\searrow',
			'↙' => '\swarrow',
			'↚' => '\nleftarrow',
			'↛' => '\nrightarrow',
			'↜' => '\arrowwaveright',
			'↝' => '\arrowwaveright',
			'↞' => '\twoheadleftarrow',
			'↠' => '\twoheadrightarrow',
			'↢' => '\leftarrowtail',
			'↣' => '\rightarrowtail',
			'↦' => '\mapsto',
			'↩' => '\hookleftarrow',
			'↪' => '\hookrightarrow',
			'↫' => '\looparrowleft',
			'↬' => '\looparrowright',
			'↭' => '\leftrightsquigarrow',
			'↮' => '\nleftrightarrow',
			'↰' => '\Lsh',
			'↱' => '\Rsh',
			'↳' => '\ElsevierGlyph{21B3}',
			'↶' => '\curvearrowleft',
			'↷' => '\curvearrowright',
			'↺' => '\circlearrowleft',
			'↻' => '\circlearrowright',
			'↼' => '\leftharpoonup',
			'↽' => '\leftharpoondown',
			'↾' => '\upharpoonright',
			'↿' => '\upharpoonleft',
			'⇀' => '\rightharpoonup',
			'⇁' => '\rightharpoondown',
			'⇂' => '\downharpoonright',
			'⇃' => '\downharpoonleft',
			'⇄' => '\rightleftarrows',
			'⇅' => '\dblarrowupdown',
			'⇆' => '\leftrightarrows',
			'⇇' => '\leftleftarrows',
			'⇈' => '\upuparrows',
			'⇉' => '\rightrightarrows',
			'⇊' => '\downdownarrows',
			'⇋' => '\leftrightharpoons',
			'⇌' => '\rightleftharpoons',
			'⇍' => '\nLeftarrow',
			'⇎' => '\nLeftrightarrow',
			'⇏' => '\nRightarrow',
			'⇐' => '\Leftarrow',
			'⇑' => '\Uparrow',
			'⇒' => '\Rightarrow',
			'⇓' => '\Downarrow',
			'⇔' => '\Leftrightarrow',
			'⇕' => '\Updownarrow',
			'⇚' => '\Lleftarrow',
			'⇛' => '\Rrightarrow',
			'⇝' => '\rightsquigarrow',
			'∀' => '\forall',
			'∁' => '\complement',
			'∂' => '\partial',
			'∃' => '\exists',
			'∄' => '\nexists',
			'∅' => '\varnothing',
			'∇' => '\nabla',
			'∈' => '\in',
			'∉' => '\not\in',
			'∋' => '\ni',
			'∌' => '\not\ni',
			'∏' => '\prod',
			'∐' => '\coprod',
			'∑' => '\sum',
			'−' => '-',
			'∓' => '\mp',
			'∔' => '\dotplus',
			'∖' => '\setminus',
			'∗' => '{_\ast}',
			'∘' => '\circ',
			'∙' => '\bullet',
			'√' => '\surd',
			'∝' => '\propto',
			'∞' => '\infty',
			'∟' => '\rightangle',
			'∠' => '\angle',
			'∡' => '\measuredangle',
			'∢' => '\sphericalangle',
			'∣' => '\mid',
			'∤' => '\nmid',
			'∥' => '\parallel',
			'∦' => '\nparallel',
			'∧' => '\wedge',
			'∨' => '\vee',
			'∩' => '\cap',
			'∪' => '\cup',
			'∫' => '\int',
			'∬' => '\int\!\int',
			'∭' => '\int\!\int\!\int',
			'∮' => '\oint',
			'∯' => '\surfintegral',
			'∰' => '\volintegral',
			'∱' => '\clwintegral',
			'∲' => '\ElsevierGlyph{2232}',
			'∳' => '\ElsevierGlyph{2233}',
			'∴' => '\therefore',
			'∵' => '\because',
			'∷' => '\Colon',
			'∸' => '\ElsevierGlyph{2238}',
			'∺' => '\mathbin{{:}\!\!{-}\!\!{:}}',
			'∻' => '\homothetic',
			'∼' => '\sim',
			'∽' => '\backsim',
			'∾' => '\lazysinv',
			'≀' => '\wr',
			'≁' => '\not\sim',
			'≂' => '\ElsevierGlyph{2242}',
			'≂' => '\NotEqualTilde',
			'≃' => '\simeq',
			'≄' => '\not\simeq',
			'≅' => '\cong',
			'≆' => '\approxnotequal',
			'≇' => '\not\cong',
			'≈' => '\approx',
			'≉' => '\not\approx',
			'≊' => '\approxeq',
			'≋' => '\tildetrpl',
			'≋' => '\not\apid',
			'≌' => '\allequal',
			'≍' => '\asymp',
			'≎' => '\Bumpeq',
			'≎' => '\NotHumpDownHump',
			'≏' => '\bumpeq',
			'≏' => '\NotHumpEqual',
			'≐' => '\doteq',
			'≐' => '\not\doteq',
			'≑' => '\doteqdot',
			'≒' => '\fallingdotseq',
			'≓' => '\risingdotseq',
			'≔' => ':=',
			'≕' => '=:',
			'≖' => '\eqcirc',
			'≗' => '\circeq',
			'≙' => '\estimates',
			'≚' => '\ElsevierGlyph{225A}',
			'≛' => '\starequal',
			'≜' => '\triangleq',
			'≟' => '\ElsevierGlyph{225F}',
			'≠' => '\not =',
			'≡' => '\equiv',
			'≢' => '\not\equiv',
			'≤' => '\leq',
			'≥' => '\geq',
			'≦' => '\leqq',
			'≧' => '\geqq',
			'≨' => '\lneqq',
			'≨' => '\lvertneqq',
			'≩' => '\gneqq',
			'≩' => '\gvertneqq',
			'≪' => '\ll',
			'≪' => '\NotLessLess',
			'≫' => '\gg',
			'≫' => '\NotGreaterGreater',
			'≬' => '\between',
			'≭' => '\not\kern-0.3em\times',
			'≮' => '\not<',
			'≯' => '\not>',
			'≰' => '\not\leq',
			'≱' => '\not\geq',
			'≲' => '\lessequivlnt',
			'≳' => '\greaterequivlnt',
			'≴' => '\ElsevierGlyph{2274}',
			'≵' => '\ElsevierGlyph{2275}',
			'≶' => '\lessgtr',
			'≷' => '\gtrless',
			'≸' => '\notlessgreater',
			'≹' => '\notgreaterless',
			'≺' => '\prec',
			'≻' => '\succ',
			'≼' => '\preccurlyeq',
			'≽' => '\succcurlyeq',
			'≾' => '\precapprox',
			'≾' => '\NotPrecedesTilde',
			'≿' => '\succapprox',
			'≿' => '\NotSucceedsTilde',
			'⊀' => '\not\prec',
			'⊁' => '\not\succ',
			'⊂' => '\subset',
			'⊃' => '\supset',
			'⊄' => '\not\subset',
			'⊅' => '\not\supset',
			'⊆' => '\subseteq',
			'⊇' => '\supseteq',
			'⊈' => '\not\subseteq',
			'⊉' => '\not\supseteq',
			'⊊' => '\subsetneq',
			'⊊' => '\varsubsetneqq',
			'⊋' => '\supsetneq',
			'⊋' => '\varsupsetneq',
			'⊎' => '\uplus',
			'⊏' => '\sqsubset',
			'⊏' => '\NotSquareSubset',
			'⊐' => '\sqsupset',
			'⊐' => '\NotSquareSuperset',
			'⊑' => '\sqsubseteq',
			'⊒' => '\sqsupseteq',
			'⊓' => '\sqcap',
			'⊔' => '\sqcup',
			'⊕' => '\oplus',
			'⊖' => '\ominus',
			'⊗' => '\otimes',
			'⊘' => '\oslash',
			'⊙' => '\odot',
			'⊚' => '\circledcirc',
			'⊛' => '\circledast',
			'⊝' => '\circleddash',
			'⊞' => '\boxplus',
			'⊟' => '\boxminus',
			'⊠' => '\boxtimes',
			'⊡' => '\boxdot',
			'⊢' => '\vdash',
			'⊣' => '\dashv',
			'⊤' => '\top',
			'⊥' => '\perp',
			'⊧' => '\truestate',
			'⊨' => '\forcesextra',
			'⊩' => '\Vdash',
			'⊪' => '\Vvdash',
			'⊫' => '\VDash',
			'⊬' => '\nvdash',
			'⊭' => '\nvDash',
			'⊮' => '\nVdash',
			'⊯' => '\nVDash',
			'⊲' => '\vartriangleleft',
			'⊳' => '\vartriangleright',
			'⊴' => '\trianglelefteq',
			'⊵' => '\trianglerighteq',
			'⊶' => '\original',
			'⊷' => '\image',
			'⊸' => '\multimap',
			'⊹' => '\hermitconjmatrix',
			'⊺' => '\intercal',
			'⊻' => '\veebar',
			'⊾' => '\rightanglearc',
			'⋀' => '\ElsevierGlyph{22C0}',
			'⋁' => '\ElsevierGlyph{22C1}',
			'⋂' => '\bigcap',
			'⋃' => '\bigcup',
			'⋄' => '\diamond',
			'⋅' => '\cdot',
			'⋆' => '\star',
			'⋇' => '\divideontimes',
			'⋈' => '\bowtie',
			'⋉' => '\ltimes',
			'⋊' => '\rtimes',
			'⋋' => '\leftthreetimes',
			'⋌' => '\rightthreetimes',
			'⋍' => '\backsimeq',
			'⋎' => '\curlyvee',
			'⋏' => '\curlywedge',
			'⋐' => '\Subset',
			'⋑' => '\Supset',
			'⋒' => '\Cap',
			'⋓' => '\Cup',
			'⋔' => '\pitchfork',
			'⋖' => '\lessdot',
			'⋗' => '\gtrdot',
			'⋘' => '\verymuchless',
			'⋙' => '\verymuchgreater',
			'⋚' => '\lesseqgtr',
			'⋛' => '\gtreqless',
			'⋞' => '\curlyeqprec',
			'⋟' => '\curlyeqsucc',
			'⋢' => '\not\sqsubseteq',
			'⋣' => '\not\sqsupseteq',
			'⋥' => '\Elzsqspne',
			'⋦' => '\lnsim',
			'⋧' => '\gnsim',
			'⋨' => '\precedesnotsimilar',
			'⋩' => '\succnsim',
			'⋪' => '\ntriangleleft',
			'⋫' => '\ntriangleright',
			'⋬' => '\ntrianglelefteq',
			'⋭' => '\ntrianglerighteq',
			'⋮' => '\vdots',
			'⋯' => '\cdots',
			'⋰' => '\upslopeellipsis',
			'⋱' => '\downslopeellipsis',
			'①' => '\ding{172}',
			'②' => '\ding{173}',
			'③' => '\ding{174}',
			'④' => '\ding{175}',
			'⑤' => '\ding{176}',
			'⑥' => '\ding{177}',
			'⑦' => '\ding{178}',
			'⑧' => '\ding{179}',
			'⑨' => '\ding{180}',
			'⑩' => '\ding{181}',
			'■' => '\ding{110}',
			'□' => '\square',
			'▪' => '\blacksquare',
			'▲' => '\ding{115}',
			'▼' => '\ding{116}',
			'◆' => '\ding{117}',
			'●' => '\ding{108}',
			'◗' => '\ding{119}',
			'◘' => '\Elzrvbull',
			'★' => '\ding{72}',
			'☎' => '\ding{37}',
			'☛' => '\ding{42}',
			'☞' => '\ding{43}',
			'♀' => '\venus',
			'♂' => '\male',
			'♠' => '\ding{171}',
			'♣' => '\ding{168}',
			'♥' => '\ding{170}',
			'♦' => '\ding{169}',
			'♪' => '\eighthnote',
			'✁' => '\ding{33}',
			'✂' => '\ding{34}',
			'✃' => '\ding{35}',
			'✄' => '\ding{36}',
			'✆' => '\ding{38}',
			'✇' => '\ding{39}',
			'✈' => '\ding{40}',
			'✉' => '\ding{41}',
			'✌' => '\ding{44}',
			'✍' => '\ding{45}',
			'✎' => '\ding{46}',
			'✏' => '\ding{47}',
			'✐' => '\ding{48}',
			'✑' => '\ding{49}',
			'✒' => '\ding{50}',
			'✓' => '\ding{51}',
			'✔' => '\ding{52}',
			'✕' => '\ding{53}',
			'✖' => '\ding{54}',
			'✗' => '\ding{55}',
			'✘' => '\ding{56}',
			'✙' => '\ding{57}',
			'✚' => '\ding{58}',
			'✛' => '\ding{59}',
			'✜' => '\ding{60}',
			'✝' => '\ding{61}',
			'✞' => '\ding{62}',
			'✟' => '\ding{63}',
			'✠' => '\ding{64}',
			'✡' => '\ding{65}',
			'✢' => '\ding{66}',
			'✣' => '\ding{67}',
			'✤' => '\ding{68}',
			'✥' => '\ding{69}',
			'✦' => '\ding{70}',
			'✧' => '\ding{71}',
			'✩' => '\ding{73}',
			'✪' => '\ding{74}',
			'✫' => '\ding{75}',
			'✬' => '\ding{76}',
			'✭' => '\ding{77}',
			'✮' => '\ding{78}',
			'✯' => '\ding{79}',
			'✰' => '\ding{80}',
			'✱' => '\ding{81}',
			'✲' => '\ding{82}',
			'✳' => '\ding{83}',
			'✴' => '\ding{84}',
			'✵' => '\ding{85}',
			'✶' => '\ding{86}',
			'✷' => '\ding{87}',
			'✸' => '\ding{88}',
			'✹' => '\ding{89}',
			'✺' => '\ding{90}',
			'✻' => '\ding{91}',
			'✼' => '\ding{92}',
			'✽' => '\ding{93}',
			'✾' => '\ding{94}',
			'✿' => '\ding{95}',
			'❀' => '\ding{96}',
			'❁' => '\ding{97}',
			'❂' => '\ding{98}',
			'❃' => '\ding{99}',
			'❄' => '\ding{100}',
			'❅' => '\ding{101}',
			'❆' => '\ding{102}',
			'❇' => '\ding{103}',
			'❈' => '\ding{104}',
			'❉' => '\ding{105}',
			'❊' => '\ding{106}',
			'❋' => '\ding{107}',
			'❍' => '\ding{109}',
			'❏' => '\ding{111}',
			'❐' => '\ding{112}',
			'❑' => '\ding{113}',
			'❒' => '\ding{114}',
			'❖' => '\ding{118}',
			'❘' => '\ding{120}',
			'❙' => '\ding{121}',
			'❚' => '\ding{122}',
			'❛' => '\ding{123}',
			'❜' => '\ding{124}',
			'❝' => '\ding{125}',
			'❞' => '\ding{126}',
			'❡' => '\ding{161}',
			'❢' => '\ding{162}',
			'❣' => '\ding{163}',
			'❤' => '\ding{164}',
			'❥' => '\ding{165}',
			'❦' => '\ding{166}',
			'❧' => '\ding{167}',
			'❶' => '\ding{182}',
			'❷' => '\ding{183}',
			'❸' => '\ding{184}',
			'❹' => '\ding{185}',
			'❺' => '\ding{186}',
			'❻' => '\ding{187}',
			'❼' => '\ding{188}',
			'❽' => '\ding{189}',
			'❾' => '\ding{190}',
			'❿' => '\ding{191}',
			'➀' => '\ding{192}',
			'➁' => '\ding{193}',
			'➂' => '\ding{194}',
			'➃' => '\ding{195}',
			'➄' => '\ding{196}',
			'➅' => '\ding{197}',
			'➆' => '\ding{198}',
			'➇' => '\ding{199}',
			'➈' => '\ding{200}',
			'➉' => '\ding{201}',
			'➊' => '\ding{202}',
			'➋' => '\ding{203}',
			'➌' => '\ding{204}',
			'➍' => '\ding{205}',
			'➎' => '\ding{206}',
			'➏' => '\ding{207}',
			'➐' => '\ding{208}',
			'➑' => '\ding{209}',
			'➒' => '\ding{210}',
			'➓' => '\ding{211}',
			'➔' => '\ding{212}',
			'➘' => '\ding{216}',
			'➙' => '\ding{217}',
			'➚' => '\ding{218}',
			'➛' => '\ding{219}',
			'➜' => '\ding{220}',
			'➝' => '\ding{221}',
			'➞' => '\ding{222}',
			'➟' => '\ding{223}',
			'➠' => '\ding{224}',
			'➡' => '\ding{225}',
			'➢' => '\ding{226}',
			'➣' => '\ding{227}',
			'➤' => '\ding{228}',
			'➥' => '\ding{229}',
			'➦' => '\ding{230}',
			'➧' => '\ding{231}',
			'➨' => '\ding{232}',
			'➩' => '\ding{233}',
			'➪' => '\ding{234}',
			'➫' => '\ding{235}',
			'➬' => '\ding{236}',
			'➭' => '\ding{237}',
			'➮' => '\ding{238}',
			'➯' => '\ding{239}',
			'➱' => '\ding{241}',
			'➲' => '\ding{242}',
			'➳' => '\ding{243}',
			'➴' => '\ding{244}',
			'➵' => '\ding{245}',
			'➶' => '\ding{246}',
			'➷' => '\ding{247}',
			'➸' => '\ding{248}',
			'➹' => '\ding{249}',
			'➺' => '\ding{250}',
			'➻' => '\ding{251}',
			'➼' => '\ding{252}',
			'➽' => '\ding{253}',
			'➾' => '\ding{254}',
			'ﬀ' => 'ff',
			'ﬁ' => 'fi',
			'ﬂ' => 'fl',
			'ﬃ' => 'ffi',
			'ﬄ' => 'ffl'
		);

		$this->_delimiters = array(
			'"' => '"',
			'{' => '}'
		);

		$this->data = array();

		$this->content = '';

		//$this->_stripDelimiter = $stripDel;
		//$this->_validate       = $val;

		$this->warnings = array();

		$this->_options = array(
			'stripDelimiter' => true,
			'validate' => true,
			'unwrap' => false,
			'wordWrapWidth' => false,
			'wordWrapBreak' => "\n",
			'wordWrapCut' => 0,
			'removeCurlyBraces' => false,
			'extractAuthors' => true
		);

		foreach ($options as $option => $value) {
			$test = $this->setOption($option, $value);
			if ($test == false) {
				//Currently nothing is done here, but it could for example raise an warning
			}
		}

		$this->rtfstring = 'AUTHORS (YEAR): {\b TITLE}.';

		$this->htmlstring = 'AUTHORS, "<strong>TITLE</strong>", <em>JOURNAL</em>, YEAR<br />';

		$this->allowedEntryTypes = array(
			'article',
			'book',
			'booklet',
			'confernce',
			'inbook',
			'incollection',
			'inproceedings',
			'manual',
			'mastersthesis',
			'misc',
			'phdthesis',
			'proceedings',
			'techreport',
			'unpublished'
		);

		$this->authorstring = 'VON LAST, FIRST';
	}

	/**
	 * Sets run-time configuration options
	 *
	 * @access public
	 * @param string $option option name
	 * @param mixed  $value value for the option
	 * @return mixed true on success PEAR_Error on failure
	 */
	function setOption($option, $value) {
		$ret = true;
		if (array_key_exists($option, $this->_options)) {
			$this->_options[$option] = $value;
		} else {
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Reads a give BibTex File
	 *
	 * @access public
	 * @param string $filename Name of the file
	 * @return mixed true on success PEAR_Error on failure
	 */
	function loadContent ($content) {
		$this->content = $content;
		$this->_pos = 0;
		$this->_oldpos = 0;

		return true;
	}

	/**
	 * Parses what is stored in content and clears the content if the parsing is successfull.
	 *
	 * @access public
	 * @return boolean true on success and PEAR_Error if there was a problem
	 */
	function parse() {
		//The amount of opening braces is compared to the amount of closing braces
		//Braces inside comments are ignored
		$this->warnings = array();
		$this->data = array();
		$valid = true;
		$open = 0;
		$entry = false;
		$char = '';
		$lastchar = '';
		$buffer = '';
		for ($i = 0; $i < strlen($this->content); $i++) {
			$char = substr($this->content, $i, 1);
			if ((0 != $open) && ('@' == $char)) {
				if (!$this->_checkAt($buffer)) {
					$this->_generateWarning('WARNING_MISSING_END_BRACE', '', $buffer);
					//To correct the data we need to insert a closing brace
					$char = '}';
					$i--;
				}
			}
			if ((0 == $open) && ('@' == $char)) { //The beginning of an entry
				$entry = true;
			} elseif ($entry && ('{' == $char) && ('\\' != $lastchar)) { //Inside an entry and non quoted brace is opening
				$open++;
			} elseif ($entry && ('}' == $char) && ('\\' != $lastchar)) { //Inside an entry and non quoted brace is closing
				$open--;
				if ($open < 0) { //More are closed than opened
					$valid = false;
				}
				if (0 == $open) { //End of entry
					$entry = false;
					$entrydata = $this->_parseEntry($buffer);
					if (!$entrydata) {
						/**
						 * This is not yet used.
						 * We are here if the Entry is either not correct or not supported.
						 * But this should already generate a warning.
						 * Therefore it should not be necessary to do anything here
						 */
					} else {
						$this->data[] = $entrydata;
					}
					$buffer = '';
				}
			}
			if ($entry) { //Inside entry
				$buffer .= $char;
			}
			$lastchar = $char;
		}
		//If open is one it may be possible that the last ending brace is missing
		if (1 == $open) {
			$entrydata = $this->_parseEntry($buffer);
			if (!$entrydata) {
				$valid = false;
			} else {
				$this->data[] = $entrydata;
				$buffer = '';
				$open = 0;
			}
		}
		//At this point the open should be zero
		if (0 != $open) {
			$valid = false;
		}
		//Are there Multiple entries with the same cite?
		if ($this->_options['validate']) {
			$cites = array();
			foreach ($this->data as $entry) {
				$cites[] = $entry['cite'];
			}
			$unique = array_unique($cites);
			if (sizeof($cites) != sizeof($unique)) { //Some values have not been unique!
				$notuniques = array();
				for ($i = 0; $i < sizeof($cites); $i++) {
					if ('' == $unique[$i]) {
						$notuniques[] = $cites[$i];
					}
				}
				$this->_generateWarning('WARNING_MULTIPLE_ENTRIES', implode(',', $notuniques));
			}
		}
		if ($valid) {
			$this->content = '';
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Extracting the data of one content
	 *
	 * The parse function splits the content into its entries.
	 * Then every entry is parsed by this function.
	 * It parses the entry backwards.
	 * First the last '=' is searched and the value extracted from that.
	 * A copy is made of the entry if warnings should be generated. This takes quite
	 * some memory but it is needed to get good warnings. If nor warnings are generated
	 * then you don have to worry about memory.
	 * Then the last ',' is searched and the field extracted from that.
	 * Again the entry is shortened.
	 * Finally after all field=>value pairs the cite and type is extraced and the
	 * authors are splitted.
	 * If there is a problem false is returned.
	 *
	 * @access private
	 * @param string $entry The entry
	 * @return array The representation of the entry or false if there is a problem
	 */
	function _parseEntry($entry) {
		global $bibliographie_bibtex_escaped_chars;
		$entrycopy = '';
		if ($this->_options['validate']) {
			$entrycopy = $entry; //We need a copy for printing the warnings
		}
		$ret = array();
		if ('@string' == strtolower(substr($entry, 0, 7))) {
			//String are not yet supported!
			if ($this->_options['validate']) {
				$this->_generateWarning('STRING_ENTRY_NOT_YET_SUPPORTED', '', $entry . '}');
			}
		} elseif ('@preamble' == strtolower(substr($entry, 0, 9))) {
			//Preamble not yet supported!
			if ($this->_options['validate']) {
				$this->_generateWarning('PREAMBLE_ENTRY_NOT_YET_SUPPORTED', '', $entry . '}');
			}
		} else {
			//Parsing all fields
			while (strrpos($entry, '=') !== false) {
				$position = strrpos($entry, '=');
				//Checking that the equal sign is not quoted or is not inside a equation (For example in an abstract)
				$proceed = true;
				if (substr($entry, $position - 1, 1) == '\\') {
					$proceed = false;
				}
				if ($proceed) {
					$proceed = $this->_checkEqualSign($entry, $position);
				}
				while (!$proceed) {
					$substring = substr($entry, 0, $position);
					$position = strrpos($substring, '=');
					$proceed = true;
					if (substr($entry, $position - 1, 1) == '\\') {
						$proceed = false;
					}
					if ($proceed) {
						$proceed = $this->_checkEqualSign($entry, $position);
					}
				}

				$value = trim(substr($entry, $position + 1));
				$entry = substr($entry, 0, $position);

				if (',' == substr($value, strlen($value) - 1, 1)) {
					$value = substr($value, 0, -1);
				}
				if ($this->_options['validate']) {
					$this->_validateValue($value, $entrycopy);
				}
				if ($this->_options['stripDelimiter']) {
					$value = $this->_stripDelimiter($value);
				}
				if ($this->_options['unwrap']) {
					$value = $this->_unwrap($value);
				}
				if ($this->_options['removeCurlyBraces']) {
					$value = $this->_removeCurlyBraces($value);
				}

				$position = strrpos($entry, ',');
				$field = strtolower(trim(substr($entry, $position + 1)));

				if($field != 'author' and $field != 'editor')
					$value = str_replace(array_values($this->escapedChars), array_keys($this->escapedChars), $value);

				$ret[$field] = $value;
				$entry = substr($entry, 0, $position);
			}
			//Parsing cite and entry type
			$arr = explode('{', $entry);
			$ret['cite'] = trim($arr[1]);
			$ret['entryType'] = strtolower(trim($arr[0]));
			if ('@' == $ret['entryType']{0}) {
				$ret['entryType'] = substr($ret['entryType'], 1);
			}
			if ($this->_options['validate']) {
				if (!$this->_checkAllowedEntryType($ret['entryType'])) {
					$this->_generateWarning('WARNING_NOT_ALLOWED_ENTRY_TYPE', $ret['entryType'], $entry . '}');
				}
			}
			$ret['entryType'] = mb_strtoupper(mb_substr($ret['entryType'], 0, 1)).mb_substr($ret['entryType'], 1);
			//Handling the authors
			if (in_array('author', array_keys($ret)) && $this->_options['extractAuthors']) {
				$ret['author'] = $this->_extractAuthors($ret['author']);
			}

			if (in_array('editor', array_keys($ret)) && $this->_options['extractAuthors']) {
				$ret['editor'] = $this->_extractAuthors($ret['editor']);
			}
		}
		return $ret;
	}

	/**
	 * Checking whether the position of the '=' is correct
	 *
	 * Sometimes there is a problem if a '=' is used inside an entry (for example abstract).
	 * This method checks if the '=' is outside braces then the '=' is correct and true is returned.
	 * If the '=' is inside braces it contains to a equation and therefore false is returned.
	 *
	 * @access private
	 * @param string $entry The text of the whole remaining entry
	 * @param int the current used place of the '='
	 * @return bool true if the '=' is correct, false if it contains to an equation
	 */
	function _checkEqualSign($entry, $position) {
		$ret = true;
		//This is getting tricky
		//We check the string backwards until the position and count the closing an opening braces
		//If we reach the position the amount of opening and closing braces should be equal
		$length = strlen($entry);
		$open = 0;
		for ($i = $length - 1; $i >= $position; $i--) {
			$precedingchar = substr($entry, $i - 1, 1);
			$char = substr($entry, $i, 1);
			if (('{' == $char) && ('\\' != $precedingchar)) {
				$open++;
			}
			if (('}' == $char) && ('\\' != $precedingchar)) {
				$open--;
			}
		}
		if (0 != $open) {
			$ret = false;
		}
		//There is still the posibility that the entry is delimited by double quotes.
		//Then it is possible that the braces are equal even if the '=' is in an equation.
		if ($ret) {
			$entrycopy = trim($entry);
			$lastchar = $entrycopy{strlen($entrycopy) - 1};
			if (',' == $lastchar) {
				$lastchar = $entrycopy{strlen($entrycopy) - 2};
			}
			if ('"' == $lastchar) {
				//The return value is set to false
				//If we find the closing " before the '=' it is set to true again.
				//Remember we begin to search the entry backwards so the " has to show up twice - ending and beginning delimiter
				$ret = false;
				$found = 0;
				for ($i = $length; $i >= $position; $i--) {
					$precedingchar = substr($entry, $i - 1, 1);
					$char = substr($entry, $i, 1);
					if (('"' == $char) && ('\\' != $precedingchar)) {
						$found++;
					}
					if (2 == $found) {
						$ret = true;
						break;
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * Checking if the entry type is allowed
	 *
	 * @access private
	 * @param string $entry The entry to check
	 * @return bool true if allowed, false otherwise
	 */
	function _checkAllowedEntryType($entry) {
		return in_array($entry, $this->allowedEntryTypes);
	}

	/**
	 * Checking whether an at is outside an entry
	 *
	 * Sometimes an entry misses an entry brace. Then the at of the next entry seems to be
	 * inside an entry. This is checked here. When it is most likely that the at is an opening
	 * at of the next entry this method returns true.
	 *
	 * @access private
	 * @param string $entry The text of the entry until the at
	 * @return bool true if the at is correct, false if the at is likely to begin the next entry.
	 */
	function _checkAt($entry) {
		$ret = false;
		$opening = array_keys($this->_delimiters);
		$closing = array_values($this->_delimiters);
		//Getting the value (at is only allowd in values)
		if (strrpos($entry, '=') !== false) {
			$position = strrpos($entry, '=');
			$proceed = true;
			if (substr($entry, $position - 1, 1) == '\\') {
				$proceed = false;
			}
			while (!$proceed) {
				$substring = substr($entry, 0, $position);
				$position = strrpos($substring, '=');
				$proceed = true;
				if (substr($entry, $position - 1, 1) == '\\') {
					$proceed = false;
				}
			}
			$value = trim(substr($entry, $position + 1));
			$open = 0;
			$char = '';
			$lastchar = '';
			for ($i = 0; $i < strlen($value); $i++) {
				$char = substr($this->content, $i, 1);
				if (in_array($char, $opening) && ('\\' != $lastchar)) {
					$open++;
				} elseif (in_array($char, $closing) && ('\\' != $lastchar)) {
					$open--;
				}
				$lastchar = $char;
			}
			//if open is grater zero were are inside an entry
			if ($open > 0) {
				$ret = true;
			}
		}
		return $ret;
	}

	/**
	 * Stripping Delimiter
	 *
	 * @access private
	 * @param string $entry The entry where the Delimiter should be stripped from
	 * @return string Stripped entry
	 */
	function _stripDelimiter($entry) {
		$beginningdels = array_keys($this->_delimiters);
		$length = strlen($entry);
		$firstchar = substr($entry, 0, 1);
		$lastchar = substr($entry, -1, 1);
		while (in_array($firstchar, $beginningdels)) { //The first character is an opening delimiter
			if ($lastchar == $this->_delimiters[$firstchar]) { //Matches to closing Delimiter
				$entry = substr($entry, 1, -1);
			} else {
				break;
			}
			$firstchar = substr($entry, 0, 1);
			$lastchar = substr($entry, -1, 1);
		}
		return $entry;
	}

	/**
	 * Unwrapping entry
	 *
	 * @access private
	 * @param string $entry The entry to unwrap
	 * @return string unwrapped entry
	 */
	function _unwrap($entry) {
		$entry = preg_replace('/\s+/', ' ', $entry);
		return trim($entry);
	}

	/**
	 * Wordwrap an entry
	 *
	 * @access private
	 * @param string $entry The entry to wrap
	 * @return string wrapped entry
	 */
	function _wordwrap($entry) {
		if (('' != $entry) && (is_string($entry))) {
			$entry = wordwrap($entry, $this->_options['wordWrapWidth'], $this->_options['wordWrapBreak'], $this->_options['wordWrapCut']);
		}
		return $entry;
	}

	/**
	 * Extracting the authors
	 *
	 * @access private
	 * @param string $entry The entry with the authors
	 * @return array the extracted authors
	 */
	function _extractAuthors($entry) {
		$entry = $this->_unwrap($entry);
		$authorarray = array();
		$authorarray = explode(' and ', $entry);
		for ($i = 0; $i < sizeof($authorarray); $i++) {
			$author = trim($authorarray[$i]);
			/* The first version of how an author could be written (First von Last)
			  has no commas in it */
			$first = '';
			$von = '';
			$last = '';
			$jr = '';
			if (strpos($author, ',') === false) {
				$tmparray = array();
				//$tmparray = explode(' ', $author);
				$tmparray = explode(' |~', $author);
				$size = sizeof($tmparray);
				if (1 == $size) { //There is only a last
					$last = $tmparray[0];
				} elseif (2 == $size) { //There is a first and a last
					$first = $tmparray[0];
					$last = $tmparray[1];
				} else {
					$invon = false;
					$inlast = false;
					for ($j = 0; $j < ($size - 1); $j++) {
						if ($inlast) {
							$last .= ' ' . $tmparray[$j];
						} elseif ($invon) {
							$case = $this->_determineCase($tmparray[$j]);
							if ($case == false) {
								// IGNORE?
							} elseif ((0 == $case) || (-1 == $case)) { //Change from von to last
								//You only change when there is no more lower case there
								$islast = true;
								for ($k = ($j + 1); $k < ($size - 1); $k++) {
									$futurecase = $this->_determineCase($tmparray[$k]);
									if ($case == false) {
										// IGNORE?
									} elseif (0 == $futurecase) {
										$islast = false;
									}
								}
								if ($islast) {
									$inlast = true;
									if (-1 == $case) { //Caseless belongs to the last
										$last .= ' ' . $tmparray[$j];
									} else {
										$von .= ' ' . $tmparray[$j];
									}
								} else {
									$von .= ' ' . $tmparray[$j];
								}
							} else {
								$von .= ' ' . $tmparray[$j];
							}
						} else {
							$case = $this->_determineCase($tmparray[$j]);
							if ($case == false) {
								// IGNORE?
							} elseif (0 == $case) { //Change from first to von
								$invon = true;
								$von .= ' ' . $tmparray[$j];
							} else {
								$first .= ' ' . $tmparray[$j];
							}
						}
					}
					//The last entry is always the last!
					$last .= ' ' . $tmparray[$size - 1];
				}
			} else { //Version 2 and 3
				$tmparray = array();
				$tmparray = explode(',', $author);
				//The first entry must contain von and last
				$vonlastarray = array();
				$vonlastarray = explode(' ', $tmparray[0]);
				$size = sizeof($vonlastarray);
				if (1 == $size) { //Only one entry->got to be the last
					$last = $vonlastarray[0];
				} else {
					$inlast = false;
					for ($j = 0; $j < ($size - 1); $j++) {
						if ($inlast) {
							$last .= ' ' . $vonlastarray[$j];
						} else {
							if (0 != ($this->_determineCase($vonlastarray[$j]))) { //Change from von to last
								$islast = true;
								for ($k = ($j + 1); $k < ($size - 1); $k++) {
									$this->_determineCase($vonlastarray[$k]);
									$case = $this->_determineCase($vonlastarray[$k]);
									if ($case == false) {
										// IGNORE?
									} elseif (0 == $case) {
										$islast = false;
									}
								}
								if ($islast) {
									$inlast = true;
									$last .= ' ' . $vonlastarray[$j];
								} else {
									$von .= ' ' . $vonlastarray[$j];
								}
							} else {
								$von .= ' ' . $vonlastarray[$j];
							}
						}
					}
					$last .= ' ' . $vonlastarray[$size - 1];
				}
				//Now we check if it is version three (three entries in the array (two commas)
				if (3 == sizeof($tmparray)) {
					$jr = $tmparray[1];
				}
				//Everything in the last entry is first
				$first = $tmparray[sizeof($tmparray) - 1];
			}
			$authorarray[$i] = array('first' => trim($first), 'von' => trim($von), 'last' => trim($last), 'jr' => trim($jr));
		}
		return $authorarray;
	}

	/**
	 * Case Determination according to the needs of BibTex
	 *
	 * To parse the Author(s) correctly a determination is needed
	 * to get the Case of a word. There are three possible values:
	 * - Upper Case (return value 1)
	 * - Lower Case (return value 0)
	 * - Caseless   (return value -1)
	 *
	 * @access private
	 * @param string $word
	 * @return int The Case or PEAR_Error if there was a problem
	 */
	function _determineCase($word) {
		$ret = -1;
		$trimmedword = trim($word);
		/* We need this variable. Without the next of would not work
		  (trim changes the variable automatically to a string!) */
		if (is_string($word) && (strlen($trimmedword) > 0)) {
			$i = 0;
			$found = false;
			$openbrace = 0;
			while (!$found && ($i <= strlen($word))) {
				$letter = substr($trimmedword, $i, 1);
				$ord = ord($letter);
				if ($ord == 123) { //Open brace
					$openbrace++;
				}
				if ($ord == 125) { //Closing brace
					$openbrace--;
				}
				if (($ord >= 65) && ($ord <= 90) && (0 == $openbrace)) { //The first character is uppercase
					$ret = 1;
					$found = true;
				} elseif (($ord >= 97) && ($ord <= 122) && (0 == $openbrace)) { //The first character is lowercase
					$ret = 0;
					$found = true;
				} else { //Not yet found
					$i++;
				}
			}
		} else {
			$ret = false;
		}
		return $ret;
	}

	/**
	 * Validation of a value
	 *
	 * There may be several problems with the value of a field.
	 * These problems exist but do not break the parsing.
	 * If a problem is detected a warning is appended to the array warnings.
	 *
	 * @access private
	 * @param string $entry The entry aka one line which which should be validated
	 * @param string $wholeentry The whole BibTex Entry which the one line is part of
	 * @return void
	 */
	function _validateValue($entry, $wholeentry) {
		//There is no @ allowed if the entry is enclosed by braces
		if (preg_match('/^{.*@.*}$/', $entry)) {
			$this->_generateWarning('WARNING_AT_IN_BRACES', $entry, $wholeentry);
		}
		//No escaped " allowed if the entry is enclosed by double quotes
		if (preg_match('/^\".*\\".*\"$/', $entry)) {
			$this->_generateWarning('WARNING_ESCAPED_DOUBLE_QUOTE_INSIDE_DOUBLE_QUOTES', $entry, $wholeentry);
		}
		//Amount of Braces is not correct
		$open = 0;
		$lastchar = '';
		$char = '';
		for ($i = 0; $i < strlen($entry); $i++) {
			$char = substr($entry, $i, 1);
			if (('{' == $char) && ('\\' != $lastchar)) {
				$open++;
			}
			if (('}' == $char) && ('\\' != $lastchar)) {
				$open--;
			}
			$lastchar = $char;
		}
		if (0 != $open) {
			$this->_generateWarning('WARNING_UNBALANCED_AMOUNT_OF_BRACES', $entry, $wholeentry);
		}
	}

	/**
	 * Remove curly braces from entry
	 *
	 * @access private
	 * @param string $value The value in which curly braces to be removed
	 * @param string Value with removed curly braces
	 */
	function _removeCurlyBraces($value) {
		//First we save the delimiters
		$beginningdels = array_keys($this->_delimiters);
		$firstchar = substr($entry, 0, 1);
		$lastchar = substr($entry, -1, 1);
		$begin = '';
		$end = '';
		while (in_array($firstchar, $beginningdels)) { //The first character is an opening delimiter
			if ($lastchar == $this->_delimiters[$firstchar]) { //Matches to closing Delimiter
				$begin .= $firstchar;
				$end .= $lastchar;
				$value = substr($value, 1, -1);
			} else {
				break;
			}
			$firstchar = substr($value, 0, 1);
			$lastchar = substr($value, -1, 1);
		}
		//Now we get rid of the curly braces
		$pattern = '/([^\\\\])\{(.*?[^\\\\])\}/';
		$replacement = '$1$2';
		$value = preg_replace($pattern, $replacement, $value);
		//Reattach delimiters
		$value = $begin . $value . $end;
		return $value;
	}

	/**
	 * Generates a warning
	 *
	 * @access private
	 * @param string $type The type of the warning
	 * @param string $entry The line of the entry where the warning occurred
	 * @param string $wholeentry OPTIONAL The whole entry where the warning occurred
	 */
	function _generateWarning($type, $entry, $wholeentry='') {
		$warning['warning'] = $type;
		$warning['entry'] = $entry;
		$warning['wholeentry'] = $wholeentry;
		$this->warnings[] = $warning;
	}

	/**
	 * Cleares all warnings
	 *
	 * @access public
	 */
	function clearWarnings() {
		$this->warnings = array();
	}

	/**
	 * Is there a warning?
	 *
	 * @access public
	 * @return true if there is, false otherwise
	 */
	function hasWarning() {
		if (sizeof($this->warnings) > 0)
			return true;
		else
			return false;
	}

	/**
	 * Returns the amount of available BibTex entries
	 *
	 * @access public
	 * @return int The amount of available BibTex entries
	 */
	function amount() {
		return sizeof($this->data);
	}

	/**
	 * Returns the author formatted
	 *
	 * The Author is formatted as setted in the authorstring
	 *
	 * @access private
	 * @param array $array Author array
	 * @return string the formatted author string
	 */
	function _formatAuthor($array) {
		if (!array_key_exists('von', $array)) {
			$array['von'] = '';
		} else {
			$array['von'] = trim($array['von']);
		}
		if (!array_key_exists('last', $array)) {
			$array['last'] = '';
		} else {
			$array['last'] = trim($array['last']);
		}
		if (!array_key_exists('jr', $array)) {
			$array['jr'] = '';
		} else {
			$array['jr'] = trim($array['jr']);
		}
		if (!array_key_exists('first', $array)) {
			$array['first'] = '';
		} else {
			$array['first'] = trim($array['first']);
		}
		$ret = $this->authorstring;
		$ret = str_replace("VON", $array['von'], $ret);
		$ret = str_replace("LAST", $array['last'], $ret);
		$ret = str_replace("JR", $array['jr'], $ret);
		$ret = str_replace("FIRST", $array['first'], $ret);
		return trim($ret);
	}

	/**
	 * Converts the stored BibTex entries to a BibTex String
	 *
	 * In the field list, the author is the last field.
	 *
	 * @access public
	 * @return string The BibTex string
	 */
	function bibTex() {
		$bibtex = '';
		foreach ($this->data as $entry) {
			//Intro
			$bibtex .= '@' . strtolower($entry['entryType']) . ' { ' . $entry['cite'] . ",\n";
			//Other fields except author
			foreach ($entry as $key => $val) {
				if ($this->_options['wordWrapWidth'] > 0) {
					$val = $this->_wordWrap($val);
				}
				if (!in_array($key, array('cite', 'entryType', 'author', 'editor'))) {
					if($key != 'url')
						$val = str_replace(array_keys($this->escapedChars), array_values($this->escapedChars), $val);
					if($key == 'pages')
						$val = str_replace('-', '--', $val);

					$bibtex .= "\t" . $key . ' = {' . $val . "},\n";
				}
			}
			//Author
			$author = '';
			if (array_key_exists('author', $entry)) {
				if ($this->_options['extractAuthors']) {
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry) {
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$author = join(' and ', $tmparray);
				} else {
					$author = $entry['author'];
				}
			}

			$editor = '';
			if (array_key_exists('editor', $entry)) {
				if ($this->_options['extractAuthors']) {
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['editor'] as $authorentry) {
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$editor = join(' and ', $tmparray);
				} else {
					$editor = $entry['editor'];
				}
			}

			if(!empty($author))
				$bibtex .= "\tauthor = {" . str_replace(array_keys($this->escapedChars), array_values($this->escapedChars), $author) . "}";

			if(!empty($editor))
				$bibtex .= ",\n\teditor = {" . str_replace(array_keys($this->escapedChars), array_values($this->escapedChars), $editor) . "}";
			$bibtex.="\n}\n\n";
		}
		return $bibtex;
	}

	/**
	 * Adds a new BibTex entry to the data
	 *
	 * @access public
	 * @param array $newentry The new data to add
	 * @return void
	 */
	function addEntry($newentry) {
		$this->data[] = $newentry;
	}

	/**
	 * Returns statistic
	 *
	 * This functions returns a hash table. The keys are the different
	 * entry types and the values are the amount of these entries.
	 *
	 * @access public
	 * @return array Hash Table with the data
	 */
	function getStatistic() {
		$ret = array();
		foreach ($this->data as $entry) {
			if (array_key_exists($entry['entryType'], $ret)) {
				$ret[$entry['entryType']]++;
			} else {
				$ret[$entry['entryType']] = 1;
			}
		}
		return $ret;
	}

	/**
	 * Returns the stored data in RTF format
	 *
	 * This method simply returns a RTF formatted string. This is done very
	 * simple and is not intended for heavy using and fine formatting. This
	 * should be done by BibTex! It is intended to give some kind of quick
	 * preview or to send someone a reference list as word/rtf format (even
	 * some people in the scientific field still use word). If you want to
	 * change the default format you have to override the class variable
	 * "rtfstring". This variable is used and the placeholders simply replaced.
	 * Lines with no data cause an warning!
	 *
	 * @return string the RTF Strings
	 */
	function rtf() {
		$ret = "{\\rtf\n";
		foreach ($this->data as $entry) {
			$line = $this->rtfstring;
			$title = '';
			$journal = '';
			$year = '';
			$authors = '';
			if (array_key_exists('title', $entry)) {
				$title = $this->_unwrap($entry['title']);
			}
			if (array_key_exists('journal', $entry)) {
				$journal = $this->_unwrap($entry['journal']);
			}
			if (array_key_exists('year', $entry)) {
				$year = $this->_unwrap($entry['year']);
			}
			if (array_key_exists('author', $entry)) {
				if ($this->_options['extractAuthors']) {
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry) {
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$authors = join(' & ', $tmparray);
				} else {
					$authors = $entry['author'];
				}
			}
			if (('' != $title) || ('' != $journal) || ('' != $year) || ('' != $authors)) {
				$line = str_replace("TITLE", $title, $line);
				$line = str_replace("JOURNAL", $journal, $line);
				$line = str_replace("YEAR", $year, $line);
				$line = str_replace("AUTHORS", $authors, $line);
				$line .= "\n\\par\n\n\\par\n";
				$ret .= $line;
			} else {
				$this->_generateWarning('WARNING_LINE_WAS_NOT_CONVERTED', '', print_r($entry, 1));
			}
		}
		$ret .= '}';
		return $ret;
	}

	/**
	 * Returns the stored data in HTML format
	 *
	 * This method simply returns a HTML formatted string. This is done very
	 * simple and is not intended for heavy using and fine formatting. This
	 * should be done by BibTex! It is intended to give some kind of quick
	 * preview. If you want to change the default format you have to override
	 * the class variable "htmlstring". This variable is used and the placeholders
	 * simply replaced.
	 * Lines with no data cause an warning!
	 *
	 * @return string the HTML Strings
	 */
	function html() {
		$ret = "<p>\n";
		foreach ($this->data as $entry) {
			$line = $this->htmlstring;
			$title = '';
			$journal = '';
			$year = '';
			$authors = '';
			if (array_key_exists('title', $entry)) {
				$title = $this->_unwrap($entry['title']);
			}
			if (array_key_exists('journal', $entry)) {
				$journal = $this->_unwrap($entry['journal']);
			}
			if (array_key_exists('year', $entry)) {
				$year = $this->_unwrap($entry['year']);
			}
			if (array_key_exists('author', $entry)) {
				if ($this->_options['extractAuthors']) {
					$tmparray = array(); //In this array the authors are saved and the joind with an and
					foreach ($entry['author'] as $authorentry) {
						$tmparray[] = $this->_formatAuthor($authorentry);
					}
					$authors = join(', ', $tmparray);
				} else {
					$authors = $entry['author'];
				}
			}
			if (('' != $title) || ('' != $journal) || ('' != $year) || ('' != $authors)) {
				$line = str_replace("TITLE", $title, $line);
				$line = str_replace("JOURNAL", $journal, $line);
				$line = str_replace("YEAR", $year, $line);
				$line = str_replace("AUTHORS", $authors, $line);
				$line .= "\n";
				$ret .= $line;
			} else {
				$this->_generateWarning('WARNING_LINE_WAS_NOT_CONVERTED', '', print_r($entry, 1));
			}
		}
		$ret .= "</p>\n";
		return $ret;
	}
}