<?php
/**
 * Affichage des charts des utilisateurs selon un lapse de temps préfédini
 *
 * @author Corentin-Eoxia <dev@eoxia.com>
 * @since 1.5.0
 * @version 1.6.0
 * @copyright 2015-2018 Eoxia
 * @package Task_Manager
 */

namespace task_manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form>


	<div class="wpeo-form">
	  <div class="wpeo-grid grid-5">
	  	<div class="form-element grid-1">
	  		<span class="form-label"><i class="fas fa-calendar"></i> <?php esc_html_e( 'Start date', 'task-manager' ); ?></span>
	  		<label class="form-field-container">
	  			<input type="date" class="form-field" placeholder="Date de début" value="<?php echo esc_attr( $date_start ); ?>" name="tm_indicator_date_start" />
	  		</label>
	  	</div>

	  	<div class="form-element grid-1">
	  		<span class="form-label"><i class="fas fa-calendar"></i> <?php esc_html_e( 'End date', 'task-manager' ); ?></span>
	  		<label class="form-field-container">
	  			<input type="date" class="form-field" value="<?php echo esc_attr( $date_end ); ?>" name="tm_indicator_date_end" />
	  		</label>
	  	</div>
			<div class="form-element grid-1" style='margin-top : 41px'>
				<button class="wpeo-button button-radius-3 action-input"
					data-time=''
					data-parent="wpeo-form"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'validate_indicator' ) ); ?>"
					data-action="validate_indicator">

					<?php esc_html_e( 'Validate', 'task-manager' ); ?>
				</button> </div>
			<div class="form-element grid-2">
				<?php

				\eoxia\View_Util::exec( 'task-manager', 'indicator', 'backend-indicator/indicator-follower-admin', array(
					'followers' => $followers,
				) );

				?>
			</div>
	  </div>
		<div>
			<br>
			<button class="wpeo-button button-radius-3 action-input button-red"
			  data-time='day'
				data-parent="wpeo-form"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'validate_indicator' ) ); ?>"
				data-action="validate_indicator">

				<?php esc_html_e( 'Day', 'task-manager' ); ?>
			</button>
			<button class="wpeo-button button-radius-3 action-input button-blue"
				data-time='week'
				data-parent="wpeo-form"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'validate_indicator' ) ); ?>"
				data-action="validate_indicator">

				<?php esc_html_e( 'Week', 'task-manager' ); ?>
			</button>
			<button class="wpeo-button button-radius-3 action-input button-yellow"
				data-time='month'
				data-parent="wpeo-form"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'validate_indicator' ) ); ?>"
				data-action="validate_indicator">

				<?php esc_html_e( 'Month', 'task-manager' ); ?>
			</button>
			<input type="hidden" name="list_follower" id="tm_indicator_list_followers" value="">

			<div id="tm_indicator_chart_display" style="float : right; display : none" data-chart-display="horizontalBar">
				<div id="tm_indicator_chart_bar" class="wpeo-button button-dark button-square-40 button-rounded clickontypechart" data-chart-type='bar'>
					<i class="far fa-chart-bar"></i>
				</div>
				<div id="tm_indicator_chart_horizontalBar" class="wpeo-button button-disabled button-dark button-square-40 button-rounded clickontypechart"  data-chart-type='horizontalBar'>
					<i class="far fa-align-left"></i>
				</div>
			</div>

		</div>
	</div>
</form>
<br>


<div id='displaycanvas'>

</div>
<div id='displaycanvas_specific_week'>

</div>



<div id='information_canvas' style='display : none'></div>
<div id='tm_redirect_settings_user' style='display : none'>
	<a target="_blank" href="<?php echo esc_attr( admin_url( 'profile.php' ) ); ?>">
		<?php esc_html_e( 'Change your settings here', 'task-manager'); ?>
	</a>
</div>
