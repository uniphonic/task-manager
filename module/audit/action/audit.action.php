<?php
/**
 * Les actions relatives aux audit.
 *
 * @author Eoxia <dev@eoxia.com>
 * @since 1.9.0
 * @version 1.9.0
 * @copyright 2019 Eoxia
 * @package Task_Manager
 */

namespace task_manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Les actions relatives aux tâches.
 */
class Audit_Action {

	/**
	 * Initialise les actions liées au audit.
	 *
	 * @since 1.9.0
	 * @version 1.9.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'callback_init' ) );
		add_action( 'admin_menu', array( $this, 'callback_admin_menu' ), 30 );

		add_action( 'add_meta_boxes', array( $this, 'callback_add_metabox' ), 10, 2 );

		add_action( 'wp_ajax_start_new_audit', array( $this, 'callback_start_new_audit' ), 10, 2 );

		add_action( 'wp_ajax_delete_audit', array( $this, 'callback_delete_audit' ) );
		add_action( 'wp_ajax_edit_audit', array( $this, 'callback_edit_audit' ) );
		add_action( 'wp_ajax_audit_select_task', array( $this, 'callback_audit_select_task' ) );
		add_action( 'wp_ajax_edit_title_audit', array( $this, 'callback_edit_title_audit' ) );

		add_action( 'wp_ajax_reset_main_page', array( $this, 'callback_reset_main_page' ) );

		add_action( 'wp_ajax_audit_created_task', array( $this, 'callback_audit_created_task' ) );

		add_action( 'wp_ajax_audit_import_tasks_and_points', array( $this, 'callback_audit_import_tasks_and_points' ) );
		add_action( 'wp_ajax_search_audit_client', array( $this, 'callback_search_audit_client' ) );

		add_action( 'wp_ajax_search_client_for_audit', array( $this, 'ajax_search_client_for_audit' ) );
	}

	/**
	 *
	 *
	 * @return void
	 *
	 * @since 1.9.0
	 * @version 1.9.0
	 */
	public function callback_init() {}

	public function callback_admin_menu() {

		add_submenu_page( 'wpeomtm-dashboard', __( 'Audit', 'task-manager' ), __( 'Audit', 'task-manager' ), 'manage_task_manager', 'audit-page', array( Audit_Class::g(), 'callable_audit_page' ) );

		ob_start();
		\eoxia\View_Util::exec( 'task-manager', 'audit', 'metabox-head-auditlist', array() );

		$view = ob_get_clean();

		add_meta_box( 'tm-indicator-audit', __( 'Audit List', 'task-manager' ) . apply_filters( 'tm_posts_metabox_audit', $view ), array( Audit_Class::g(), 'callback_audit_list_metabox' ), 'audit-page', 'normal' );

	}

	/**
	 * Fait le contenu de la metabox
	 *
	 * @param string  $post_type Le type du post.
	 * @param WP_Post $post      Les données du post.
	 *
	 * @since 1.0.0
	 * @version 1.6.2
	 */
	public function callback_add_metabox( $post_type, $post ) {
		if ( in_array( $post_type, \eoxia\Config_Util::$init['task-manager']->associate_post_type, true ) ) {

				$data = array();

				ob_start();
				\eoxia\View_Util::exec( 'task-manager', 'audit', 'metabox-head-auditlist', array( 'post_id' => $post->ID ) );

				$view = ob_get_clean();

				add_meta_box( 'wpeo-task-metabox-auditlist', __( 'Audit', 'task-manager' ) . apply_filters( 'tm_posts_metabox_audit', $view ), array( Audit_Class::g(), 'callback_render_indicator' ), $post_type, 'normal', 'default' );

			}
	}

	public function callback_start_new_audit( ){
		check_ajax_referer( 'start_new_audit' );

		$parent_id = isset( $_POST[ 'parent_id' ] ) ? (int) $_POST[ 'parent_id' ] : 0;
		$page = isset( $_POST[ 'page' ] ) ? sanitize_text_field( $_POST[ 'page' ] ) : 'audit-createnew';

		$args = array();
		if( $parent_id ){
			$args = array(
					'parent_id' => $parent_id
			);
		}

		$audit = Audit_Class::g()->create( $args );

		ob_start();
		\eoxia\View_Util::exec(
			'task-manager',
			'audit',
			$page,
			array(
			'audit' => $audit,
			'parent_id' => $parent_id
			)
		);

		wp_send_json_success(
			array(
				'namespace'        => 'taskManager',
				'module'           => 'audit',
				'callback_success' => 'startNewAudit',
				'view'             => ob_get_clean(),
			)
		);
	}

