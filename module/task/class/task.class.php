<?php
/**
 * Gestion des tâches.
 *
 * @author Eoxia <dev@eoxia.com>
 * @since 1.0.0
 * @version 1.6.0
 * @copyright 2015-2018 Eoxia
 * @package Task_Manager
 */

namespace task_manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestion des tâches.
 */
class Task_Class extends \eoxia\Post_Class {

	/**
	 * Toutes les couleurs disponibles pour une t$ache
	 *
	 * @var array
	 */
	public $colors = array(
		'white',
		'red',
		'yellow',
		'green',
		'blue',
		'purple',
	);

	/**
	 * Le nom du modèle
	 *
	 * @var string
	 */
	protected $model_name = '\task_manager\Task_Model';

	/**
	 * Le post type
	 *
	 * @var string
	 */
	protected $type = 'wpeo-task';

	/**
	 * La clé principale du modèle
	 *
	 * @var string
	 */
	protected $meta_key = 'wpeo_task';

	/**
	 * La route pour accéder à l'objet dans la rest API
	 *
	 * @var string
	 */
	protected $base = 'task';

	/**
	 * La version de l'objet
	 *
	 * @var string
	 */
	protected $version = '0.1';

	/**
	 * La taxonomy lié à ce post type.
	 *
	 * @var string
	 */
	protected $attached_taxonomy_type = 'wpeo_tag';

	/**
	 * Permet d'ajouter le post_status 'archive'.
	 *
	 * @since 1.0.0
	 * @version 1.0.0
	 *
	 * @param array   $args   Les paramètres à appliquer pour la récupération @see https://codex.wordpress.org/Function_Reference/WP_Query.
	 * @param boolean $single Si on veut récupérer un tableau, ou qu'une seule entrée.
	 *
	 * @return Object
	 */
	public function get( $args = array(), $single = false ) {
		$array_posts = array();

		// Définition des arguments par défaut pour la récupération des "posts".
		$default_args = array(
			'post_status'    => array(
				'any',
				'archive',
			),
			'post_type'      => $this->get_type(),
			'posts_per_page' => -1,
		);

		$final_args = wp_parse_args( $args, $default_args );

		return parent::get( $final_args, $single );
	}

