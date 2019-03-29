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
 * Get the cached version of the json file or extract it from $url
 *
 * @param string $url : the url to query.
 * @param string $query : the url queyr part used as transient name.
 *
 * @return string $json : the json file
 */
function get_cached_json( $url = '', $query = '' ) {

	$json = get_transient( $query );
	if ( false === $json ) {
		// this url is not cached, upload it.
		if ( ! class_exists( 'WP_Http' ) ) {
				include_once ABSPATH . WPINC . '/class-http.php';
		}
		$request = new WP_Http();
		$result  = $request->request( $url );
		$json    = $result['body'];
		// And cache it.
		set_transient( $query, $json, 24 * HOUR_IN_SECONDS );
	}
	return $json;
}

// Création du shortcode lab-hal-list.
add_shortcode( 'lab-hal-list', 'lab_hal_list' );

/**
 * Shortcode function for lab_hal_list : render a publication liste
 *
 * @param array $param : parameters list of the shortcode.
 */
function lab_hal_list( $param ) {
	global $wp_query;
	$hal_footer = '';

	/*
	 *  lab_hal_option_type  lab_hal_option_idhal
	 *  authIdHal_s Id Hal (Exemple : laurent-capelli)
	 *  structId_i Struct Id (Exemple : 413106)
	 *  authStructId_i AuthorStruct Id (Exemple : 413106)
	 *  anrProjectId_i anrProject Id (Exemple : 1646)
	 *  europeanProjectId_i europeanProject Id (Exemple : 17877)
	 *  collCode_s  (Exemple : TICE2014)
	 */

	// Attention: $param keys are lowercase as compared to page content !
	if ( isset( $param['collcode_s'] ) ) {
		$lab_hal_option_type  = 'collCode_s';
		$lab_hal_option_idhal = $param['collcode_s'];
	} elseif ( isset( $param['authidhal_s'] ) ) {
		$lab_hal_option_type  = 'authIdHal_s';
		$lab_hal_option_idhal = $param['authidhal_s'];
	} elseif ( isset( $param['structid_i'] ) ) {
		$lab_hal_option_type  = 'structId_i';
		$lab_hal_option_idhal = $param['structid_i'];
	} elseif ( isset( $param['authstructid_i'] ) ) {
		$lab_hal_option_type  = 'authStructId_i';
		$lab_hal_option_idhal = $param['authstructid_i'];
	} elseif ( isset( $param['anrprojectid_i'] ) ) {
		$lab_hal_option_type  = 'anrProjectId_i';
		$lab_hal_option_idhal = $param['anrprojectiid_i'];
	} elseif ( isset( $param['europeanprojectid_i'] ) ) {
		$lab_hal_option_type  = 'europeanProjectId_i';
		$lab_hal_option_idhal = $param['europeanprojectid_i'];
	} else {
		$lab_hal_option_type  = get_option( 'lab_hal_option_type' );
		$lab_hal_option_idhal = get_option( 'lab_hal_option_idhal' );
	}

	if ( ! isset( $param['lastyears'] ) ) {
		$param['lastyears'] = get_option( 'lab_hal_option_lastyears', '' );
	}

	if ( '' === $param['lastyears'] ) {
		$yearfrom = '';
	} elseif ( (int) $param['lastyears'] < 0 ) {
		$yearfrom = date( 'Y' ) + (int) $param['lastyears'];
	} else {
		$yearfrom = (int) $param['lastyears'];
	}

	// If maxauthors=0 (no limit) set limit to 999 !!
	$lab_hal_option_maxauthors = get_option( 'lab_hal_option_maxauthors', 5 );
	$lab_hal_option_maxauthors = 0 === $lab_hal_option_maxauthors ? 999 : $lab_hal_option_maxauthors;

	$lab_hal_option_nb_max_entrees = get_option( 'lab_hal_option_nbMaxEntrees', 0 );
	// override $lab_hal_option_nb_max_entrees when query_vars['extractall'] is used.
	if ( isset( $wp_query->query_vars['extractall'] ) ) {
		$lab_hal_option_nb_max_entrees = 0;
	}
	if ( 0 === $lab_hal_option_nb_max_entrees ) {
		// If nbMaxEntrees=0 (no limit) set limit to 999999 !
		$lab_hal_option_nb_max_entrees = 99999;
	}

	/* translators: %i is the maximun number of publications to extract from HAL. (default template if no other file found and template in settings is empty) */
	$truncated        = '<strong>' . sprintf( __( '%d latest publications', 'lab-hal' ), $lab_hal_option_nb_max_entrees ) . '</strong>';
	$extract_all_link = '<a href="' . get_permalink() . '?extractall">Extract all HAL publication:</a> ';
	$fulllist         = '<strong>' . __( 'Publications full list', 'lab-hal' ) . '</strong>';

	$attributs     = 'docType_s,halId_s,version_i,producedDateY_i,title_s,citationRef_s,authFullName_s';
	$tri           = 'producedDate_s desc';
	$lab_hal_query = $lab_hal_option_type . ':' . $lab_hal_option_idhal;
	$url           = 'https://api.archives-ouvertes.fr/search/?sort=' . rawurlencode( $tri ) . '&rows=' . rawurlencode( $lab_hal_option_nb_max_entrees ) . '&fl=' . rawurlencode( $attributs ) . '&q=' . rawurlencode( $lab_hal_query );

	$json = get_cached_json( $url, rawurlencode( $lab_hal_query ) . '_' . (string) $lab_hal_option_nb_max_entrees );

	$content  = '';
	$content .= '
	<div id="wrapper">

				<section class="container top-no-header" ng-app="PublisApp" ng-controller="PublisEquipeCtrl">

	<div class="row">
   		<div class="span12 centered">

	  		<div class="breaker">
            	<span class="left"></span>
            	<div class="feather"></div>
            	<span class="right"></span>
            </div>
		</div>
    </div>

    <div class="row">
    	<div class="col-md-8">
    		<div ng-show="loading">
				<h3>Loading...<img alt="loading" src="' . LAB_HAL_URI . 'img/loading.gif" height="16" /></h3>
    			<p>' . __( 'Publications list with more than 500 items may require more than 10 second to load. Be patient! ', 'lab-hal' ) . '</p>
    		</div>
    		<div ng-hide="loading" class="ng-hide">
    		<div ng-show="!(publis| filter:filter).length"><p>Aucune publication à afficher !<p></div>
    		<div ng-repeat="publi in publis | filter:filter:true">
				<p>
					<span class="dashicons dashicons-media-default" data-original-title="Document" ></span>
					<a data-original-title="Voir la ressource" href="https://hal.archives-ouvertes.fr/{{::publi.halId_s}}" data-toggle="tooltip" data-placement="bottom" title="">
						{{publi.halId_s}}<small>v{{publi.version_i}}</small>
					</a>
					<span class="label label-{{::publi.docType_s | lowercase}}">{{::docTypeLabel[publi.docType_s]}}</span>
					<span class="label label-success">{{::publi.producedDateY_i}}</span>
				</p>
				<p>
        			<i><a ng-repeat="auteur in publi.authFullName_s |limitTo:' . $lab_hal_option_maxauthors . ':0"
        				target="_blank"
        				href="https://hal.archives-ouvertes.fr/search/index/q/%2A/authFullName_t/{{::auteur}}/">
        				{{::auteur}}<span ng-show=" ! $last ">, </span>
        			</a></i>

    				<i> <a ng-click="hiddenAuthors = !hiddenAuthors"
    					ng-show="publi.authFullName_s.length>' . $lab_hal_option_maxauthors . '">, <strong>et al...</strong></a> </i>

    				<span ng-show="hiddenAuthors">
    				<i><a ng-repeat="auteur in publi.authFullName_s |limitTo:1000:' . $lab_hal_option_maxauthors . '"
        				target="_blank"
        				href="https://hal.archives-ouvertes.fr/search/index/q/%2A/authFullName_t/{{::auteur}}/">
        				{{::auteur}}<span ng-show=" ! $last ">, </span>
        			</a>
    				</i>
    				</span>
					<br/>
        			<strong>
        				<a data-original-title="Voir la ressource" target="_blank" href="https://hal.archives-ouvertes.fr/{{::publi.halId_s}}" data-toggle="tooltip" data-placement="bottom" title="">
        					{{::publi.title_s[0]}}
        				</a>
        			</strong>
        			<br>
	        		<span ng-bind-html="publi.citationRef_s"></span>
        		</p>
    	    	</div>
	    	</div>' . $hal_footer . '
		</div> <!--<div class="col-md-8">-->
    	<div class="col-md-4">
	    			<div ng-hide="loading" class="ng-hide">
   					<p ng-hide="fullist" class="lead" >
						' . $truncated . '<br />' . $extract_all_link . '
    				</p>
   					<p ng-show="fullist" class="lead" >
						' . $fulllist . '
    				</p>
								<p ng-hide="debut" class="lead" ng-model="strict" >
						<strong>Filter by type:</strong>
						<a class="label label-tout" ng-click="filter = {}">All ({{nbPublis}}) </a> <br/>
    					<a ng-repeat="type in docTypes" class="label label-{{type | lowercase}} active" ng-click="$parent.filter = {docType_s: type}">{{docTypeLabel[type]}} ({{totalPardocTypes[type]}})</a>
    				</p>
    				<p ng-hide="debut" class="lead">
    					<strong>Filter by year :</strong>
						<a class="label label-tout ng-scope" ng-click="filter = {}">All ({{nbPublis}})</a><br/>
						<a class="label label-success " ng-click="filter = {lastyears: 1}">' . $yearfrom . '-{{currentYear}} ({{totallastyears}})</a> <a ng-repeat="annee in annees" class="label label-success" ng-click="$parent.filter = {producedDateY_i: annee}">{{annee}} ({{totalParannees[annee]}})</a>
    				</p>
						<p ng-hide="debut" class="lead">
    					<strong>Filter by author :</strong>
						<a class="label label-tout" ng-click="filter = {}">All ({{nbPublis}})</a><br/>
						<button class="label label-all-less-btn" ng-click="showAllLessItems()">All/Less author filters</button>
						<a ng-repeat="auteur in auteurs | limitTo: nbItemsToDisplay()" class="label label-success liste-courte" ng-click="$parent.filter = {authFullName_s: auteur}">{{auteur}} ({{totalParauteurs[auteur]}})</a>
						</p>
 				</div>
    		</div>
    	</div><!--<div class="col-md-4">-->
    </div>


</section>

<!-- javascript var useb by publi.js -->
<script type="text/javascript">
	var lab_hal_query = \'' . $lab_hal_option_type . ':' . $lab_hal_option_idhal . '\';
	var lab_hal_yearfrom = \'' . $yearfrom . '\';
	var lab_hal_max_authors =  \'' . $lab_hal_option_maxauthors . '\';
	var lab_hal_publis =  ' . $json . ';
	var lab_hal_nbMaxEntrees =  ' . $lab_hal_option_nb_max_entrees . ';
</script>
			';
	return $content;
}


