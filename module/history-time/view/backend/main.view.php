<?php
/**
 * Affiches l'historique des 'dates estimées'.
 * Affiches le formulaire pour ajouter une date estimé.
 *
 * @author Jimmy Latour <dev@eoxia.com>
 * @since 1.0.0
 * @version 1.6.0
 * @copyright 2015-2018 Eoxia
 * @package Task_Manager
 */

namespace task_manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<div class="history-time-container">

	<div class="history-time-new">
		<div class="wpeo-grid grid-2 wpeo-form">
			<input type="hidden" name="action" value="create_history_time" />
			<input type="hidden" name="task_id" value="<?php echo esc_attr( $task_id ); ?>" />
			<?php wp_nonce_field( 'create_history_time' ); ?>

			<div>
				<div class="form-element">
					<label><?php esc_html_e( 'Estimated time (min)', 'task-manager' ); ?> </label>
					<input name="estimated_time" type="text" />
				</div>
			</div>

			<div>
				<div class="form-element">
					<input name="repeat" id="repeat" type="checkbox" />
					<label for="repeat"><?php esc_html_e( 'Repeat monthly', 'task-manager' ); ?></label>
				</div>

				<div class="group-date form-element">
					<label><i class="dashicons dashicons-calendar-alt"></i><?php esc_html_e( 'Due date', 'task-manager' ); ?> </label>
					<input type="text" class="mysql-date" style="width: 0px; padding: 0px; border: none;" name="due_date" value="<?php echo esc_attr( current_time( 'mysql' ) ); ?>" />
					<input class="date" type="text" />
				</div>


				<div aria-label="<?php echo esc_attr_e( 'Create new', 'task-manager' ); ?>"
						data-parent="history-time-new"
						class="wpeo-tooltip-event action-input wpeo-button button-blue">
					<span><?php echo esc_html_e( 'Add new', 'task-manager' ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<h2><?php esc_html_e( 'Event historic', 'task-manager' ); ?></h2>

	<ul class="history-time-list">
		<?php
		if ( ! empty( $history_times ) ) :
			foreach ( $history_times as $history_time ) :
				if ( ! empty( $history_time->id ) ) :
					\eoxia\View_Util::exec( 'task-manager', 'history-time', 'backend/history-time', array(
						'history_time' => $history_time,
					) );
				endif;
			endforeach;
		else :
			?>
			<li><?php esc_html_e( 'No history time for now', 'task-manager' ); ?></li>
			<?php
		endif;
		?>
	</ul>
</div>