	/**
	 * Récupères les tâches.
	 *
	 * @since 1.0.0
	 * @version 1.5.0
	 *
	 * @param array $param {
	 *                      Les propriétés du tableau.
	 *
	 *                      @type integer $id(optional)              L'ID de la tâche.
	 *                      @type integer $offset(optional)          Sautes x tâches.
	 *                      @type integer $posts_per_page(optional)  Le nombre de tâche.
	 *                      @type array   $users_id(optional)        Un tableau contenant l'ID des utilisateurs.
	 *                      @type array   $categories_id(optional)   Un tableau contenant le TERM_ID des categories.
	 *                      @type string  $status(optional)          Le status des tâches.
	 *                      @type integer $post_parent(optional)     L'ID du post parent.
	 *                      @type string  $term(optional)            Le terme pour rechercher une tâche.
	 * }.
	 * @return array        La liste des tâches trouvées.
	 */
	public function get_tasks( $param ) {
		global $wpdb;

		$param['id']             = isset( $param['id'] ) ? (int) $param['id'] : 0;
		$param['task_id']       = isset( $param['task_id'] ) ? (int) $param['task_id'] : 0;
		$param['point_id']       = isset( $param['point_id'] ) ? (int) $param['point_id'] : 0;
		$param['offset']         = ! empty( $param['offset'] ) ? (int) $param['offset'] : 0;
		$param['posts_per_page'] = ! empty( $param['posts_per_page'] ) ? (int) $param['posts_per_page'] : -1;
		$param['users_id']       = ! empty( $param['users_id'] ) ? (array) $param['users_id'] : array();
		$param['categories_id']  = ! empty( $param['categories_id'] ) ? (array) $param['categories_id'] : array();
		$param['status']         = ! empty( $param['status'] ) ? sanitize_text_field( $param['status'] ) : 'any';
		$param['post_parent']    = ! empty( $param['post_parent'] ) ? (array) $param['post_parent'] : null;
		$param['term']           = ! empty( $param['term'] ) ? sanitize_text_field( $param['term'] ) : '';

		$tasks    = array();
		$tasks_id = array();

		if ( ! empty( $param['status'] ) ) {
			if ( 'any' === $param['status'] ) {
				$param['status'] = '"publish","pending","draft","future","private","inherit"';
			} else {
				// Ajout des apostrophes.
				$param['status'] = '"' . $param['status'] . '"';

				// Entre chaque virgule.
				$param['status'] = str_replace( ',', '","', $param['status'] );
			}
		}

		$param = apply_filters( 'task_manager_get_tasks_args', $param );

		$point_type = Point_Class::g()->get_type();

		$comment_type = Task_Comment_Class::g()->get_type();

		$query = "SELECT DISTINCT TASK.ID FROM {$wpdb->posts} AS TASK
			LEFT JOIN {$wpdb->comments} AS POINT ON POINT.comment_post_id=TASK.ID AND POINT.comment_approved = 1 AND POINT.comment_type = '{$point_type}'
			LEFT JOIN {$wpdb->comments} AS COMMENT ON COMMENT.comment_parent=POINT.comment_id AND COMMENT.comment_approved = 1 AND POINT.comment_approved = 1 AND COMMENT.comment_type = '{$comment_type}'
			LEFT JOIN {$wpdb->postmeta} AS TASK_META ON TASK_META.post_id=TASK.ID AND TASK_META.meta_key='wpeo_task'
			LEFT JOIN {$wpdb->term_relationships} AS CAT ON CAT.object_id=TASK.ID
		WHERE TASK.post_type='wpeo-task'";

		$query .= 'AND TASK.post_status IN (' . $param['status'] . ')';

		if ( ! is_null( $param['post_parent'] ) ) {
			$query .= 'AND TASK.post_parent IN (' . implode( $param['post_parent'], ',' ) . ')';
		}

		if ( ! empty( $param['users_id'] ) ) {
			$query .= "AND (
				(
					TASK_META.meta_value REGEXP '{\"user_info\":{\"owner_id\":" . implode( $param['users_id'], '|' ) . ",'
				) OR (
					TASK_META.meta_value LIKE '%affected_id\":[" . implode( $param['users_id'], '|' ) . "]%'
				) OR (
					TASK_META.meta_value LIKE '%affected_id\":[" . implode( $param['users_id'], '|' ) . ",%'
				) OR (
					TASK_META.meta_value REGEXP 'affected_id\":\\[[0-9,]+" . implode( $param['users_id'], '|' ) . "\\]'
				) OR (
					TASK_META.meta_value REGEXP 'affected_id\":\\[[0-9,]+" . implode( $param['users_id'], '|' ) . "[0-9,]+\\]'
				)
			)";
		}

		if ( ! empty( $param['categories_id'] ) ) {
			$sub_query = '   ';
			foreach ( $param['categories_id'] as $cat_id ) {
				$sub_query .= '(CAT.term_taxonomy_id=' . $cat_id . ') OR';
			}

			$sub_query = substr( $sub_query, 0, -3 );
			if ( ! empty( $sub_query ) ) {
				$query .= "AND ({$sub_query})";
			}
		}

		$sub_where = '';

		if ( ! empty( $param['term'] ) ) {
			$sub_where = "
				(
					TASK.ID LIKE '%" . $param['term'] . "%' OR TASK.post_title LIKE '%" . $param['term'] . "%'
				) OR (
					POINT.comment_id LIKE '%" . $param['term'] . "%' OR POINT.comment_content LIKE '%" . $param['term'] . "%'
				) OR (
					COMMENT.comment_parent != 0 AND (COMMENT.comment_id LIKE '%" . $param['term'] . "%' OR COMMENT.comment_content LIKE '%" . $param['term'] . "%')
				)";
		}

		if ( $param['task_id'] ) {
			if ( ! empty( $sub_where ) ) {
				$sub_where .= ' OR (TASK.ID = ' . $param['task_id'] . ')';
			} else {
				$sub_where .= ' (TASK.ID = ' . $param['task_id'] . ')';
			}
		}

		if ( $param['point_id'] ) {
			if ( ! empty( $sub_where ) ) {
				$sub_where .= ' OR (POINT.comment_id = ' . $param['point_id'] . ')';
			} else {
				$sub_where .= ' (POINT.comment_id = ' . $param['point_id'] . ')';
			}
		}

