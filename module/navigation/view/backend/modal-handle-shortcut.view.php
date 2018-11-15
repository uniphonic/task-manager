<?php
/**
 * Gestion des raccourcis.
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2015-2018 Eoxia <dev@eoxia.com>.
 *
 * @license   GPLv3 <https://spdx.org/licenses/GPL-3.0-or-later.html>
 *
 * @package   EO_Framework\EO_Search\Template
 *
 * @since     1.8.0
 */

namespace task_manager;

defined( 'ABSPATH' ) || exit; ?>

<table class="wpeo-table">
    <thead>
        <tr>
            <th data-title="Nom">Nom</th>
            <th data-title="Terme">Terme</th>
            <th data-title="Utilisateur">Utilisateur</th>
            <th data-title="Catégories">Catégories</th>
            <th data-title="Client">Client</th>
            <th data-title="Commande">Commande</th>
            <th data-title="Action">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ( ! empty( $shortcuts['wpeomtm-dashboard'] ) ) :
            foreach ( $shortcuts['wpeomtm-dashboard'] as $key => $shortcut ) :
                ?>
                <tr>
                    <td><?php echo esc_html( $shortcut['label'] ); ?></td>
                    <td>Terme</td>
                    <td>Utilisateur</td>
                    <td>Catégories</td>
                    <td>Client</td>
                    <td>Commande</td>
                    <td>
                        <?php if ( $shortcut['label'] != 'My tasks' && $shortcut['label'] != 'Mes tâches' ) : ?>
                            <div class="action-delete wpeo-button button-progress button-grey button-square-20 button-rounded"
                    			data-action="delete_shortcut"
                    			data-message-delete="<?php echo esc_attr_e( 'Are you sure to delete this shorcut ?', 'task-manager' ); ?>"
                    			data-nonce="<?php echo esc_attr( wp_create_nonce( 'delete_shortcut' ) ); ?>"
                    			data-key="<?php echo esc_attr( $key ); ?>">
                    			<span class="button-icon fa fa-times" aria-hidden="true"></span>
                    		</div>
                        <?php else: ?>
                            <div class="button-disable button-event wpeo-tooltip-event wpeo-button button-progress button-grey button-square-20 button-rounded"
                    			aria-label="<?php esc_attr_e( 'Canno\'t be deleted', 'task-manager' ); ?>">
                    			<span class="button-icon fa fa-times" aria-hidden="true"></span>
                    		</div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php
            endforeach;
        endif;
        ?>
    </tbody>
</table>