<?php
/**
 * Méthodes utilitaires pour les fichiers CSV.
 *
 * @author Jimmy Latour <dev@eoxia.com>
 * @since 0.1.0
 * @version 0.5.0
 * @copyright 2015-2017 Eoxia
 * @package WPEO_Util
 */

namespace eoxia;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\eoxia\CSV_Util' ) ) {
	/**
	 * Gestion des fichiers CSV
	 */
	class CSV_Util extends \eoxia\Singleton_Util {
		/**
		 * Le constructeur obligatoirement pour utiliser la classe \eoxia\Singleton_Util
		 *
		 * @return void nothing
		 */
		protected function construct() {}

		/**
		 * Lit un fichier CSV et forme un tableau 2D selon $list_index
		 *
		 * @param string $csv_path   Le chemin vers le fichier .csv.
		 * @param array  $list_index Les index personnalisés.
		 * @return array 						 Le tableau 2D avec les données du csv
		 */
		public function read_and_set_index( $csv_path, $list_index = array() ) {
			if ( empty( $csv_path ) ) {
				return false;
			}

			$data = array();
			$csv_content = file( $csv_path );
			if ( ! empty( $csv_content ) ) {
				foreach ( $csv_content as $key => $line ) {
					if ( 0 !== $key ) {
						$data[ $key ] = str_getcsv( $line );
						foreach ( $data[ $key ] as $i => $entry ) {
							if ( ! empty( $list_index[ $i ] ) ) {
								$data[ $key ][ $list_index[ $i ] ] = $entry;
							}
						}
					}
				}
			}

			return $data;
		}
	}
} // End if().
