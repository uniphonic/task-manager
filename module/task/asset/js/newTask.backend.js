/**
 * Initialise l'objet "task" ainsi que la méthode "init" obligatoire pour la bibliothèque EoxiaJS.
 *
 * @since 1.0.0
 * @version 1.5.0
 */
window.eoxiaJS.taskManager.newTask = {};

window.eoxiaJS.taskManager.newTask.init = function() {
	window.eoxiaJS.taskManager.newTask.event();
};

window.eoxiaJS.taskManager.newTask.event = function() {
	jQuery( '.tm-wrap' ).on( 'click', '.task-toggle-points', window.eoxiaJS.taskManager.newTask.togglePoints );
};

window.eoxiaJS.taskManager.newTask.togglePoints = function() {
	if ( jQuery( this ).find( '.fas' ).hasClass( 'fa-angle-down' ) ) {
		jQuery( this ).find( '.fas' ).removeClass( 'fa-angle-down' ).addClass( 'fa-angle-right' );
	} else {
		var data = {};
		var element;

		if ( ! element ) {
			element = jQuery( this );
		}

		data.action = 'load_point';
		data._wpnonce = element.data( 'nonce' );
		data.task_id = element.data( 'id' );
		window.eoxiaJS.loader.display( element );
		window.eoxiaJS.request.send( element, data );

		jQuery( this ).find( '.fas' ).removeClass( 'fa-angle-right' ).addClass( 'fa-angle-down' );
	}
};