	public function callback_delete_audit( ){
		$audit_id  = isset( $_POST[ 'id' ] ) ? (int) $_POST[ 'id' ] : 0;
		$parent_id = isset( $_POST[ 'parent_id' ] ) ? (int) $_POST[ 'parent_id' ] : 0;
		$page      = isset( $_POST[ 'page' ] ) ? sanitize_text_field( $_POST[ 'page' ] ) : '';


		if( ! $audit_id || ( ! $parent_id && ! $page ) ){
			wp_send_json_error();
		}

		Audit_Class::g()->update(
			 array(
			'id'     => $audit_id,
			'status' => 'trash',
		));

		$this->callback_reset_main_page( $parent_id, $page );
	}

	public function callback_edit_audit( $id = 0 ){
		check_ajax_referer( 'edit_audit' );

		$audit_id  = isset( $_POST[ 'id' ] ) ? (int) $_POST[ 'id' ] : 0;
		$parent_id = isset( $_POST[ 'parent_id' ] ) ? (int) $_POST[ 'parent_id' ] : 0;
		$page      = isset( $_POST[ 'page' ] ) ? sanitize_text_field( $_POST[ 'page' ] ) : '';

		if( $id ){
			$audit_id = $id;
		}

		if( ! $audit_id ){
			wp_send_json_error();
		}

		$audit = Audit_Class::g()->get( array( 'id' => $audit_id ), true );

		$parent_id = $audit->data[ 'parent_id' ] ? $audit->data[ 'parent_id' ] : 0;

		$page = $page ? $page : 'audit-createnew';

		ob_start();

		if( ! empty ( $audit ) ){
			\eoxia\View_Util::exec(
				'task-manager',
				'audit',
				$page,
				array(
					'audit' => $audit,
					'parent_id' => $parent_id
				)
			);
		}

		wp_send_json_success(
			array(
				'namespace'        => 'taskManager',
				'module'           => 'audit',
				'callback_success' => 'startNewAudit',
				'view'             => ob_get_clean(),
				'page'             => $page
			)
		);
	}

	public function callback_edit_title_audit(){
		$title        = isset( $_POST[ 'title' ] ) ? $_POST[ 'title' ] : '';
		$id           = isset( $_POST[ 'id' ] ) ? (int) $_POST[ 'id' ] : 0;
		$deadline_str = isset( $_POST[ 'due_date' ] ) ? strtotime( $_POST[ 'due_date' ] ) : 0;
		$customer_id  = isset( $_POST[ 'customer_id' ] ) ? (int) $_POST[ 'customer_id' ] : 0;

		if( ! $id ){
			wp_send_json_error();
		}

		$audit->data[ 'date_modified' ] = current_time( 'mysql' );

		if( $title ){
			$title = str_replace( array( '&nbsp;' ), '', $title );

			$audit = Audit_Class::g()->get( array( 'id' => $id ), true );

			$audit->data[ 'title' ] = $title;
		}

		if( $deadline_str > 0 ){
			$audit->data[ 'deadline' ] = date( 'Y-m-d 00:00:00', $deadline_str );
		}

		if( $customer_id ){
			$audit->data[ 'parent_id' ] = $customer_id;
		}

		$audit = Audit_Class::g()->update( $audit->data );

		wp_send_json_success(
			array(
				'namespace'        => 'taskManager',
				'module'           => 'audit',
				'callback_success' => 'updateTitle',
				'title'            => $title,
				'deadline'         => $audit->data[ 'deadline' ],
				'customer_id'      => $customer_id
			)
		);
	}

	public function callback_audit_select_task() {
		$task_id = isset( $_POST[ 'task_id' ] ) ? (int) $_POST[ 'task_id' ] : 0;
		$parent_id = isset( $_POST[ 'parent_id' ] ) ? (int) $_POST[ 'parent_id' ] : 0;
		$audit_id = isset( $_POST[ 'audit_id' ] ) ? (int) $_POST[ 'audit_id' ] : 0;

		if( ! $task_id || ! $audit_id ){
			wp_send_json_error();
		}

		$parent = array( 'ID' => $task_id );

	 	Task_Class::g()->update( array( 'id' => $task_id, 'parent_id' => $audit_id,) );

		$shortcode_view = do_shortcode( '[task post_parent="' . $audit_id .'"]' );

		wp_send_json_success(
			array(
				'namespace'        => 'taskManager',
				'module'           => 'audit',
				'callback_success' => 'displayShortcodeTask',
				'view'             => $shortcode_view,
			)
		);
	}

