var bibliographie_mouse_last_x = 0;
var bibliographie_mouse_last_y = 0;

var bibliographie_mouse_current_x = 0;
var bibliographie_mouse_current_y = 0;

var bibliographie_mouse_vector_x = 0;
var bibliographie_mouse_vector_y = 0;

var bibliographie_mouse_distance = 0;

var bibliographie_mouse_cycles = 0;
var bibliographie_mouse_tracker = false;

var bibliographie_mouse_strg_pressed = false;

function bibliographie_mouse () {
	bibliographie_mouse_cycles = bibliographie_mouse_cycles + 1;

	bibliographie_mouse_vector_x = bibliographie_mouse_current_x - bibliographie_mouse_last_x;
	bibliographie_mouse_vector_y = bibliographie_mouse_current_y - bibliographie_mouse_last_y;

	bibliographie_mouse_distance = Math.ceil(Math.sqrt(bibliographie_mouse_vector_x * bibliographie_mouse_vector_x + bibliographie_mouse_vector_y * bibliographie_mouse_vector_y));

	bibliographie_mouse_last_x = bibliographie_mouse_current_x;
	bibliographie_mouse_last_y = bibliographie_mouse_current_y;
}

(function($){
	$.fn.dodge = function () {
		if(bibliographie_mouse_tracker == false){
			$(document).mousemove(function (event) {
				bibliographie_mouse_current_x = event.pageX;
				bibliographie_mouse_current_y = event.pageY;
			}).keydown(function (event) {
				if(event.which == 17)
					bibliographie_mouse_strg_pressed = true;
			}).keyup(function (event) {
				bibliographie_mouse_strg_pressed = false;
			});
			setInterval('bibliographie_mouse()', 20);

			bibliographie_mouse_tracker = true;
		}

		$(this).mouseover(function (event) {
			if(bibliographie_mouse_strg_pressed){
				$(event.currentTarget).css('left', bibliographie_mouse_current_x - (event.currentTarget.offsetWidth / 2)).css('top', bibliographie_mouse_current_y - (event.currentTarget.offsetHeight / 2));
			}else{
				if(bibliographie_mouse_distance > 100 && bibliographie_mouse_cycles > 10){
					var position = $(event.currentTarget).offset();

					position.left = position.left + 1.5 * bibliographie_mouse_vector_x;
					position.top = position.top + 1.5 * bibliographie_mouse_vector_y;

					$(event.currentTarget).animate(position, 'slow');
					bibliographie_mouse_cycles = 0;
				}
			}
		});

		return this;
	};
})(jQuery);