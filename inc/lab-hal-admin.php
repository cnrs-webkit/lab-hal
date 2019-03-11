<?php
/**
 * Plugin lab-hal
 *
 * @package     lab-hal
 * @since       0.0
 * @author      Christophe Seguinot
 * @link        https://github.com/cnrs-webkit/lab-hal
 * @license     GPL-3.0
 */


/**
 * Rename on activation.
 *
 * adapted from : https://github.com/afragen/github-updater/src/GitHub_Updater/Init.php
 * Correctly renames the slug when lab-hal is installed
 * via FTP or from plugin upload from Github.
 *
 * `rename()` causes activation to fail.
 *
 * @return void
 */
function lab_hal_rename_on_activation() {
	$plugin_dir = trailingslashit( WP_PLUGIN_DIR );
	$slug       = isset( $_GET['plugin'] ) ? $_GET['plugin'] : false;

	if ( $slug && 'lab-hal/lab-hal.php' !== $slug ) {
		@rename( $plugin_dir . dirname( $slug ), $plugin_dir . 'lab-hal' );
	}
}

/**
 *  Define the upgrader_pre_download callback to stop upgrade if folder is a git or an Eclipse project.
 *
 * @param boolean $reply : reply sent by Wordpress (Whether to bail without returning the package. Default false.).
 * @param string  $package : a plugin/package name.
 * @param object  $instance : the plugin WP_Upgrader instance.
 *
 * @return boolean $reply : always return the incoming $reply parameter
 */
function lab_hal_upgrader_pre_download( $reply, $package, $instance ) {
	if ( strpos( $package, 'lab-hal' ) && (
		file_exists( LAB_HAL_DIR . '/.gitignore' )
		|| file_exists( LAB_HAL_DIR . '/.project' ) ) ) {
				// Cancel this update.
				return new WP_Error( 'update canceled', __( 'Lab-hal plugin update has been canceled because corresponding folder is a git folder and/or an eclipse project', 'lab-hal' ) );
	}

	return $reply;
};

add_filter( 'upgrader_pre_download', 'lab_hal_upgrader_pre_download', 10, 3 );

/**
 * Add a hook to force upgrade of lab-hal-master or lab-hal-tag in lab-hal directory (instead of lab-hal-tags
 * see: https://github.com/YahnisElsts/plugin-update-checker/issues/1
 * Note: this is not used at install, because lab-hal is not yet activated !!
 */
add_filter( 'upgrader_source_selection', 'lab_hal_rename_install_folder', 1, 3);

/**
 * Removes the prefix "-master" or "-tags" when installating from GitHub zip files
 *
 * See: https://github.com/YahnisElsts/plugin-update-checker/issues/1
 *
 * @param string $source
 * @param string $remote_source
 * @param object $thiz
 * @return string
 */
function lab_hal_rename_install_folder( $source, $remote_source, $thiz )
{
	$newsource = trailingslashit( $path_parts['dirname'] ) . trailingslashit( 'lab-hal' );
	$messages[]= array('message' => "Lab-Hal2 install: Source = $source ==> $newsource ",
		'notice-level' => 'notice-info' );
	LAbHalAdminNotices::addNotices( $messages  );
var_dump($source) ;
var_dump($remote_source);
var_dump($thiz);
die('TOTO');
	if(  false === strpos( $source, 'lab-hal') ){
		// Only fired for 'lab-hal'!
		return $source;
	}

	if(  true === is_plugin_active( 'github-updater') && true === is_plugin_active( 'lab-hal') ) {
		/* Upgrade of 'lab-hal' while afragen/github-updater is already activated
		 * Do not modify $source, lab-hal install/upgrade is managed by afragen/github-updater
		 */
		return $source;
	}
	// TODO in case lab-hal active BUT github-updater not used for upgrading !!
	/*
	 * Not GitHub Updater plugin/theme.
	 */
	if ( ! isset( $_POST['github_updater_repo'] ) && empty( $repo ) ) {
		return $source;
	}

	// Installation of 'lab-hal'
	$path_parts = pathinfo( $source );
    $newsource = trailingslashit( $path_parts['dirname'] ) . trailingslashit( 'lab-hal' );
    rename( $source, $newsource );
    $toto = fprintf( $thiz);
    $messages[]= array('message' => "Lab-Hal install: folder renamed from $source to $newsource <br>".$toto,
    	'notice-level' => 'notice-info' );
    LAbHalAdminNotices::addNotices( $messages  );
    return $newsource;
}