	public function callback_reset_main_page( $id = 0, $page = ''){
		$parent_id = isset( $_POST[ 'parent_id' ] ) ? $_POST[ 'parent_id' ] : 0;

		if( ! $page ){
			$page      = isset( $_POST[ 'page' ] ) ? sanitize_text_field( $_POST[ 'page' ] ) : '';
		}

		if( $id ){
			$parent_id = $id;
		}

		if( ! $parent_id && ! $page ){
			wp_send_json_error();
		}

		ob_start();

		if( $page ){
			Audit_Class::g()->callback_audit_list_metabox( array(), array(), true );
		}else if( $parent_id ){
			Audit_Class::g()->callback_render_indicator( array(), $parent_id, true );
		}else{

		}

		$view = ob_get_clean();

		wp_send_json_success(
			array(
				'namespace'        => 'taskManager',
				'module'           => 'audit',
				'callback_success' => 'viewMainPage',
				'view'             => $view
			)
		);
	}

	public function callback_audit_created_task(){
		check_ajax_referer( 'audit_created_task' );

		$parent_id         = ! empty( $_POST['parent_id'] ) ? (int) $_POST['parent_id'] : 0;
		$tag_slug_selected = ! empty( $_POST['tag'] ) ? sanitize_text_field( $_POST['tag'] ) : 0;

		$task_args = array(
			'title'     => __( 'New task', 'task-manager' ),
			'parent_id' => $parent_id,
			'status'    => 'inherit',
		);

		if ( ! empty( $tag_slug_selected ) ) {
			$tag = get_term_by( 'slug', $tag_slug_selected, 'wpeo_tag', 'ARRAY_A' );

			if ( empty( $tag ) ) {
				$tag = wp_create_term( $tag_slug_selected, 'wpeo_tag' );
			}

			$task_args['taxonomy'] = array(
				Tag_Class::g()->get_type() => array(
					$tag['term_id'],
				),
			);
		}

		$task = Task_Class::g()->create( $task_args, true );

		ob_start();
		\eoxia\View_Util::exec(
			'task-manager',
			'task',
			'backend/task',
			array(
				'task' => $task,
			)
		);

		wp_send_json_success(
			array(
				'namespace'        => 'taskManager',
				'module'           => 'audit',
				'callback_success' => 'createdAuditTaskSuccess',
				'view'             => ob_get_clean(),
			)
		);
	}


	public function callback_audit_import_tasks_and_points(){
		check_ajax_referer( 'audit_import_tasks_and_points' );

		$post_id = isset( $_POST[ 'parent_id' ] ) ? (int) $_POST[ 'parent_id' ] : 0;
		$content = isset( $_POST[ 'content' ] ) ? $_POST[ 'content' ] : '';
		$task_id = isset( $_POST[ 'task_id' ] ) ? $_POST[ 'task_id' ] : 0;

		if( ! $content || ! $post_id ){
			wp_send_json_error( array(
				'message' => 'Content empty or parentID undefined',
			) );
		}

		$created_elements = Import_Class::g()->treat_content( $post_id, $content, $task_id );

		if ( ! empty( $created_elements['created']['tasks'] ) ) {
			$type = 'tasks';
			foreach ( $created_elements['created']['tasks'] as $task ) {
				$task_id = $task->data[ 'id' ];
				$task = Task_Class::g()->update( array( 'id' => $task_id, 'status' => 'inherit' ) );

				ob_start();
				\eoxia\View_Util::exec(
					'task-manager',
					'task',
					'backend/task',
					array(
						'task' => $task,
					)
				);
				$view .= ob_get_clean();
			}

		} elseif ( ! empty( $created_elements['created']['points'] ) ) {
			$type    = 'points';
			$point  = '';
			$task = Task_Class::g()->get(
				array(
					'id' => $task_id,
				),
				true
			);

			$task->data['count_uncompleted_points'] += count( $created_elements['created']['points'] );
			Task_Class::g()->update( $task->data, true );

			foreach ( $created_elements['created']['points'] as $point ) {
				ob_start();
				\eoxia\View_Util::exec(
					'task-manager',
					'point',
					'backend/point',
					array(
						'point'      => $point,
						'parent_id'  => $task_id,
						'comment_id' => 0,
						'point_id'   => 0,
					)
				);
				$view .= ob_get_clean();
			}

			Task_Class::g()->update( array( 'id' => $task_id, 'status' => 'inherit' ) );
		}

		wp_send_json_success( array(
			'namespace'        => 'taskManager',
			'module'           => 'audit',
			'callback_success' => 'importAuditTaskSuccess',
			'view'             => $view,
		) );
	}

