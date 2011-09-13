var jquery_charmap_chars = {
	'a': {'upper': Array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', '\u0100', '\u0102', '\u0104', '\u01cd', '\u01fa', '\u01fc'), 'lower': Array('à', 'á', 'â', 'ã', 'ä', 'å', 'æ', '\u0101', '\u0103', '\u0105', '\u01ce', '\u01fb', '\u01fd')},
	'c': {'upper': Array('Ç', '\u0106', '\u0108', '\u010a', '\u010c'), 'lower': Array('ç', '\u0107', '\u0109', '\u010b', '\u010d')},
	'd': {'upper': Array('Ð', '\u010e', '\u0110'), 'lower': Array('ð', '\u010f', '\u0111')},
	'e': {'upper': Array('È', 'É', 'Ê', 'Ë', '\u0112', '\u0114', '\u0116', '\u0118', '\u011a'), 'lower': Array('è', 'é', 'ê', 'ë', '\u0113', '\u0115', '\u0117', '\u0119', '\u011b')},
	'g': {'upper': Array('\u011c', '\u011e', '\u0120', '\u0122'), 'lower': Array('\u011d', '\u011f', '\u0121', '\u0123')},
	'h': {'upper': Array('\u0124', '\u0126'), 'lower': Array('\u0125', '\u0127')},
	'i': {'upper': Array('Ì', 'Í', 'Î', 'Ï', '\u0128', '\u012a', '\u012c', '\u012e', '\u0130', '\u0132', '\u01cf'), 'lower': Array('ì', 'í', 'î', 'ï', '\u0129', '\u012b', '\u012d', '\u012f', '\u0131', '\u0133', '\u017f', '\u01d0')},
	'j': {'upper': Array('\u0134'), 'lower': Array('\u0135')},
	'k': {'upper': Array('\u0136'), 'lower': Array('\u0137', '\u0138')},
	'l': {'upper': Array('\u0139', '\u013b', '\u013d', '\u013f', '\u0141'), 'lower': Array('\u013a', '\u013c', '\u013e', '\u0140', '\u0142')},
	'n': {'upper': Array('Ñ', '\u0143', '\u0145', '\u0147', '\u014a'), 'lower': Array('ñ', '\u0144', '\u0146', '\u0148', '\u0149', '\u014b')},
	'o': {'upper': Array('Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', '\u014c', '\u014e', '\u0150', '\u0152', '\u01d1', '\u01fe'), 'lower': Array('ò', 'ó', 'ô', 'õ', 'ö', 'ø', '\u014d', '\u014f', '\u0151', '\u0153', '\u01d2', '\u01ff')},
	'r': {'upper': Array('\u0154', '\u0156', '\u0158'), 'lower': Array('\u0155', '\u0157', '\u0159')},
	's': {'upper': Array('\u015a', '\u015c', '\u015e', '\u0160', '\u0218'), 'lower': Array('\u015b', '\u015d', '\u015f', '\u0161', '\u0219')},
	't': {'upper': Array('\u0162', '\u0164', '\u0166', '\u021a'), 'lower': Array('\u0163', '\u0165', '\u0167', '\u021b')},
	'u': {'upper': Array('Ù', 'Ú', 'Û', 'Ü', '\u0168', '\u016a', '\u016c', '\u016e', '\u0170', '\u0172', '\u01d3', '\u01d5', '\u01d7', '\u01d9', '\u01db'), 'lower': Array('ù', 'ú', 'û', 'ü', '\u0169', '\u016b', '\u016d', '\u016f', '\u0171', '\u0173', '\u01d4', '\u01d6', '\u01d8', '\u01da', '\u01dc')},
	'w': {'upper': Array('\u0174'), 'lower': Array('\u0175')},
	'y': {'upper': Array('Ý', '\u0176', '\u0178'), 'lower': Array('ý', 'ÿ', '\u0177')},
	'z': {'upper': Array('\u0179', '\u017b', '\u017d'), 'lower': Array('\u017a', '\u017c', '\u017e')},
	'misc': {'upper': Array('ß', 'Þ', 'þ'), 'lower': null}
};

var jquery_charmap_field = null;

function jquery_charmap_insert_char (substituteChar) {
	var firstPart = $(jquery_charmap_field).val().slice(0, jquery_charmap_field.selectionStart);
	var secondPart = $(jquery_charmap_field).val().slice(jquery_charmap_field.selectionEnd);
	$(jquery_charmap_field).val(firstPart+substituteChar+secondPart).focus();
}

(function($){
	$.fn.charmap = function(options) {
		var defaults = {
			'left': null
		};

		var options = $.extend(defaults, options);

		$(this).bind('focus', function (event) {
			jquery_charmap_field = event.target;

			var offsetParent = jquery_charmap_field;
			var offsetLeft = jquery_charmap_field.offsetLeft;
			var offsetTop = jquery_charmap_field.offsetTop;

			if(offsetParent.offsetParent != document.getElementsByTagName('body')[0]){
				do {
					offsetParent = offsetParent.offsetParent;
					offsetLeft = offsetLeft + offsetParent.offsetLeft;
					offsetTop = offsetTop + offsetParent.offsetTop;
				} while (document.getElementsByTagName('body')[0] != offsetParent.offsetParent);
			}

			if($('#jquery_charmap').length == 0){
				$('body').append('<div id="jquery_charmap"></div>');

				$('#jquery_charmap')
					.append('<div id="jquery_charmap_header"></div>')
					.append('<table id="jquery_charmap_upper_substitutes"><tr></tr></table><table id="jquery_charmap_lower_substitutes"><tr></tr></table>')
					.css('top', offsetTop - 100)
					.show();

				if(options.left == null)
					$('#jquery_charmap').css('left', offsetLeft + offsetWidth + 10);
				else
					$('#jquery_charmap').css('left', options.left);

				$('#jquery_charmap_header')
					.append('<a href="javascript:;" onclick="$(\'#jquery_charmap\').hide();" id="jquery_charmap_closer">x</a>')
					.append('<strong>Charmap</strong>')
					.append(' <a href="javascript:;" onclick="$(\'#jquery_charmap table\').hide(); $(\'#jquery_charmap_lower_substitutes\').show()">lower</a>')
					.append('/<a href="javascript:;" onclick="$(\'#jquery_charmap table\').hide(); $(\'#jquery_charmap_upper_substitutes\').show()">upper</a> case');

				$.each(jquery_charmap_chars, function (latinChar, substitutes) {
					$.each(substitutes, function (charCase, substituteChars) {
						$.each(substituteChars, function (index, substituteChar) {
							$('#jquery_charmap_'+charCase+'_substitutes tr:last')
								.append('<td><a href="javascript:;" onclick="jquery_charmap_insert_char(\''+substituteChar+'\');" class="jquery_charmap_substitute">'+substituteChar+'</a></td>');

							if($('#jquery_charmap_'+charCase+'_substitutes a').length % 10 == 0)
								$('#jquery_charmap_'+charCase+'_substitutes').append('<tr></tr>');
						});
					});
				});
			}

			$('#jquery_charmap')
				.animate({'top': offsetTop - 100}, 'slow')
				.show();

			if(options.left == null)
				$('#jquery_charmap').css('left', offsetLeft + offsetWidth + 10);
			else
				$('#jquery_charmap').css('left', options.left);
		});

		return this;
	}
})(jQuery);