/**
 * Add an anction link in admin for lab-hal
 *
 * @param array  $links : list of action.
 * @param string $file : name of the current plugin.
 *
 * @return $links : modified list of action
 */
function lab_hal_plugin_action_links( $links, $file ) {
	if ( 'lab-hal/lab-hal.php'  !== $file ) {
		return $links;
	}

	$settings_link = '<a href="admin.php?page=lab-hal.php">' . __( 'Paramètres', 'lab-hal' ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links', 'lab_hal_plugin_action_links', 10, 2 );

/**
 * Ajoute le menu admin
 */
add_action( 'admin_menu', 'lab_hal_menu' );

/**
 * Fonction de création du menu admin
 */
function lab_hal_menu() {
	$lab_hal_admin_page = add_options_page( 'Options', 'Lab-Hal', 'manage_options', 'lab-hal.php', 'lab_hal_option', '', 21 );
	add_action( 'load-' . $lab_hal_admin_page, 'lab_hal_admin_help' );

	add_action( 'admin_init', 'lab_hal_register_settings' );
}

/**
 * Charge lorsque le plugin est activé ou désactivé
 */
register_deactivation_hook( __FILE__, 'lab_hal_reset_option' );
register_activation_hook( __FILE__, 'lab_hal_set_option' );


/**
 * Register lab_hal_options
 */
function lab_hal_register_settings() {
	register_setting( 'lab_hal_option', 'lab_hal_option_choix' );
	register_setting( 'lab_hal_option', 'lab_hal_option_type' );
	register_setting( 'lab_hal_option', 'lab_hal_option_lastyears' );
	register_setting( 'lab_hal_option', 'lab_hal_option_maxauthors' );
	register_setting( 'lab_hal_option', 'lab_hal_option_nbMaxEntrees' );
	register_setting( 'lab_hal_option', 'lab_hal_option_groupe' );
	register_setting( 'lab_hal_option', 'lab_hal_option_idhal' );
	register_setting( 'lab_hal_option', 'lab_hal_option_lang' );
	register_setting( 'lab_hal_option', 'lab_hal_option_infocontact' );
	register_setting( 'lab_hal_option', 'lab_hal_option_email' );
	register_setting( 'lab_hal_option', 'lab_hal_option_tel' );
	register_setting( 'lab_hal_option', 'lab_hal_option_social0' );
	register_setting( 'lab_hal_option', 'lab_hal_option_social1' );
	register_setting( 'lab_hal_option', 'lab_hal_option_social2' );
	register_setting( 'lab_hal_option', 'lab_hal_option_social3' );
}

/**
 * Crée le menu d'option du plugin
 */
function lab_hal_option() {

	if ( '' === get_option( 'lab_hal_option_type' ) ) {
		update_option( 'lab_hal_option_type', 'authIdHal_s' );
	}
	if ( '' === get_option( 'lab_hal_option_groupe' ) ) {
		update_option( 'lab_hal_option_groupe', 'paginer' );
	}

	// Set all lab_hal_option_choix to false and merge real option.
	$defaults                 = array(
		'1'  => false,
		'2'  => false,
		'3'  => false,
		'4'  => false,
		'5'  => false,
		'6'  => false,
		'7'  => false,
		'8'  => false,
		'9'  => false,
		'10' => false,
	);
	$lab_hal_option_lastyears = get_option( 'lab_hal_option_lastyears', '' );
	$lab_hal_option_choix     = get_option( 'lab_hal_option_choix', array() );
	if ( empty( $lab_hal_option_choix ) ) {
		$lab_hal_option_choix = array();
	}
	foreach ( $defaults as $name => $default ) {
		if ( ! array_key_exists( $name, $lab_hal_option_choix ) ) {
			$lab_hal_option_choix[ $name ] = $default;
		}
	}

	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Plugin LAB-HAL', 'lab-hal' ); ?></h2>
		<form method="post" enctype="multipart/form-data" action="options.php">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<?php settings_fields( 'lab_hal_option' ); ?>
						<?php do_settings_sections( 'lab_hal_option' ); ?>
						<table class="form-table">
							<tr valign="top">
								<th scope="row" style="font-size: 18px;"><?php esc_html_e( 'Paramètre de la page :', 'lab-hal' ); ?></th>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Type d\'Id', 'lab-hal' ); ?></th>
								<td><select name="lab_hal_option_type">
										<option id="Idhal" value="authIdHal_s" <?php echo ( 'authIdHal_s' === get_option( 'lab_hal_option_type' ) ) ? 'selected' : ''; ?>><label for="Idhal">Id Hal</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : laurent-capelli)', 'lab-hal' ); ?></span></option>
										<option id="StructId" value="structId_i" <?php echo ( 'structId_i' === get_option( 'lab_hal_option_type' ) ) ? 'selected' : ''; ?>><label for="StructId">Struct Id</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : 413106)', 'lab-hal' ); ?></span></option>
										<option id="AuthorStructId" value="authStructId_i" <?php echo ( 'authStructId_i' === get_option( 'lab_hal_option_type' ) ) ? 'selected' : ''; ?>><label for="AuthorStructId">AuthorStruct Id</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : 413106)', 'lab-hal' ); ?></span></option>
										<option id="Anrproject" value="anrProjectId_i" <?php echo ( get_option( 'anrProjectId_i' === 'lab_hal_option_type' ) ) ? 'selected' : ''; ?>><label for="Anrproject">anrProject Id</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : 1646)', 'lab-hal' ); ?></span></option>
										<option id="Europeanproject" value="europeanProjectId_i" <?php echo ( 'europeanProjectId_i' === get_option( 'lab_hal_option_type' ) ) ? 'selected' : ''; ?>><label for="Europeanproject">europeanProject Id</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : 17877)', 'lab-hal' ); ?></span></option>
										<option id="Collection" value="collCode_s" <?php echo ( 'collCode_s' === get_option( 'lab_hal_option_type' ) ) ? 'selected' : ''; ?>><label for="Collection">Collection</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : IRCICA)', 'lab-hal' ); ?></span></option>
									</select>
									<input type="text" name="lab_hal_option_idhal" id="lab_hal_option_idhal" value="<?php echo esc_html( get_option( 'lab_hal_option_idhal' ) ); ?>"/>
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Period for last years', 'lab-hal' ); ?></th>
								<td><input type="text" style="width:200px;" placeholder="Exemple : -4 or 2013" name="lab_hal_option_lastyears" id="lab_hal_option_lastyears" value="<?php echo esc_html( get_option( 'lab_hal_option_lastyears' ) ); ?>"/>
								<br /> -4: display last for years, 2013 display 2013 to current-year period
								<br /> or leave blank to cancel last period display
								</td>
							</tr>

							<tr>
								<th><?php esc_html_e( 'Limit number of publications to', 'lab-hal' ); ?></th>
								<td><input type="text" style="width:200px;" placeholder="Exemple : 0 , 200" name="lab_hal_option_nbMaxEntrees" id="lab_hal_option_nbMaxEntrees" value="<?php echo esc_html( get_option( 'lab_hal_option_nbMaxEntrees' ) ); ?>"/>
								<br /> 0: do not limit the number publications extracted from HAL (It can takes 5 to 15 second displaying a list of 1000 publications)
								<br /> 200: limit the number of publications extracted from HAL to 200, and display a link to full list
								</td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Truncate number of authors to', 'lab-hal' ); ?></th>
								<td><input type="text" style="width:200px;" placeholder="Exemple : 0 , 5" name="lab_hal_option_maxauthors" id="lab_hal_option_maxauthors" value="<?php echo esc_html( get_option( 'lab_hal_option_maxauthors' ) ); ?>"/>
								<br /> 0: do not limit the number of authors displayed for a publications
								<br /> 5: limit the authors list to 5 authors and display an "et al.." link to toggle show/hide other authors
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Affichage des documents', 'lab-hal' ); ?></th>
								<td><input type="radio" name="lab_hal_option_groupe" id="paginer" value="paginer" <?php echo ( 'paginer' === get_option( 'lab_hal_option_groupe' ) ) ? 'checked' : ''; ?>><label for="paginer"><?php esc_html_e( 'Documents avec pagination', 'lab-hal' ); ?></label><br>
									<input type="radio" name="lab_hal_option_groupe" id="grouper" value="grouper" <?php echo ( 'grouper' === get_option( 'lab_hal_option_groupe' ) ) ? 'checked' : ''; ?>><label for="grouper"><?php esc_html_e( 'Documents groupés par type', 'lab-hal' ); ?></label><br>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">ATTENTION les paramètres ci-dessous ne sont pas opérationnels (plugin en développement)</th>
								<td><b>ATTENTION les paramètres ci-dessous ne sont pas opérationnels (plugin en développement) </b></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Choix des éléments menu', 'lab-hal' ); ?></th>
								<td><input type="checkbox" name="lab_hal_option_choix[1]" id="Contact" value="contact" <?php echo ( 'contact' === $lab_hal_option_choix[1] ) ? 'checked' : ''; ?>><label for="Contact"><?php esc_html_e( 'Contact', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[2]" id="Disciplines" value="disciplines" <?php echo ( 'disciplines' === $lab_hal_option_choix[2] ) ? 'checked' : ''; ?>><label for="Disciplines"><?php esc_html_e( 'Disciplines', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[3]" id="Mots-clefs" value="mots-clefs" <?php echo ( 'mots-clefs' === $lab_hal_option_choix[3] ) ? 'checked' : ''; ?>><label for="Mots-clefs"><?php esc_html_e( 'Mots-clefs', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[4]" id="Auteurs" value="auteurs" <?php echo ( 'auteurs' === $lab_hal_option_choix[4] ) ? 'checked' : ''; ?>><label for="Auteurs"><?php esc_html_e( 'Auteurs', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[5]" id="Revues" value="revues" <?php echo ( 'revues' === $lab_hal_option_choix[5] ) ? 'checked' : ''; ?>><label for="Revues"><?php esc_html_e( 'Revues', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[6]" id="Annee" value="annee" <?php echo ( 'annee' === $lab_hal_option_choix[6] ) ? 'checked' : ''; ?>><label for="Annee"><?php esc_html_e( 'Année de production', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[7]" id="Institution" value="institution" <?php echo ( 'institution' === $lab_hal_option_choix[7] ) ? 'checked' : ''; ?>><label for="Institution"><?php esc_html_e( 'Institutions', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[8]" id="Laboratoire" value="laboratoire" <?php echo ( 'laboratoire' === $lab_hal_option_choix[8] ) ? 'checked' : ''; ?>><label for="Laboratoire"><?php esc_html_e( 'Laboratoires', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[9]" id="Departement" value="departement" <?php echo ( 'departement' === $lab_hal_option_choix[9] ) ? 'checked' : ''; ?>><label for="Departement"><?php esc_html_e( 'Départements', 'lab-hal' ); ?></label><br/>
									<input type="checkbox" name="lab_hal_option_choix[10]" id="Equipe" value="equipe" <?php echo ( 'equipe' === $lab_hal_option_choix[10] ) ? 'checked' : ''; ?>><label for="Equipe"><?php esc_html_e( 'Équipes de recherche', 'lab-hal' ); ?></label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row" style="font-size: 18px;"><?php esc_html_e( 'Contact :', 'lab-hal' ); ?></th>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( "Afficher les informations d'un chercheur ayant un IdHal ?", 'lab-hal' ); ?></th>
								<td><input type="radio" name="lab_hal_option_infocontact" id="lab-hal-yes" value="yes" <?php echo ( 'yes' === get_option( 'lab_hal_option_infocontact' ) ) ? 'checked' : ''; ?>><label for="lab-hal-yes"><?php esc_html_e( 'Oui', 'lab-hal' ); ?></label><br>
									<input type="radio" name="lab_hal_option_infocontact" id="lab-hal-no" value="no" <?php echo ( 'no' === get_option( 'lab_hal_option_infocontact' ) ) ? 'checked' : ''; ?>><label for="lab-hal-no"><?php esc_html_e( 'Non', 'lab-hal' ); ?></label><br>
								</td>
							<tr>
								<th scope="row"><?php esc_html_e( 'Email', 'lab-hal' ); ?></th>
								<td><input type="text" style="width:300px;" placeholder="Exemple : hi@mail.com" name="lab_hal_option_email" id="lab_hal_option_email" value="<?php echo esc_html( get_option( 'lab_hal_option_email' ) ); ?>"/><img alt="email" src="<?php echo esc_html( LAB_HAL_URI ); ?>img/mail.svg" style="vertical-align:middle; width:32px; margin-left:2px; margin-right:2px;"/></td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Téléphone', 'lab-hal' ); ?></th>
								<td><input type="text" style="width:300px;" placeholder="Exemple : 06-01-02-03-04" name="lab_hal_option_tel" id="lab_hal_option_tel" value="<?php echo esc_html( get_option( 'lab_hal_option_tel' ) ); ?>"/><img alt="phone" src="<?php echo esc_html( LAB_HAL_URI ); ?>img/phone.svg" style="vertical-align:middle; width:32px; margin-left:2px; margin-right:2px;"/></td>
							</tr>
							<tr>
								<th>Facebook (http://www.facebook.com/)</th>
								<td><input type="text" style="width:300px;" placeholder="Exemple : fa.book" name="lab_hal_option_social0" id="lab_hal_option_social0" value="<?php echo esc_html( get_option( 'lab_hal_option_social0' ) ); ?>"/><img alt="facebook" src="<?php echo esc_html( LAB_HAL_URI ); ?>img/facebook.svg" style="vertical-align:middle; width:32px; margin-left:2px; margin-right:2px;"/></td>
							</tr>
							<tr>
								<th>Twitter (http://www.twitter.com/)</th>
								<td><input type="text" style="width:300px;" placeholder="Exemple : tweet_heure" name="lab_hal_option_social1" id="lab_hal_option_social1" value="<?php echo esc_html( get_option( 'lab_hal_option_social1' ) ); ?>"/><img alt="twitter" src="<?php echo esc_html( LAB_HAL_URI ); ?>img/twitter.svg" style="vertical-align:middle; width:32px; margin-left:2px; margin-right:2px;"/></td>
							</tr>
							<tr>
								<th>Google + (https://plus.google.com/u/0/+)</th>
								<td><input type="text" style="width:300px;" placeholder="Exemple : goo.plus" name="lab_hal_option_social2" id="lab_hal_option_social2" value="<?php echo esc_html( get_option( 'lab_hal_option_social2' ) ); ?>"/><img alt="google" src="<?php echo esc_html( LAB_HAL_URI ); ?>img/google-plus.svg" style="vertical-align:middle; width:32px; margin-left:2px; margin-right:2px;"/></td>
							</tr>
							<tr>
								<th>LinkedIn (https://www.linkedin.com/in/)</th>
								<td><input type="text" style="width:300px;" placeholder="Exemple : link.dine" name="lab_hal_option_social3" id="lab_hal_option_social3" value="<?php echo esc_html( get_option( 'lab_hal_option_social3' ) ); ?>"/><img alt="linkedin" src="<?php echo esc_html( LAB_HAL_URI ); ?>img/linkedin.svg" style="vertical-align:middle; width:32px; margin-left:2px; margin-right:2px;"/></td>
							</tr>
						</table>
						<?php
						submit_button( __( 'Enregistrer', 'lab-hal' ), 'primary large', 'submit', true );
						?>
					</div>
					<div id="postbox-container-1" class="postbox-container">

					</div>
				</div>
				<br class="clear"><br/>
			</div>
		</form>

	</div>

	<?php
}


/**
 * Delete lab-hal options on plugin desactivation
 */
function lab_hal_reset_option() {
	delete_option( 'lab_hal_option_type' );
	delete_option( 'lab_hal_option_idhal' );
	delete_option( 'lab_hal_option_choix' );
	delete_option( 'lab_hal_option_lastyears' );
	delete_option( 'lab_hal_option_maxauthors' );
	delete_option( 'lab_hal_option_nbMaxEntrees' );
	delete_option( 'lab_hal_option_groupe' );
	delete_option( 'lab_hal_option_infocontact' );
	delete_option( 'lab_hal_option_email' );
	delete_option( 'lab_hal_option_tel' );
	delete_option( 'lab_hal_option_social0' );
	delete_option( 'lab_hal_option_social1' );
	delete_option( 'lab_hal_option_social2' );
	delete_option( 'lab_hal_option_social3' );
}

/**
 * Set lab-hal options on plugin activation
 */
function lab_hal_set_option() {
	$temp = array(
		'1'  => false,
		'2'  => false,
		'3'  => false,
		'4'  => false,
		'5'  => false,
		'6'  => false,
		'7'  => false,
		'8'  => false,
		'9'  => false,
		'10' => false,
	);
	update_option( 'lab_hal_option_type', 'collCode_s' );
	update_option( 'lab_hal_option_idhal', 'IRCICA' );
	update_option( 'lab_hal_option_choix', $temp );
	update_option( 'lab_hal_option_lastyears', '-4' );
	update_option( 'lab_hal_option_maxauthors', '8' );
	update_option( 'lab_hal_option_nbMaxEntrees', '200' );
	update_option( 'lab_hal_option_groupe', 'grouper' );
	update_option( 'lab_hal_option_infocontact', '' );
	update_option( 'lab_hal_option_email', '' );
	update_option( 'lab_hal_option_tel', '' );
	update_option( 'lab_hal_option_social0', '' );
	update_option( 'lab_hal_option_social1', '' );
	update_option( 'lab_hal_option_social2', '' );
	update_option( 'lab_hal_option_social3', '' );

}

/**
 * Lab-HAL plugin update function runned after the Wordpress plugin update
 *
 * This should be run each time the plugin is updated (manually or not), after the WordPress process of plugin updating
 * So this code runs the new (updated) plugin CODE
 * This code is hooked on 'admin_init' to consider the manual upgrade case (updated plugin folder copy instead of built in update do not fire 'upgrader_process_complete')
 * So it is not executed if admin is not launched (manual upgrade with no admin browsing)
 */
function lab_hal_detect_need_update() {
	// Do not echo anything in this function, this breaks wordpress!
	$previous_version = get_option( 'LAB_HAL_VERSION', -1 ); // no version saved in former 0.0 version.


	if ( version_compare( LAB_HAL_VERSION, $previous_version, '==' ) ) {
		// Identical version: no upgrade needed !
		return;
	} elseif ( version_compare( LAB_HAL_VERSION, $previous_version, '>=' ) ) {
		// New version, upgrade needed.
		lab_hal_update( $previous_version, LAB_HAL_VERSION );

	} else {
		// Older version !! not possible excepted in case of manual downgrade !
		return;
	}
}

add_action( 'admin_init', 'lab_hal_detect_need_update' );

/**
 * This function update settings and database when needed
 *
 * $new_version is > to $previous_version
 *
 * @param string $previous_version : installed version (previous).
 * @param string $new_version : update package version (new).
 */
function lab_hal_update( $previous_version = '', $new_version = '' ) {
	// Do not echo anything in this function, this breaks wordpress!
	$messages = array();

	// Its a good practice to limit update conditionnally to $previous_version AND $new_version.

	if ( version_compare( $previous_version, '0.0', '<' ) &&
			version_compare( $new_version, '0.0', '>=' ) ) {
			// Upgrade from version -1 to some version >0.0.
			$messages[]= array('message' => "Lab-Hal : Upgrade from version $previous_version to $new_version",
				'notice-level' => 'notice-info' );
			LAbHalAdminNotices::addNotices( $messages  );
	}
	if ( version_compare( $previous_version, '0.0', '<' ) ) {
		// previous_version <=0.0 : Delete unused option.
		delete_option( 'lab_hal_option_lang' );
		// previous_version <=0.0: Initialize new options : add_option does nothing if the option already exists.
		add_option( 'lab_hal_option_lastyears', '-4' );
		add_option( 'lab_hal_option_maxauthors', '8' );
		add_option( 'lab_hal_option_nbMaxEntrees', '200' );
	}

	update_option('LAB_HAL_VERSION', LAB_HAL_VERSION);
}

/**
 * Provide lab-hal help tabs in admin
 */
function lab_hal_admin_help() {
	$screen = get_current_screen();

	$content = '<p>' . __( 'The current settings represents default settings used by Lab-HAL with the default shortcode [lab-hal-list]. Most importants parameters can be override inside the shortcode as described in "how to" help section', 'lab-hal' ) . '</p>';
	$screen->add_help_tab(
		array(
			'id'      => 'settings',
			'title'   => 'Settings',
			'content' => $content,
		)
	);
	$content  = '<p>' . __( 'This section describe how to set up a page to browse an author or an organisation publication\'s list.', 'lab-hal' );
	$content .= '<ul>';
	$content .= '<li>' . __( 'Define lab-HAL settings on this current page .', 'lab-hal' ) . '</li>';
	$content .= '<li>' . __( 'Create a page or article containing your text and the shortcode [lab-hal-list].', 'lab-hal' ) . '</li>';
	$content .= '</ul>';
	$content .= __( 'You can give parameters to the shortcode [cv-hal_list] to display multiple pages on your website with different IDs. These parameters override the default settings of lab-HAL (the default HAL query). This is simply done adding <i>option_type=idhal</i> in the shortcode. Use the next examples by providing your idhal:', 'lab-hal' );

	$content .= '<ul>';
	$content .= '<li>[lab-hal-list collCode_s=IRCICA] ' . __( 'Browse an entire collection (laboratory).', 'lab-hal' ) . '</li>';
	$content .= '<li>[lab-hal-list structId_i=413106] ' . __( 'Browse a structure publications list.', 'lab-hal' ) . '</li>';
	$content .= '<li>[lab-hal-list authIdHal_s=laurent-capelli] ' . __( 'Search for an author.', 'lab-hal' ) . '</li>';
	$content .= '<li>[lab-hal-list authStructId_i=413106] ' . __( 'Search for an author by its ID.', 'lab-hal' ) . '</li>';
	$content .= '<li>[lab-hal-list anrProjectId_i=1646] ' . __( 'Browse an ANR project publications list.', 'lab-hal' ) . '</li>';
	$content .= '<li>[lab-hal-list europeanProjectId_i=TICE2014]' . __( 'Browse a European project publications list.', 'lab-hal' ) . '</li>';
	$content .= '<li><a href="https://wiki.ccsd.cnrs.fr/wikis/hal/index.php/Requ%C3%AAtes_sur_les_ressources_de_HAL">' . __( 'See more on HAL requests.', 'lab-hal' ) . '</a></li>';
	$content .= '<li>' . __( 'At present, only the previously described requests are implemented in lab-HAL.', 'lab-hal' ) . '</li>';
	$content .= __( 'You can also override others default settings of lab-HAL adding some parameters in the shortcode:', 'lab-hal' );
	$content .= '<ul>';
	$content .= '<li>[lab-hal-list someoption_type=idhal lastyears=-4] ' . __( 'Define a time period for filtering publication list: -4: display last 4 years; 2013 display 2013 to current-year period; or leave blank to cancel last period display', 'lab-hal' ) . '</li>';
	$content .= '</ul>';
	$content .= '</p>';

	$screen->add_help_tab(
		array(
			'id'      => 'usage',
			'title'   => 'How To',
			'content' => $content,
		)
	);

}

add_action('admin_notices', [new LabHalAdminNotices(), 'displayAdminNotice']);
/**
 * Class used to display notice message in admin
 * @author seguinot
 *
 */
class LabHalAdminNotices
{
	const NOTICE_FIELD = 'lab_hal_admin_notices';

	public function displayAdminNotice()
	{
		$notices = get_option(self::NOTICE_FIELD);
		if ( empty( $notices ) ) {
			return;
		}
		foreach ($notices as $notice) {
			$message     = isset($notice['message']) ? $notice['message'] : false;
			$noticeLevel = ! empty($notice['notice-level']) ? $notice['notice-level'] : 'notice-error';

			if ($message) {
				echo "<div class='notice {$noticeLevel} is-dismissible'><p>{$message}</p></div>";
			}
		}
		delete_option(self::NOTICE_FIELD);
	}

	public static function addNotices( $notices ) {
		if ( $notices ) {
			update_option(self::NOTICE_FIELD, $notices);
		}

	}
}
