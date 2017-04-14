<?php
/**
 * Initialise les actions liées à la barre de recherche.
 *
 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
 * @since 1.0.0.0
 * @version 1.3.6.0
 * @copyright 2015-2017 Eoxia
 * @package task
 * @subpackage action
 */

namespace task_manager;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Les actions relatives aux tâches.
 */
class Navigation_Action {

	/**
	 * Initialise les actions liées à la barre de recherche.
	 *
	 * @since 1.0.0.0
	 * @version 1.3.6.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_load_all_task', array( $this, 'callback_load_all_task' ) );
		add_action( 'wp_ajax_load_my_task', array( $this, 'callback_load_my_task' ) );
		add_action( 'wp_ajax_load_archived_task', array( $this, 'ajax_load_archived_task' ) );

		add_action( 'wp_ajax_search', array( $this, 'callback_search' ) );

	}

	/**
	 * Charges toutes les tâches expecté celles qui sont archivées.
	 *
	 * @return void
	 *
	 * @since 1.0.0.0
	 * @version 1.3.6.0
	 */
	public function callback_load_all_task() {
		check_ajax_referer( 'load_all_task' );

		ob_start();
		echo do_shortcode( '[task_manager_dashboard_content]' );
		wp_send_json_success( array(
			'view' => ob_get_clean(),
			'module' => 'task',
			'callback_success' => 'loadedAllTask',
		) );
	}

	/**
	 * Charges toutes les tâches au l'utilisateur courant est affecté soit en tant que responsable ou followers.
	 *
	 * @return void
	 *
	 * @since 1.0.0.0
	 * @version 1.3.6.0
	 */
	public function callback_load_my_task() {
		check_ajax_referer( 'load_my_task' );

		ob_start();
		echo do_shortcode( '[task_manager_dashboard_content users_id="' . get_current_user_id() . '"]' );
		wp_send_json_success( array(
			'view' => ob_get_clean(),
			'module' => 'navigation',
			'callback_success' => 'loadedMyTask',
		) );
	}

	/**
	 * Charges toutes les tâches archivées
	 *
	 * @return void
	 *
	 * @since 1.0.0.0
	 * @version 1.3.6.0
	 */
	public function ajax_load_archived_task() {
		check_ajax_referer( 'load_archived_task' );

		ob_start();
		echo do_shortcode( '[task_manager_dashboard_content status="archive"]' );
		wp_send_json_success( array(
			'module' => 'tag',
			'callback_success' => 'loadedArchivedTask',
			'view' => ob_get_clean(),
		) );
	}

	/**
	 * Utilises le shorcode "tasks" pour récupérer les tâches correspondant au critères de la recherche.
	 *
	 * @return void
	 *
	 * @since 1.0.0.0
	 * @version 1.3.6.0
	 * @todo nonce
	 */
	public function callback_search() {
		$term = ! empty( $_POST['term'] ) ? sanitize_text_field( $_POST['term'] ) : '';
		$categories_id_selected = ! empty( $_POST['categories_id_selected'] ) ? sanitize_text_field( $_POST['categories_id_selected'] ) : '';
		$follower_id_selected = ! empty( $_POST['follower_id_selected' ] ) ? (int) $_POST['follower_id_selected'] : '';

		ob_start();
		Navigation_Class::g()->display_search_result( $term, $categories_id_selected, $follower_id_selected );
		$search_result_view = ob_get_clean();

		ob_start();
		do_shortcode( '[task_manager_dashboard_content users_id="' . $follower_id_selected . '" categories_id="' . $categories_id_selected . '" term="' . $term . '" posts_per_page="' . Config_Util::$init['task']->posts_per_page . '"]' );
		$tasks_view = ob_get_clean();

		wp_send_json_success( array(
			'module' => 'navigation',
			'callback_success' => 'searchedSuccess',
			'view' => array(
				'tasks' => $tasks_view,
				'search_result' => $search_result_view,
			)
		) );
	}
}

new Navigation_Action();
