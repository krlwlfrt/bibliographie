<?php
$bibliographie_charmap_chars = array (
	'upper' => array (
		'a' => array ('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ā', 'Ă', 'Ą', 'Ǎ', 'Ǻ', 'Ǽ'),
		'c' => array ('Ç', 'Ć', 'Ĉ', 'Ċ', 'Č'),
		'd' => array('Ð', 'Ď', 'Đ'),
		'e' => array('È', 'É', 'Ê', 'Ë', 'Ē', 'Ĕ', 'Ė', 'Ę', 'Ě'),
		'g' => array('Ĝ', 'Ğ', 'Ġ', 'Ģ'),
		'h' => array('Ĥ', 'Ħ'),
		'i' => array('Ì', 'Í', 'Î', 'Ï', 'Ĩ', 'Ī', 'Ĭ', 'Į', 'İ', 'Ĳ', 'Ǐ'),
		'j' => array('Ĵ'),
		'k' => array('Ķ'),
		'l' => array('Ĺ', 'Ļ', 'Ľ', 'Ŀ', 'Ł'),
		'n' => array('Ñ', 'Ń', 'Ņ', 'Ň', 'Ŋ'),
		'o' => array('Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ō', 'Ŏ', 'Ő', 'Œ', 'Ǒ', 'Ǿ'),
		'r' => array('Ŕ', 'Ŗ', 'Ř'),
		's' => array('Ś', 'Ŝ', 'Ş', 'Š', 'Ș'),
		't' => array('Ţ', 'Ť', 'Ŧ', 'Ț'),
		'u' => array('Ù', 'Ú', 'Û', 'Ü', 'Ũ', 'Ū', 'Ŭ', 'Ů', 'Ű', 'Ų', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ'),
		'w' => array('Ŵ'),
		'y' => array('Ý', 'Ŷ', 'Ÿ'),
		'z' => array('Ź', 'Ż', 'Ž'),
		'misc' => array('ß', 'Þ', 'þ')
	),
	'lower' => array (
		'a' => array ('à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ā', 'ă', 'ą', 'ǎ', 'ǻ', 'ǽ'),
		'c' => array('ç', 'ć', 'ĉ', 'ċ', 'č'),
		'd' => array('ð', 'ď', 'đ'),
		'e' => array('è', 'é', 'ê', 'ë', 'ē', 'ĕ', 'ė', 'ę', 'ě'),
		'g' => array('ĝ', 'ğ', 'ġ', 'ģ'),
		'h' => array('ĥ', 'ħ'),
		'i' => array('ì', 'í', 'î', 'ï', 'ĩ', 'ī', 'ĭ', 'į', 'ı', 'ĳ', 'ſ', 'ǐ'),
		'j' => array('ĵ'),
		'k' => array('ķ', 'ĸ'),
		'l' => array('ĺ', 'ļ', 'ľ', 'ŀ', 'ł'),
		'n' => array('ñ', 'ń', 'ņ', 'ň', 'ŉ', 'ŋ'),
		'o' => array('ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ō', 'ŏ', 'ő', 'œ', 'ǒ', 'ǿ'),
		'r' => array('ŕ', 'ŗ', 'ř'),
		's' => array('ś', 'ŝ', 'ş', 'š', 'ș'),
		't' => array('ţ', 'ť', 'ŧ', 'ț'),
		'u' => array('ù', 'ú', 'û', 'ü', 'ũ', 'ū', 'ŭ', 'ů', 'ű', 'ų', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ'),
		'w' => array('ŵ'),
		'y' => array('ý', 'ÿ', 'ŷ'),
		'z' => array('ź', 'ż', 'ž')
	)
);

function bibliographie_charmap_print_charmap () {
	global $bibliographie_charmap_chars;
?>

<div id="bibliographie_charmap">
	<div id="bibliographie_charmap_header">
		<a id="bibliographie_charmap_closer" href="javascript:;" onclick="$('#bibliographie_charmap').hide('clip');">close</a>
		<strong>Charmap</strong>
		<a href="javascript:;" onclick="$('#bibliographie_charmap_upper_substitutes').hide(); $('#bibliographie_charmap_lower_substitutes').show()">lower</a> /
		<a href="javascript:;" onclick="$('#bibliographie_charmap_lower_substitutes').hide(); $('#bibliographie_charmap_upper_substitutes').show()">upper</a> case
	</div>

	<table id="bibliographie_charmap_structure"><tr><td>
<?php
	foreach($bibliographie_charmap_chars as $case => $substitutes){
		$i = (int) 0;
		echo '<div id="bibliographie_charmap_'.$case.'_substitutes" class="charmap_table">';
		foreach($substitutes as $latinChar => $substituteChars){
			foreach($substituteChars as $substituteChar){
				$substituteChar = $substituteChar;
				echo '<a href="javascript:;" onclick="bibliographie_charmap_insert_char(\''.$substituteChar.'\');" onmouseover="$(\'#bibliographie_charmap_magnifier\').html(\''.$substituteChar.'\')" class="bibliographie_charmap_substitute">'.$substituteChar.'</a>';
				if(++$i % 20 == 0){
					echo '<br />';
				}
			}
		}
		do { echo '<a href="javascript:;" class="bibliographie_charmap_substitute">&nbsp;</a>'; } while (++$i % 20 != 0);
		echo '</div>';
	}
?>
			</td><td id="bibliographie_charmap_magnifier"></td></tr>
	</table>
	<em>You don't have to close me. You can push me away with your mouse!</em>
</div>
<?php
}