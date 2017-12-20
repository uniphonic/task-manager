<?php
/**
 * Gestion des temps rapides.
 *
 * @author Jimmy Latour <jimmy.eoxia@gmail.com>
 * @since 1.6.0
 * @version 1.6.0
 * @copyright 2015-2017 Eoxia
 * @package Task_Manager
 */

namespace task_manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gestion des temps rapides.
 */
class Quick_Time_Class extends \eoxia\Singleton_Util {

	/**
	 * Constructeur obligatoire pour Singleton_Util
	 *
	 * @since 1.6.0
	 * @version 1.6.0
	 *
	 * @return void
	 */
	protected function construct() {}

	/**
	 * Appel la vue principale de la metabox "temps rapides".
	 *
	 * @since 1.6.0
	 * @version 1.6.0
	 *
	 * @return void
	 */
	public function display() {
		$quicktimes = $this->get_quicktimes();
		sort( $quicktimes );

		$comment_schema = Task_Comment_Class::g()->get( array(
			'schema' => true,
		), true );

		\eoxia\View_Util::exec( 'task-manager', 'quick_time', 'backend/main', array(
			'quicktimes'     => $quicktimes,
			'comment_schema' => $comment_schema,
		) );
	}

	/**
	 * Récupères les templates des temps rapides.
	 *
	 * @since 1.6.0
	 * @version 1.6.0
	 *
	 * @return array (Voir au dessus)
	 */
	public function get_quicktimes() {
		$quicktimes = get_user_meta( get_current_user_id(), \eoxia\Config_Util::$init['task-manager']->quick_time->meta_quick_time, true );
		if ( ! empty( $quicktimes ) ) {
			foreach ( $quicktimes as $key => $quicktime ) {
				$quicktimes[ $key ] = quicktime_format_data( $quicktime );
			}
		}

		return $quicktimes;
	}

}

Quick_Time_Class::g();
