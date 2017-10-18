<?php
/**
 * Les actions relatives aux activitées.
 *
 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
 * @since 1.5.0
 * @version 1.5.0
 * @copyright 2015-2017 Eoxia
 * @package Task_Manager
 */

namespace task_manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les actions relatives aux activitées.
 */
class Activity_Action {

	/**
	 * Initialise les actions liées aux activitées.
	 *
	 * @since 1.5.0
	 * @version 1.5.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_load_last_activity', array( $this, 'callback_load_last_activity' ) );

		add_action( 'admin_bar_menu', array( $this, 'action_admin_bar_menu' ), 11 );

		add_action( 'wp_ajax_open_popup_user_activity', array( $this, 'load_customer_activity' ) );
	}

	/**
	 * Charges les évènements liés à la tâche puis renvoie la vue.
	 *
	 * @since 1.5.0
	 * @version 1.5.0
	 *
	 * @return void
	 */
	public function callback_load_last_activity() {
		// check_ajax_referer( 'switch_view_to_grid' );

		$title = ! empty( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
		$tasks_id = ! empty( $_POST['tasks_id'] ) ? sanitize_text_field( $_POST['tasks_id'] ) : '';
		$offset = ! empty( $_POST['offset'] ) ? (int) $_POST['offset'] : 0;
		$last_date = ! empty( $_POST['last_date'] ) ? sanitize_text_field( $_POST['last_date'] ) : '';
		$term = ! empty( $_POST['term'] ) ? sanitize_text_field( $_POST['term'] ) : '';
		$categories_id_selected = ! empty( $_POST['categories_id_selected'] ) ? sanitize_text_field( $_POST['categories_id_selected'] ) : '';
		$follower_id_selected = ! empty( $_POST['follower_id_selected'] ) ? (int) $_POST['follower_id_selected'] : 0;

		if ( empty( $tasks_id ) ) {
			$tasks = Task_Class::g()->get_tasks( array(
				'posts_per_page' => \eoxia\Config_Util::$init['task-manager']->task->posts_per_page,
				'categories_id' => $categories_id_selected,
				'term' => $term,
				'users_id' => $follower_id_selected,
			) );

			$tasks_id = array_map( function( $e ) {
				return $e->id;
			}, $tasks );
		} else {
			$tasks_id = explode( ',', $tasks_id );
		}

		$datas = Activity_Class::g()->get_activity( $tasks_id, $offset );

		if ( ! empty( $offset ) ) {
			$offset += \eoxia\Config_Util::$init['task-manager']->activity->activity_per_page;
		} else {
			$offset = \eoxia\Config_Util::$init['task-manager']->activity->activity_per_page;
		}

		$last_date = $datas['last_date'];
		unset( $datas['last_date'] );

		ob_start();
		\eoxia\View_Util::exec( 'task-manager', 'activity', 'backend/list', array(
			'datas' => $datas,
			'last_date' => $last_date,
		) );

		$view = ob_get_clean();

		$data_search = Navigation_Class::g()->get_search_result( $term, $categories_id_selected, $follower_id_selected );
		ob_start();
		\eoxia\View_Util::exec( 'task-manager', 'activity', 'backend/title', array(
			'term' => $data_search['term'],
			'categories_searched' => $data_search['categories_searched'],
			'follower_searched' => $data_search['follower_searched'],
			'have_search' => $data_search['have_search'],
		) );
		$title_popup = ob_get_clean();

		if ( ! empty( $title_popup ) ) {
			$title_popup = ':' . $title_popup;
		}

		wp_send_json_success( array(
			'namespace' => 'taskManager',
			'module' => 'activity',
			'callback_success' => 'loadedLastActivity',
			'view' => $view,
			'title_popup' => $title . $title_popup,
			'offset' => $offset,
			'last_date' => $last_date,
			'end' => ( 0 === count( $datas ) ) ? true : false,
		) );
	}

	/**
	 * Adds a 'Switch back to {user}' link to the account menu in WordPress' admin bar.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The admin bar object.
	 */
	public function action_admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {

		if ( ! function_exists( 'is_admin_bar_showing' ) ) {
			return;
		}
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		if ( method_exists( $wp_admin_bar, 'get_node' ) ) {
			if ( $wp_admin_bar->get_node( 'user-actions' ) ) {
				$parent = 'user-actions';
			} else {
				return;
			}
		} elseif ( get_option( 'show_avatars' ) ) {
			$parent = 'my-account-with-avatar';
		} else {
			$parent = 'my-account';
		}

		if ( current_user_can( 'manage_options' ) ) {
			$query_args = array(
				'action'   => 'open_popup_user_activity',
				'_wpnonce' => wp_create_nonce( 'load_user_activity' ),
				'width'    => '1024',
				'height'   => '768px',
				'first_load'   => true,
			);
			$wp_admin_bar->add_menu( array(
				'parent' => $parent,
				'id'     => 'task-manger-view-daily-activities',
				'href'			=> '#',
				'title'			=> __( 'My daily activity', 'task-manager' ),
				'meta'		 	=> array(
					'onclick' => 'tb_show( "' . __( 'Your activity', 'task-manager' ) . '", "' . add_query_arg( $query_args, admin_url( 'admin-ajax.php' ) ) . '" )',
				),
			) );
		}
	}

	/**
	 * Load user activity by date
	 */
	public function load_customer_activity() {
		check_ajax_referer( 'load_user_activity' );

		$customer_id = get_current_user_id();
		$date_start = ! empty( $_POST ) && ! empty( $_POST['tm_abu_date_start'] ) ? $_POST['tm_abu_date_start'] : current_time( 'Y-m-d' );
		$date_end = ! empty( $_POST ) && ! empty( $_POST['tm_abu_date_end'] ) ? $_POST['tm_abu_date_end'] : current_time( 'Y-m-d' );
		$first_load = ! empty( $_GET ) && ! empty( $_GET['first_load'] ) ? $_GET['first_load'] : false;

		$view = Activity_Class::g()->display_user_activity_by_date( $customer_id, $date_start, $date_end );

		if ( $first_load ) {
			wp_die( $view ); // WPCS: XSS ok.
		}

		wp_send_json_success( array(
			'namespace' => 'taskManager',
			'module' => 'adminBar',
			'callback_success' => 'loadedCustomerActivity',
			'view' => $view,
		) );
	}

}

new Activity_Action();
