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
	jQuery( '.tm-wrap' ).on( 'blur', '.table-projects .project-title', window.eoxiaJS.taskManager.newTask.editTitle );
	jQuery( '.tm-wrap' ).on( 'click', '.project-toggle-task', window.eoxiaJS.taskManager.newTask.togglePoints );
	jQuery( '.tm-wrap' ).on( 'click', '.project-state .dropdown-item',  window.eoxiaJS.taskManager.newTask.displayState );
};

window.eoxiaJS.taskManager.newTask.editTitle = function() {
	var data = {};
	var element;

	if ( ! element ) {
		element = jQuery( this );
	}


	data.action  = 'edit_title';
	data.task_id = element.closest( '.table-row' ).data( 'id' );
	data.title   = element.text();

	window.eoxiaJS.loader.display( element.closest( 'div' ) );
	window.eoxiaJS.request.send( element, data );
};

window.eoxiaJS.taskManager.newTask.togglePoints = function() {
	if ( jQuery( this ).find( '.fas' ).hasClass( 'fa-angle-down' ) ) {
		jQuery( this ).find( '.fas' ).removeClass( 'fa-angle-down' ).addClass( 'fa-angle-right' );
		jQuery( this ).closest( '.table-column' ).find( '.column-extend' ).slideUp( 400 );
	} else {
		var data = {};
		var element;

		if ( ! element ) {
			element = jQuery( this );
		}

		data.action   = 'load_point';
		data._wpnonce = element.data( 'nonce' );
		data.task_id  = element.data( 'id' );
		window.eoxiaJS.loader.display( element );
		window.eoxiaJS.request.send( element, data );

		jQuery( this ).find( '.fas' ).removeClass( 'fa-angle-right' ).addClass( 'fa-angle-down' );
	}
};

window.eoxiaJS.taskManager.newTask.displayState = function ( event ) {
	var state          = jQuery( this ).attr( 'data-state' );
	var parent_element = jQuery( this ).closest( '.project-state' );
	parent_element.find( 'input[name="state"]' ).val( state );

	var this_html = jQuery( this ).html();
	parent_element.find( '.dropdown-toggle' ).html( this_html );

	var data = {};
	var element;

	if ( ! element ) {
		element = jQuery( this );
	}
	data.action   = 'task_state';
	data.task_id  = parent_element.data( 'id' );
	data.state = state;
	window.eoxiaJS.loader.display( element );
	window.eoxiaJS.request.send( element, data );
};

window.eoxiaJS.taskManager.newTask.taskStateSuccess = function( element, response ) {
	jQuery( element ).closest( '.table-column').replaceWith( response.data.view );
};
