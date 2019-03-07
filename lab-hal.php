<?php
/**
 * Plugin lab-hal
 *
 * @package     lab-hal
 * @since       0.0
 * @author      Christophe Seguinot
 * @copyright   2016 Christophe Seguinot
 * @license     GPL-3.0
 *
 * Plugin Name:  LAB-HAL
 * Plugin URI:   https://github.com/cnrs-webkit/lab-hal
 * Description:  Ce plugin est une adaptation du plugin wp-HAL de Baptiste Blondelle . Il Crée une page qui remonte les publications d'un auteur ou d'une structure en relation avec HAL et un widget des dernières publications d'un auteur ou d'une structure.
 * Version:      0.0
 * Author:       Christophe Seguinot
 * License:      http://www.gnu.org/licenses/gpl-3.0.html
 * Contributors: Baptiste Blondelle (wp-HAL), Emmanuel Leguy (partie Angular Js glanée sur le site https://www.cristal.univ-lille.fr)
 * Author URI:   christophe.seguinot@univ-lille.fr
 * Text Domain:  lab-hal
 * Domain Path:  /languages
 * GitHub Plugin URI: https://github.com/cnrs-webkit/lab-hal
 */

// Constante de Version.
define( 'LAB_HAL_VERSION', '0.0' );

// Constante pour l'api utilisé par le widget.
define( 'LAB_HAL_API', 'http://api.archives-ouvertes.fr/search/hal/' );

// Constante pour le tri par date utilisé par le widget.
define( 'LAB_HAL_PRODUCEDDATEY', rawurlencode( 'producedDateY_i desc' ) );

// lab-hal plugin directory
define( 'LAB_HAL_URI',  plugin_dir_url( __FILE__ ) );
define( 'LAB_HAL_DIR',  dirname( __FILE__ ) );

/*
 * Le Plugin lab-HAL sera prochainement mis en ligne (Github?)
 * Ce plugin est en cours de développement, quelques fonctionnalités du plugin original ne sont pas encore implémentées.
 * le widget dernières publications n'a pas été impléménte/testé
 * Merci de remonter les bugs et propositions à l'auteur christophe.seguinot@univ-lille.fr
 */

if ( is_admin() ) {
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
	//TODO languages not implemented yet!
	load_plugin_textdomain( 'lab-hal', false, dirname( plugin_basename( __FILE__ ) ) . '/LAB_HAL_LANG/' );
}

add_action( 'plugins_loaded', 'lab_hal_load_language' );
