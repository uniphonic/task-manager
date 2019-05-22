<?php
/**
 * La vue principale des tâches dans le backend.
 *
 * @author Eoxia <dev@eoxia.com>
 * @since 1.0.0.0
 * @version 1.3.6.0
 * @copyright 2015-2017 Eoxia
 * @package task
 * @subpackage view
 */

namespace task_manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
	?>

		<div class="wpeo-tag">
			<label>
				<i class="fas fa-search"></i>
				<input type="hidden" class="task_search-taxonomy" name="parent_id"/>
				<input type="text" class="tm_task_autocomplete_parent" placeholder="<?php echo esc_html( 'Search ... ', 'task-manager') ?>" />
			</label>
			<ul style="background-color: #ececec; cursor: pointer; position: absolute; opacity: 1; z-index: 5; max-height: 300px; overflow-y: scroll; min-width: 300px; margin-top: 5px;">
				<?php foreach( $data as $key => $typepost_type ): ?>
					<li style="font-size: 18px; background-color: #A9A9A9; padding: 0.6em 2.6em;">
						<?php $name_posttype = get_post_type_object( $key ); ?>
						<?php echo esc_html( $name_posttype->label ); ?>
					</li>
					<?php foreach( $typepost_type as $key_post => $posttype ): ?>
						<li class="tm_list_parent_li_element" data-id="<?php echo esc_html( $posttype['id'] ); ?>" style="font-size: 15px; padding: 0.6em 2.6em;">
							<?php echo esc_html( $posttype['value'] ); ?>
						</li>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</ul>
		</div>