		if ( ! empty( $sub_where ) ) {
			$query .= ' AND (' . $sub_where . ')';
		}

		$query .= ' ORDER BY TASK.post_date DESC ';

		if ( -1 !== $param['posts_per_page'] ) {
			$query .= 'LIMIT ' . $param['offset'] . ',' . $param['posts_per_page'];
		}

		$tasks_id = $wpdb->get_col( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! empty( $tasks_id ) ) {
			$tasks = self::g()->get(
				array(
					'post__in'    => $tasks_id,
					'post_status' => $param['status'],
				)
			);
		} // End if().

		return $tasks;
	}

	/**
	 * Charges les tâches, et fait le rendu.
	 *
	 * @param array $tasks    La liste des tâches qu'il faut afficher.
	 * @param bool  $frontend L'affichage aura t il lieu dans le front ou le back.
	 *
	 * @return void
	 *
	 * @since 1.3.6
	 * @version 1.6.0
	 *
	 * @todo: With_wrapper ?
	 */
	public function display_tasks( $tasks, $frontend = false ) {
		if ( $frontend ) {
			\eoxia\View_Util::exec(
				'task-manager',
				'task',
				'frontend/tasks',
				array(
					'tasks'        => $tasks,
					'with_wrapper' => false,
				)
			);
		} else {
			\eoxia\View_Util::exec(
				'task-manager',
				'task',
				'backend/tasks',
				array(
					'tasks'        => $tasks,
					'with_wrapper' => false,
				)
			);
		}
	}

	/**
	 * Fait le rendu de la metabox
	 *
	 * @param  WP_Post $post les données du post.
	 * @return void
	 *
	 * @since 1.0.0
	 * @version 1.6.0
	 */
	public function callback_render_metabox( $post ) {
		$parent_id = $post->ID;
		$user_id   = $post->post_author;

		$tasks                = array();
		$task_ids_for_history = array();
		$total_time_elapsed   = 0;
		$total_time_estimated = 0;

		// Affichage des tâches de l'élément sur lequel on se trouve.
		$tasks[ $post->ID ]['title'] = '';
		$tasks[ $post->ID ]['data']  = self::g()->get_tasks(
			array(
				'post_parent' => $post->ID,
				'status'      => 'publish,pending,draft,future,private,inherit,archive',
			)
		);

		if ( ! empty( $tasks[ $post->ID ]['data'] ) ) {
			foreach ( $tasks[ $post->ID ]['data'] as $task ) {
				if ( empty( $tasks[ $post->ID ]['total_time_elapsed'] ) ) {
					$tasks[ $post->ID ]['total_time_elapsed'] = 0;
				}

				$tasks[ $post->ID ]['total_time_elapsed'] += $task->data['time_info']['elapsed'];
				$total_time_elapsed                       += $task->data['time_info']['elapsed'];
				$total_time_estimated                     += $task->data['last_history_time']->data['estimated_time'];

				$task_ids_for_history[] = $task->data['id'];
			}
		}

		// Récupération des enfants de l'élément sur lequel on se trouve.
		$args     = array(
			'post_parent' => $post->ID,
			'post_type'   => \eoxia\Config_Util::$init['task-manager']->associate_post_type,
			'numberposts' => -1,
			'post_status' => 'any',
		);
		$children = get_posts( $args );

		if ( ! empty( $children ) ) {
			foreach ( $children as $child ) {
				/* Translators: Titre du post sur lequel on veut afficher les tâches. */
				$tasks[ $child->ID ]['title'] = sprintf( __( 'Task for %1$s', 'task-manager' ), $child->post_title );
				$tasks[ $child->ID ]['data']  = self::g()->get_tasks(
					array(
						'post_parent' => $child->ID,
						'status'      => 'publish,pending,draft,future,private,inherit,archive',
					)
				);

				if ( empty( $tasks[ $child->ID ]['data'] ) ) {
					unset( $tasks[ $child->ID ] );
				}

				if ( ! empty( $tasks[ $child->ID ]['data'] ) ) {
					foreach ( $tasks[ $child->ID ]['data'] as $task ) {
						if ( empty( $tasks[ $child->ID ]['total_time_elapsed'] ) ) {
							$tasks[ $child->ID ]['total_time_elapsed'] = 0;
						}
						$tasks[ $child->ID ]['total_time_elapsed'] += $task->data['time_info']['elapsed'];
						$total_time_elapsed                        += $task->data['time_info']['elapsed'];
						$total_time_estimated                      += $task->data['last_history_time']->data['estimated_time'];

						$task_ids_for_history[] = $task->data['id'];
					}
				}
			}
		}

		$total_time_elapsed   = \eoxia\Date_Util::g()->convert_to_custom_hours( $total_time_elapsed );
		$total_time_estimated = \eoxia\Date_Util::g()->convert_to_custom_hours( $total_time_estimated );

		\eoxia\View_Util::exec(
			'task-manager',
			'task',
			'backend/metabox-posts',
			array(
				'post'                 => $post,
				'tasks'                => $tasks,
				'task_ids_for_history' => implode( ',', $task_ids_for_history ),
				'total_time_elapsed'   => $total_time_elapsed,
				'total_time_estimated' => $total_time_estimated,
			)
		);
	}

	/**
	 * Historique de la metabox
	 *
	 * @param  [type] $post [description].
	 * @return void
	 */
	public function callback_render_history_metabox( $post ) {
		$tasks_id = array();

		$tasks = self::g()->get_tasks(
			array(
				'post_parent' => $post->ID,
				'status'      => 'publish,pending,draft,future,private,inherit,archive',
			)
		);

		if ( ! empty( $tasks ) ) {
			foreach ( $tasks as $task ) {
				$tasks_id[] = $task->data['id'];
			}
		}

		$args     = array(
			'post_parent' => $post->ID,
			'post_type'   => \eoxia\Config_Util::$init['task-manager']->associate_post_type,
			'numberposts' => -1,
			'post_status' => 'any',
		);
		$children = get_posts( $args );

		if ( ! empty( $children ) ) {
			foreach ( $children as $child ) {
				$tasks[ $child->ID ]['data'] = self::g()->get_tasks(
					array(
						'post_parent' => $child->ID,
						'status'      => 'publish,pending,draft,future,private,inherit,archive',
					)
				);

				if ( empty( $tasks[ $child->ID ]['data'] ) ) {
					unset( $tasks[ $child->ID ] );
				}

				if ( ! empty( $tasks[ $post->ID ] ) ) {
					foreach ( $tasks[ $post->ID ]['data'] as $task ) {
						$tasks_id[] = $task->data['id'];
					}
				}
			}
		}

		$date_end   = current_time( 'Y-m-d' );
		$date_start = date( 'Y-m-d', strtotime( '-1 month', strtotime( $date_end ) ) );

		if ( ! empty( $tasks_id ) ) {
			$datas = Activity_Class::g()->get_activity( $tasks_id, 0, $date_start, $date_end );
		}

		if ( ! empty( $tasks_id ) ) {
			\eoxia\View_Util::exec(
				'task-manager',
				'activity',
				'backend/post-last-activity',
				array(
					'datas'      => $datas,
					'date_start' => $date_start,
					'date_end'   => $date_end,
					'tasks_id'   => implode( ',', $tasks_id ),
				)
			);
		}
	}

	public function all_month_between_two_dates( $date_start, $date_end ){ // premiers mois EXLCUS et denier mois INCLUS
		$dates   = array();
		$current = $date_start;
		$last    = $date_end;

		$temp_month = '';
		$all_month_in_year = array();

		while ( $current <= $last ) {
			if ( date( 'm', $current ) != $temp_month ) {

				$temp_month = date( 'm', $current );

				$all_month_in_year[ count( $all_month_in_year ) ] = array(
					'month' => date( 'm', $current ),
					'year'  => date( 'Y', $current ),
					'name_month' => date_i18n("F", $current ),
					'str_month_start' => strtotime( date( 'd-m-Y', $current ) ),
					'str_month_end' => strtotime( date( 't-m-Y', $current ) ) + 86340,
					'total_time_elapsed' => 0,
					'total_time_estimated' => 0
				);
			}

			// on recupère le dernier mois en ENTIER
			//$all_month_in_year[ count( $all_month_in_year ) - 1 ][ 'str_month'] = strtotime( date( 't-m-Y', $all_month_in_year[ count( $all_month_in_year ) - 1 ][ 'str_month' ] ) );

			$dates[] = date( 'd/m/Y', $current );
			$current = strtotime( '+1 day', $current );

		}
		$all_month_in_year = array_slice( $all_month_in_year, 1 );

		return $all_month_in_year;
	}

	public function update_client_indicator( $postid, $postauthor, $year ){
		if( ! $year || $year > date("Y") ){
				$year = date("Y");
		}

		return $this->callback_render_indicator( array(), $postid, $postauthor, $year );
	}

	public function test_func_indicator_client( $tasks, $allmonth, $post_id )
	{
		$str_start = $allmonth[ 0 ][ 'str_month_start' ];
		$str_end = $allmonth[ count( $allmonth ) - 1 ][ 'str_month_end' ];

		$categories_indicator = array();
		if ( empty( $tasks ) )
		{
			return  array();
		}

		foreach ( $tasks as $key => $task ) { // Pour chaque tache
			$task_recursive = false;
			$args = array(
				'post_id' => $task->data[ 'id' ]
			);

			if( ! $str_start < strtotime( $task->data['date_modified'][ 'rendered' ][ 'mysql' ] ) && ! $str_end > strtotime( $task->data['date_modified'][ 'rendered' ][ 'mysql' ] ) ){ // On vérifie que la tache est était modifiée dans l'année
				continue;
			}

			foreach ( $task->data['taxonomy'][ 'wpeo_tag' ] as $key_task => $value_task ) { // Si la tache a plusieurs catégories
				if( empty( $categories_indicator[ $value_task ] ) ) { // On créait la catégorie
					$categories_indicator[ $value_task ] = $allmonth;
					$name_categories = get_term_by( 'id', $value_task, 'wpeo_tag' );

					$categories_indicator[ $value_task ][0][ 'name' ] = $name_categories->name;
					$categories_indicator[ $value_task ][0][ 'id' ]   = $name_categories->term_id;
				}

				if( $task->data['last_history_time']->data['custom'] == 'recursive' ){ // Si la tache est récursive, on ajoute du temps chaque mois
					$task_recursive = true;
					foreach( $categories_indicator[ $value_task ] as $key_categorie => $month ){ // Pour chaque tache, Chaque mois de l'année
						if( strtotime( $task->data['date'][ 'rendered' ][ 'mysql' ] ) < $categories_indicator[ $value_task ][ $key_categorie ][ 'str_month_end' ] ){
							if( strtotime( 'now' ) >= $month[ 'str_month_start' ] ){
								$categories_indicator[ $value_task ][ $key_categorie ][ 'total_time_estimated' ] += $task->data['last_history_time']->data['estimated_time'];
							}
						}
					}
				}else{
					foreach( $categories_indicator[ $value_task ] as $key_categorie => $month ){ // Pour chaque tache, Chaque mois de l'année
						if( $month[ 'str_month_start' ] < strtotime( $task->data['date'][ 'rendered' ][ 'mysql' ] ) && $month[ 'str_month_end' ] > strtotime( $task->data['date'][ 'rendered' ][ 'mysql' ] ) ){
							$categories_indicator[ $value_task ][ $key_categorie ][ 'total_time_elapsed' ] += $task->data['time_info']['elapsed'];
							$categories_indicator[ $value_task ][ $key_categorie ][ 'total_time_estimated' ] += $task->data['last_history_time']->data['estimated_time'];
							break;
						}
					}
				}
				if( ! $task_recursive ){
					continue;
				}

				$comments       = Task_Comment_Class::g()->get_comments( 0, $args );

				foreach ( $comments as $key => $value_com ) { // Pour chaque commentaire de la tache

					foreach( $categories_indicator[ $value_task ] as $key_cat => $month ){ // Pour chaque tache, Chaque mois de l'année
						if( $month[ 'str_month_start' ] < strtotime( $value_com->data[ 'date' ][ 'rendered' ][ 'mysql' ] ) && $month[ 'str_month_end' ] > strtotime( $value_com->data[ 'date' ][ 'rendered' ][ 'mysql' ] ) ){

							$categories_indicator[ $value_task ][ $key_cat ][ 'total_time_elapsed' ] += $value_com->data[ 'time_info' ][ 'elapsed' ];

							break;
						}
					}
				}

			}
		}

		return $categories_indicator;
	}

	public function callback_render_indicator( $post = array(), $post_id = 0, $post_author = 0, $year = 0 ) {

		if( ! empty ( $post ) ){
			$post_id = $post->ID;
			$post_author = $post->post_author;
		}

		if( ! $year ){
			$indicator_date_start = strtotime( "-1 year" );
			$indicator_date_end = strtotime( "now" ) + 3600;
		}else{
			$indicator_date_start = strtotime( '01-01-' . $year ) - 3600;
			$indicator_date_end  = strtotime( '31-12-' . $year );
		}

		$parent_id = $post_id;
		$user_id   = $post_author;

		$tasks = array();

		$tasks[ $post_id ]['title'] = '';
		$tasks[ $post_id ]['data']  = self::g()->get_tasks(
			array(
				'post_parent' => $post_id,
				'status'      => 'publish,pending,draft,future,private,inherit,archive',
			)
		);

		$tasks_indicator = array(); // trie toutes les taches
		$categories_indicator = array(); // tries toutes les taches selon les catégories

		$allmonth_betweendates = $this->all_month_between_two_dates( $indicator_date_start, $indicator_date_end );

		$categories_indicator = $this->test_func_indicator_client( $tasks[ $post_id ]['data'], $allmonth_betweendates, $post_id );
		// echo '<pre>'; print_r( $tasks[ $post_id ] ); echo '</pre>'; exit;


		/*if ( ! empty( $tasks[ $post_id ]['data'] ) ) {
			foreach ( $tasks[ $post_id ]['data'] as $task ) {
				if ( empty( $tasks[ $post_id ]['total_time_elapsed'] ) ) {
					$tasks[ $post_id ]['total_time_elapsed'] = 0;
				}

				$temp_length = count( $tasks_indicator );
				$tasks_indicator[ $temp_length ][ 'total_time_elapsed' ]   = $task->data['time_info']['elapsed'];
				$tasks_indicator[ $temp_length ][ 'estimated_time' ]       =  $task->data['last_history_time']->data['estimated_time'];
				$tasks_indicator[ $temp_length ][ 'monthly_time' ]         =  $task->data['last_history_time']->data['custom'];
				$tasks_indicator[ $temp_length ][ 'date_humain_readable' ] = $task->data['date'][ 'rendered' ][ 'date_time' ];
				$tasks_indicator[ $temp_length ][ 'date_str' ]             = strtotime( $task->data['date'][ 'rendered' ][ 'mysql' ] );
				$tasks_indicator[ $temp_length ][ 'date_str_modified' ]    = strtotime( $task->data['date_modified'][ 'rendered' ][ 'mysql' ] );
				$tasks_indicator[ $temp_length ][ 'categorie' ]            = $task->data['taxonomy'][ 'wpeo_tag' ];

				if( ! empty( $tasks_indicator[ $temp_length ][ 'categorie' ] ) ){
					foreach ($tasks_indicator[ $temp_length ][ 'categorie' ] as $key_task => $value_task ) { // Si la tache a plusieurs catégories
						if( empty( $categories_indicator[ $value_task ] ) ) {
							$categories_indicator[ $value_task ] = $allmonth_betweendates;
							$name_categories = $tag = get_term_by( 'term_taxonomy_id', $value_task, 'wpeo_tag' );
							$categories_indicator[ $value_task ][0][ 'name' ] = $name_categories->name;
							$categories_indicator[ $value_task ][0][ 'id' ]   = $name_categories->term_id;
						}

						foreach( $categories_indicator[ $value_task ] as $key_cat => $month ){ // Pour chaque tache, Chaque mois de l'année
							if( $month[ 'str_month_start' ] < $tasks_indicator[ $temp_length ][ 'date_str' ] && $month[ 'str_month_end' ] > $tasks_indicator[ $temp_length ][ 'date_str' ] ){

								$categories_indicator[ $value_task ][ $key_cat ][ 'total_time_elapsed' ] += $tasks_indicator[ $temp_length ][ 'total_time_elapsed' ];
								$categories_indicator[ $value_task ][ $key_cat ][ 'total_time_estimated' ] += $tasks_indicator[ $temp_length ][ 'estimated_time' ];

								break;
							}
						}
					}
				}
			}
		}*/


foreach ( $categories_indicator as $keycategorie => $valuecategorie ) { // Pour chaque catégories
	$total_estimated = 0;
	$total_elapsed = 0;
	foreach ( $valuecategorie as $keymonth => $valuemonth ) { // Pour chaque mois de cette catégorie
		$total_estimated += $valuemonth[ 'total_time_estimated' ];
		$total_elapsed += $valuemonth[ 'total_time_elapsed' ];
		$categories_indicator[ $keycategorie ][ $keymonth ][ 'purcent_color' ] = '#F1F8E9';

		if( $valuemonth[ 'total_time_estimated' ] != 0 && $valuemonth[ 'total_time_elapsed' ] != 0 ){
			$categories_indicator[ $keycategorie ][ $keymonth ][ 'total_pourcent' ] = intval( $valuemonth[ 'total_time_elapsed' ] / $valuemonth[ 'total_time_estimated' ] * 100 );

			$pourcent_color = $this->return_color_from_pourcent( $categories_indicator[ $keycategorie ][ $keymonth ][ 'total_pourcent' ] );



			$categories_indicator[ $keycategorie ][ $keymonth ][ 'purcent_color' ] = $pourcent_color;
			$categories_indicator[ $keycategorie ][ $keymonth ][ 'total_time_estimated_readable' ] = $this->change_minute_time_to_readabledate( $valuemonth[ 'total_time_estimated' ] );
			$categories_indicator[ $keycategorie ][ $keymonth ][ 'total_time_elapsed_readable' ] = $this->change_minute_time_to_readabledate( $valuemonth[ 'total_time_elapsed' ] );

		}
	}

	$categories_indicator[ $keycategorie ][0][ 'all_month_estimated'] = $total_estimated;
	$categories_indicator[ $keycategorie ][0][ 'all_month_estimated_readable'] = $this->change_minute_time_to_readabledate( $total_estimated );
	$categories_indicator[ $keycategorie ][0][ 'all_month_elapsed'] = $total_elapsed;
	$categories_indicator[ $keycategorie ][0][ 'all_month_elapsed_readable'] = $this->change_minute_time_to_readabledate( $total_elapsed );
	if( $total_elapsed > 0 && $total_estimated > 0 ){
		$categories_indicator[ $keycategorie ][0][ 'all_month_pourcent'] = intval( $total_elapsed / $total_estimated * 100 );
	}else{
		$categories_indicator[ $keycategorie ][0][ 'all_month_pourcent'] = 0;
	}
	$categories_indicator[ $keycategorie ][0][ 'all_month_pourcent_color'] = $this->return_color_from_pourcent( $categories_indicator[ $keycategorie ][0][ 'all_month_pourcent'] );

}


		if( $year ){
			$data_return = array(
				'year' => $year,
				'categories' => $categories_indicator,
				'everymonth' => $allmonth_betweendates
			);
			return $data_return;
		}

		\eoxia\View_Util::exec(
			'task-manager',
			'task',
			'backend/metabox-indicators',
			array(
				'categories' => $categories_indicator,
				'everymonth' => $allmonth_betweendates
			)
		);
	}

	public function return_color_from_pourcent( $pourcent ){
		switch ( $pourcent ){
			case $pourcent <= 0:
				$color = "#F1F8E9";break;

			case $pourcent <= 50:
				$color = "#CCFF90";break;

			case $pourcent <= 75:
				$color = "#B2FF59";break;

			case $pourcent <= 100;
			$color = "#64DD17"; break;

			case $pourcent <= 150:
				$color = "#FF5722";break;

			default :
				$color = "#DD2C00";
			break;
		}

		return $color;
	}

	public function change_minute_time_to_readabledate( $minute_format ){

		$d = floor ( $minute_format / 1440 );
		$h = floor ( ( $minute_format - $d * 1440 ) / 60 );
		$m = $minute_format - ( $d * 1440 ) - ( $h * 60 );

		return $d . 'j ' . $h . 'h ' . $m . 'm';
	}
}

Task_Class::g();