/**
 * Add styles
 */
function lab_hal_wp_adding_style() {
	wp_register_style( 'lab-hal-style1', LAB_HAL_URI . 'css/style.css', '', LAB_HAL_VERSION );
	// TODO not used wp_register_style( 'lab-hal-style2', plugins_url( '/css/jquery.jqplot.css', __FILE__ ), '', LAB_HAL_VERSION );.
	wp_enqueue_style( 'lab-hal-style1' );
}

/**
 * Register and Add scripts
 */
function lab_hal_wp_adding_script() {
	wp_register_script( 'lab-hal-script1', LAB_HAL_URI . 'js/jquery.jqplot.js', '', '1.0.8', true );
	wp_register_script( 'lab-hal-script2', LAB_HAL_URI . 'js/jqplot.highlighter.js', '', '1.0.8', true );
	wp_register_script( 'lab-hal-script3', LAB_HAL_URI . 'js/jqplot.pieRenderer.js', '', '1.0.8', true );

	wp_register_script( 'lab-hal-publis', LAB_HAL_URI . 'js/publis.js', '', LAB_HAL_VERSION, true );
	wp_register_script( 'lab-hal-angular', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.7.7/angular.min.js', '', '1.7.7', true );
	wp_register_script( 'lab-hal-angular-sanitize', 'https://ajax.googleapis.com/ajax/libs/angularjs/1.7.7/angular-sanitize.min.js', '', '1.7.7', true );

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'lab-hal-angular' );
	wp_enqueue_script( 'lab-hal-angular-sanitize' );
	wp_enqueue_script( 'lab-hal-publis' );

	wp_enqueue_script( 'lab-hal-script4', LAB_HAL_URI . 'js/cv-hal.js', '', LAB_HAL_VERSION, true );
}

/**
 * Récupère les fichiers css et js
 */
add_action( 'wp_enqueue_scripts', 'lab_hal_wp_adding_style' );
add_action( 'wp_enqueue_scripts', 'lab_hal_wp_adding_script' );