	public function callback_search_audit_client(){ // strtotime( "midnight", strtotime( 'now' ) )
		check_ajax_referer( 'search_audit_client' );

		$date_start_str        = isset( $_POST[ 'tm_indicator_date_start' ] ) && $_POST[ 'tm_indicator_date_start' ] != "" ? strtotime( $_POST[ 'tm_indicator_date_start' ] ) : 0;
		$date_end_str          = isset( $_POST[ 'tm_indicator_date_end' ] ) && $_POST[ 'tm_indicator_date_end' ] != "" ? strtotime( $_POST[ 'tm_indicator_date_end' ] ) : 0;
		$audit_selector        = isset( $_POST[ 'tm_audit_selector_search_' ] ) ? $_POST[ 'tm_audit_selector_search_' ] : 'all';
		$customer_select       = isset( $_POST[ 'tm_audit_selector_customer' ] ) ? (int) $_POST[ 'tm_audit_selector_customer' ] : 0;
		$selector_modification = isset( $_POST[ 'modification' ] ) ? $_POST[ 'modification' ] : '';

		$date_modification = false;
		if( $date_start_str != 0 && $date_end_str != 0 ){
			$date_modification = true;
		}

		$audits = Audit_Class::g()->get();

		if( $customer_select ){
			$audits_valid = Audit_Class::g()->get( array( 'post_parent' => $customer_select ) );

			foreach( $audits as $key => $audit ){
				foreach( $audits_valid as $key_valid => $audit_valid ){
					if( $audit->data[ 'id' ] == $audit_valid->data[ 'id' ] ){
						$audits[ $key ]->data[ 'valid' ] = true;
					}
				}
			}
		}

		if( $selector_modification ){
			if( $audit_selector == "completed" || $audit_selector == "progress" ){ // Si l'audit selector est égal à ALL, on n'a pas besoin de trier
				$audits = Audit_Class::g()->sortByStatus( $audits, $audit_selector );
			}else{
				$selector_modification = false;
			}
		}

		if( $date_modification ){
			$return = Audit_Class::g()->sortByDateStartDateEndDate( $audits, $date_start_str, $date_end_str, $selector_modification );
			$audits = $return[ 'audits' ];
			$date_start_str = $return[ 'date_start' ];
			$date_end_str = $return[ 'date_end' ];
		}

		$list_valid_audit = array();

		foreach( $audits as $key => $audit ){ // Pour chaque audit

			$data = array( 'id' => $audit->data[ 'id' ], 'valid' => 0 );
			if( $audit->data[ 'valid' ] ){ // On verifie si il est valide
				$data[ 'valid' ] = 1;
			}

			array_push( $list_valid_audit, $data );
		}

		wp_send_json_success( array(
			'namespace'        => 'taskManager',
			'module'           => 'audit',
			'callback_success' => 'searchAuditFilter',
			'list_audit'       => $list_valid_audit,
			'start_day'        => date( 'Y/m/d', $date_start_str ),
			'end_day'          => date( 'Y/m/d', $date_end_str )
		) );
	}

	public function ajax_search_client_for_audit() {
		$term          = sanitize_text_field( $_GET['term'] );
		$founded_by_id = false;

		$query = new \WP_Query(
			array(
				'post_type'   => 'wpshop_customers',
				's'           => $term,
			)
		);

		$customers_founded = array();

		if ( ! empty( $query->posts ) ) {
			foreach ( $query->posts as $post ) {
				if ( $post->ID == $term ) {
					$founded_by_id = true;
				}

				$customers_founded[] = array(
					'label' => '#' . $post->ID . ' ' . $post->post_title,
					'value' => '#' . $post->ID . ' ' . $post->post_title,
					'id'    => $post->ID,
				);
			}
		}

		$term = (int) $term;
		if ( ! $founded_by_id && ! empty( $term ) && is_int( $term ) ) {
			$post = get_post( $term );

			if ( ! empty( $post ) ) {
				if ( 'wpeo-task' === $post->post_type ) {
					$customers_founded[] = array(
						'label' => '#' . $post->ID . ' ' . $post->post_title,
						'value' => '#' . $post->ID . ' ' . $post->post_title,
						'id'    => $post->ID,
					);
				}
			}
		}

		if ( empty( $customers_founded ) ) { // Aucun client trouvé
			$customers_founded[] = array(
				'label' => __( 'No client found', 'task-manager' ),
				'value' => __( 'No client found', 'task-manager' ),
				'id'    => 0,
			);
		}

		wp_die( wp_json_encode( $customers_founded ) );
	}

}

new Audit_Action();
