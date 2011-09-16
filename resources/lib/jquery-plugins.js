(function($){
	var tables = Array();
	var clones = Array();
	var i = 0;

	$.fn.extend({
		floatingTableHead: function (options) {
			var defaults = {
				context: document,
				contextIsjQuery: false,
				reset: false
			}
			
			var options = $.extend(defaults, options);

			if(options.contextIsjQuery == false)
				options.context = $(options.context);

			if(options.reset){
				$.each(clones, function (key, clone) {
					clone.remove();
				});
				tables = null;	tables = Array();
				clones = null;	clones = Array();
				i = 0;
			}

			this.each(function (key, value) {
				if($.inArray($(value), tables) == -1)
					tables[i++] = $(value);
			});

			$.each(tables, function (key, table) {
				var tableHead = table.find('tr:first');

				if(typeof(clones[key]) != 'object'){
					clones[key] = tableHead
						.clone(true, true)
						.css('display', 'none')
						.css('position', 'fixed')
						.css('top', 0)
						.css('left', tableHead.offset().left)
						.css('width', table.width())
						.css('z-index', parseInt(10 + 2 * i));

					tableHead.children('th').each(function (index) {
						clones[key].children('th:eq('+index+')').css('width', $(this).width());
					});

					clones[key].appendTo(table);
				}
			});

			options.context.unbind('scroll');
			if(clones.length > 0){
				options.context.bind('scroll', function () {
					$.each(tables, function (key, table) {
						var tableHead = table.find('tr:first');

						if(tableHead){
							if(options.context.scrollTop() >= Math.round(tableHead.offset().top) && options.context.scrollTop() <= Math.round(table.offset().top + table.height() - 2 * tableHead.height())){
								if(!$(clones[key]).is(':visible'))
									clones[key].show();
							}else if($(clones[key]).is(':visible'))
								clones[key].hide();
						}else{
							clones[key] = null;
							tables[key] = null;
						}
					});
				});
			}

			return this;
		}
	});
})(jQuery);