<?php if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'wpeo_util' ) ) {
	class wpeo_util {
		public static $array_exclude_module = array( 'timeline' );

		/**
		 * CORE - Install all extra-modules in "Core/Module" folder
		 */
		public static function install_in( $folder ) {
			/** Define the directory containing all exrta-modules for current plugin    */
			$module_folder = WPEO_TASKMANAGER_PATH . $folder . '/';

			/**  Check if the defined directory exists for reading and including the different modules   */
			if( is_dir( $module_folder ) ) {
				$parent_folder_content = scandir( $module_folder );
				foreach ( $parent_folder_content as $folder ) {
					if ( $folder && substr( $folder, 0, 1) != '.' && !in_array( $folder, self::$array_exclude_module ) ) {
						if( is_dir ( $module_folder . $folder ) )
							$child_folder_content = scandir( $module_folder . $folder );

						if ( file_exists( $module_folder . $folder . '/' . $folder . '.php') ) {
							$f =  $module_folder . $folder . '/' . $folder . '.php';
							include( $f );
						}
					}
				}
			}
		}

		public static function install_module( $path_to_module ) {
			$module_name = $path_to_module . '.php';
			$path_to_module = WPEO_TASKMANAGER_PATH . 'core/' . $path_to_module;

			if( file_exists( $path_to_module . '/' . $module_name ) ) {
				include(  $path_to_module . '/' . $module_name );
			}
		}

		public static function convert_to_hours_minut( $time, $format = '%02d:%02d' ) {
			if ( $time < 1 ) {
				return '00:00';
			}
			$hours = floor( $time / 60 );
			$minutes = ( $time % 60 );
			return sprintf( $format, $hours, $minutes );
		}

		public static function convert_to_minut( $time ) {
			$time = explode( ':', $time );

			if( count( $time ) != 2 )
				return 0;

			$final_time = $time[0] * 60;
			$final_time+= $time[1];

			return $final_time;
		}
	}
}
