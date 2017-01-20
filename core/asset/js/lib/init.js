"use strict";

window.eoxiaJS = {};
window.task_manager = {};

window.eoxiaJS.init = function() {
	window.eoxiaJS.load_list_script();
	window.eoxiaJS.init_array_form();
}

window.eoxiaJS.load_list_script = function() {
	for ( var key in window.task_manager ) {
		window.task_manager[key].init();
	}
}

window.eoxiaJS.init_array_form = function() {
	 window.eoxiaJS.array_form.init();
}

jQuery(document).ready(window.eoxiaJS.init);
