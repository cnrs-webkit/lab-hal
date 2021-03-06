<?php
/**
 * Plugin lab-hal
 *
 * @package     lab-hal
 * @since       0.0
 * @author      Christophe Seguinot
 * @link        https://github.com/cnrs-webkit/lab-hal
 * @license     GPL-3.0
 *
 * Plugin Name:  LAB-HAL
 * Plugin URI:   https://github.com/cnrs-webkit/lab-hal
 * Description:  Ce plugin est une adaptation du plugin wp-HAL de Baptiste Blondelle . Il Crée une page qui remonte les publications d'un auteur ou d'une structure en relation avec HAL et un widget des dernières publications d'un auteur ou d'une structure.
 * Version:      0.6
 * Author:       Christophe Seguinot
 * License:      http://www.gnu.org/licenses/gpl-3.0.html
 * Contributors: Baptiste Blondelle (wp-HAL), Emmanuel Leguy (partie Angular Js glanée sur le site https://www.cristal.univ-lille.fr)
 * Author URI:   christophe.seguinot@univ-lille.fr
 * Text Domain:  lab-hal
 * Domain Path:  /languages
 * GitHub Plugin URI: https://github.com/cnrs-webkit/lab-hal
 */

function cnrswebkit_unregister_some_post_type() {
    if (headers_sent()) {
        die("Redirect failed. Please click on this link: <a href=...>");
    }
    
    // unregister_post_type( 'contact' );
}

// Pods use default priority 10, higher must be used here
// TODO add_action('init','cnrswebkit_unregister_some_post_type', 20, 0);
add_action('registered_taxonomy','cnrswebkit_unregister_some_post_type', 20, 0);


// Constante de Version.
define( 'LAB_HAL_VERSION', '0.6' );

// Constante pour l'api utilisé par le widget.
define( 'LAB_HAL_API', 'http://api.archives-ouvertes.fr/search/hal/' );

// Constante pour le tri par date utilisé par le widget.
define( 'LAB_HAL_PRODUCEDDATEY', rawurlencode( 'producedDateY_i desc' ) );

// lab-hal plugin directory.
define( 'LAB_HAL_URI', plugin_dir_url( __FILE__ ) );
define( 'LAB_HAL_DIR', dirname( __FILE__ ) );

if ( is_admin() ) {
	// register_activation_hook must be called whitin the main plugin __FILE__ because folder is lab-hal + unkown suffix!
	register_activation_hook( __FILE__, 'lab_hal_rename_on_activation' );
	require_once LAB_HAL_DIR . '/inc/lab-hal-admin.php';
} else {
	require_once LAB_HAL_DIR . '/inc/lab-hal-frontend.php';
}

/**
 * Ajoute le widget lab_hal à l'initialisation des widgets
 */

/*
 * TODO Test this widget before activation
 * require_once(LAB_HAL_DIR . '/class-lab-hal-widget.php' );
 * add_action('widgets_init','lab_hal_init');.
 *
 */

/**
 * Initialise le nouveau widget temporay unactivated!
 */

/*
 *
function lab_hal_init() {
	register_widget( 'Lab_Hal_Widget' );
}
*/

// Traduction de la description.
__( "Crée une page qui remonte les publications d'un auteur ou d'une structure en relation avec HAL et un widget des dernières publications d'un auteur ou d'une structure.", 'lab-hal' );

if ( get_locale() === 'fr_FR' ) {
	define( 'LAB_HAL_LANG', 'fr' );
} elseif ( get_locale() === 'es_ES' ) {
	define( 'LAB_HAL_LANG', 'es' );
} else {
	define( 'LAB_HAL_LANG', 'en' );
}

/**
 * Add 'extractall' query var to $vars
 *
 * @param array $vars : array of query vars.
 *
 * @return $vars
 */
function add_query_vars_filter( $vars ) {
	$vars[] = 'extractall';
	return $vars;
}

add_filter( 'query_vars', 'add_query_vars_filter' );

/**
 * Load one of the existing language translation (FR, ES), or GB english as a default
 */
function lab_hal_load_language() {
	// TODO languages not implemented yet!
	// load_plugin_textdomain( 'lab-hal', false, dirname( plugin_basename( __FILE__ ) ) . '/LAB_HAL_LANG/' );
}

add_action( 'plugins_loaded', 'lab_hal_load_language' );
