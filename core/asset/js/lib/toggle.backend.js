window.task_manager.toggle = {};

window.task_manager.toggle.init = function() {
	window.task_manager.toggle.event();
};

window.task_manager.toggle.event = function() {
  jQuery( document ).on( 'click', '.toggle:not(.disabled), .toggle:not(.disabled) i', window.task_manager.toggle.open );
  jQuery( document ).on( 'click', 'body', window.task_manager.toggle.close );
};

window.task_manager.toggle.open = function( event ) {
	var target = undefined;
	var elementToggle = jQuery( this );

	if ( elementToggle.is( 'i' ) ) {
		elementToggle = elementToggle.parents( '.toggle' );
	}

	jQuery( '.toggle .content.active' ).removeClass( 'active' );

	if ( elementToggle.data( 'parent' ) ) {
		target = elementToggle.closest( '.' + elementToggle.data( 'parent' ) ).find( '.' + elementToggle.data( 'target' ) );
	} else {
		target = jQuery( '.' + elementToggle.data( 'target' ) );
	}

	if ( target ) {
	  target.toggleClass( 'active' );
	  event.stopPropagation();
	}
};

window.task_manager.toggle.close = function( event ) {
	jQuery( '.toggle .content' ).removeClass( 'active' );
	event.stopPropagation();
};
