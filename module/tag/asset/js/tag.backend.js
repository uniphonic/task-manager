/**
 * Initialise l'objet "point" ainsi que la méthode "tag" obligatoire pour la bibliothèque EoxiaJS.
 *
 * @since 1.0.0.0
 * @version 1.0.0.0
 */
window.task_manager.tag = {};

window.task_manager.tag.init = function() {
	window.task_manager.tag.event();
};

window.task_manager.tag.event = function() { };

/**
 * Lorsqu'on clique sur la barre des tags, avant de lancer l'action on ajoute une classe permettant de bloquer les actions futures tant que cette action n'est pas terminée
 *
 * @param  {HTMLUListElement} element  The element clicked where to display tags.
 */
window.task_manager.tag.before_load_tags = function( element ) {
	element.addClass( 'no-action' );

	return true;
};

/**
 * Le callback en cas de réussite à la requête Ajax "archive_task".
 * Remplaces le contenu de list-task.
 *
 * @param  {HTMLDivElement} triggeredElement  L'élement HTML déclenchant la requête Ajax.
 * @param  {Object}         response          Les données renvoyées par la requête Ajax.
 * @return {void}
 *
 * @since 1.0.0.0
 * @version 1.0.0.0
 */
window.task_manager.tag.archivedTaskSuccess = function( triggeredElement, response ) {
	jQuery( triggeredElement ).closest( '.wpeo-project-task' ).remove();
};

/**
 * Le callback en cas de réussite à la requête Ajax "unarchive_task".
 * Remplaces le contenu de list-task.
 *
 * @param  {HTMLDivElement} triggeredElement  L'élement HTML déclenchant la requête Ajax.
 * @param  {Object}         response          Les données renvoyées par la requête Ajax.
 * @return {void}
 *
 * @since 1.0.0.0
 * @version 1.0.0.0
 */
window.task_manager.tag.unarchivedTaskSuccess = function( triggeredElement, response ) {
	jQuery( triggeredElement ).closest( '.wpeo-project-task' ).remove();
};

/**
 * Le callback en cas de réussite à la requête Ajax "load_tags".
 * Remplaces le contenu de l'element cliqué par la vue reçu dans la réponse AJAX.
 *
 * @param  {HTMLDivElement} triggeredElement  L'élement HTML déclenchant la requête Ajax.
 * @param  {Object}         response          Les données renvoyées par la requête Ajax.
 * @return {void}
 *
 * @since 1.0.0.0
 * @version 1.0.0.0
 */
window.task_manager.tag.loadedTagSuccess = function( element, response ) {
	element.replaceWith( response.data.view );
};

/**
 * Le callback en cas de réussite à la requête Ajax "close_tag_edit_mode".
 * Remplaces le contenu de ".wpeo-tag-wrap" par la vue reçu dans la réponse AJAX.
 *
 * @param  {HTMLDivElement} triggeredElement  L'élement HTML déclenchant la requête Ajax.
 * @param  {Object}         response          Les données renvoyées par la requête Ajax.
 * @return {void}
 *
 * @since 1.0.0.0
 * @version 1.0.0.0
 */
window.task_manager.tag.closedTagEditMode = function( element, response ) {
	element.closest( '.wpeo-tag-wrap' ).replaceWith( response.data.view );
};

/**
 * Le callback en cas de réussite à la requête Ajax "load_archived_task".
 * Remplaces le contenu de list-task.
 *
 * @param  {HTMLDivElement} triggeredElement  L'élement HTML déclenchant la requête Ajax.
 * @param  {Object}         response          Les données renvoyées par la requête Ajax.
 * @return {void}
 *
 * @since 1.0.0.0
 * @version 1.0.0.0
 */
window.task_manager.tag.loadedArchivedTask = function( element, response ) {
	jQuery( '.list-task' ).replaceWith( response.data.view );
};

window.task_manager.tag.tag_affectation_success = function( element, response ) {
	element.addClass( 'active' );
};
