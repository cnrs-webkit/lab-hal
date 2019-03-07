<?php
/**
 * Plugin lab-hal
 *
 * @package     lab-hal
 * @subpackage  Widget "Lastest Publications".
 * @since       0.0
 * @author      Christophe Seguinot
 * @copyright   2016 Christophe Seguinot
 * @license     GPL-3.0
 *
 * Plugin Name:  LAB-HAL
 * Plugin URI:
 * Description:  Ce plugin est une adaptation du plugin wp-HAL de Baptiste Blondelle . Il Crée une page qui remonte les publications d'un auteur ou d'une structure en relation avec HAL et un widget des dernières publications d'un auteur ou d'une structure.
 * Version:      0.0
 * Author:       Christophe Seguinot
 * Contributors: Baptiste Blondelle (wp-HAL), Emmanuel Leguy (partie Angular Js glanée sur le site https://www.cristal.univ-lille.fr)
 * Author URI:   christophe.seguinot@univ-lille.fr
 * Text Domain:  lab-hal
 * Domain Path:  /languages
 */

/**
 * Classe du widget lab_hal
 */
class Lab_Hal_Widget extends WP_widget {

	/**
	 * Défini les propriétés du widget
	 */
	public function __construct() {
		$options = array(
			'classname'   => 'lab-hal-publications',
			'description' => __( "Afficher les dernières publications d'un auteur ou d'une structure.", 'lab-hal' ),
		);

		parent::__construct(
			'ld-hal-publications',
			__( 'Publications récentes', 'lab-hal' ),
			$options
		);
	}

	/**
	 * Crée le widget Lab_Hal_Widget
	 *
	 * @param array $args : The widget's sidebar args.
	 * @param array $instance :  The widget's instance setting.
	 */
	public function widget( $args, $instance ) {
		if ( ! function_exists( 'curl_init' ) ) {
			$content = 'Please check the <a href="https://wordpress.org/plugins/hal/faq/" target="_blank" id="FAQ">FAQ</a> with the code : CURL';
			extract( $args );
			echo $before_widget;
			echo $before_title . $instance['titre'] . $after_title;
			echo $content;
			echo $after_widget;
		} elseif ( ! isset( $instance['idhal'] ) ) {
			// In case idhal is not set!
			$content = 'No IDHAL given !';
			extract( $args );
			echo $before_widget;
			echo $before_title . $instance['titre'] . $after_title;
			echo $content;
		} else {
			$instance['idhal'] = $this::lab_hal_verif_solr( $instance['idhal'] );

			$url = LAB_HAL_API . '?q=*:*&fq=' . $instance['select'] . ':' . rawurlencode( $instance['idhal'] ) . '&fl=uri_s,' . $instance['typetext'] . '&sort=' . LAB_HAL_PRODUCEDDATEY . '&rows=' . $instance['nbdoc'] . '&wt=json';
			$ch  = curl_init( $url );

			$options = array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER     => array( 'Content-type: application/json' ),
				CURLOPT_TIMEOUT        => 10,
				CURLOPT_USERAGENT      => 'HAL Plugin Wordpress ' . LAB_HAL_VERSION,
			);

			// Bind des options et de l'objet cURL que l'on va utiliser.
			curl_setopt_array( $ch, $options );
			// Récupération du résultat JSON.
			$json = json_decode( curl_exec( $ch ) );
			curl_close( $ch );

			// Account for possible empty result!
			if ( count( $json->response->docs ) ) {
				$content = '<ul class="widldhal-ul">';

				$nb_doc = count( $json->response->docs );
				for ( $i = 0; $i < $nb_doc; $i++ ) {

					if ( 'citationRef_s' === $instance['typetext'] ) {
						$typetext = $json->response->docs[ $i ]->citationRef_s;
					} else {
						$typetext = $json->response->docs[ $i ]->title_s[0];
					}
					$content .= '<li class="widldhal-li"><a href="' . $json->response->docs[ $i ]->uri_s . '" target="_blank">' . $typetext . '</a></li>';
				}
				$content .= '</ul>';
			} else {
				$content = 'No recent publication';
			}

			extract( $args );
			echo $before_widget;
			echo $before_title . $instance['titre'] . $after_title;
			echo $content;
			echo $after_widget;
		}
	}

	/**
	 * This function convert a CSV string into and OR sql request
	 *
	 * @param string $values : list of value.
	 */
	public function lab_hal_verif_solr( $values ) {
	    $verifsolr = explode( ',', $values );
	    $numverif  = count( $verifsolr );
	    $solrsql   = '';
	    if ( $numverif < 1024 ) {
	        $solrsql = str_replace( ',', ' OR ', $values );
	    } else {
	        $listsolrsql = explode( ',', $values );
	        for ( $i = 0;$i < 1024;$i++ ) {
	            if ( 0 === $i ) {
	                $solrsql = $listsolrsql[ $i ];
	            } else {
	                $solrsql .= ' OR ';
	                $solrsql .= $listsolrsql[ $i ];
	            }
	        }
	    }

	    return $solrsql;
	}


	/**
	 * Sauvegarde des données du formulaire wu widget
	 *
	 * @param object $new : new instance.
	 * @param object $old : old instance.
	 */
	public function update( $new, $old ) {
		return $new;
	}

	/**
	 * Affiche le formulaire du widget
	 *
	 * @param object $instance : widget instance.
	 */
	public function form( $instance ) {

		$defaut   = array(
			'titre'    => __( 'Publications récentes', 'lab-hal' ),
			'select'   => 'authIdHal_s',
			'typetext' => 'title_s',
			'nbdoc'    => 5,
		);
		$instance = wp_parse_args( $instance, $defaut );
		?>
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'titre' ) ); ?>"><?php esc_html_e( 'Titre', 'lab-hal' ) . ' :'; ?></label>
			<input value="<?php echo esc_html( $instance['titre'] ); ?>" name="<?php echo esc_html( $this->get_field_name( 'titre' ) ); ?>" id="<?php echo esc_html( $this->get_field_id( 'titre' ) ); ?>" class="widefat" type="text"/>
		</p>
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'nbdoc' ) ); ?>"><?php esc_html_e( 'Nombre de documents affichés', 'lab-hal' ) . ' :'; ?></label>
			<input class="tiny-text" id="<?php echo esc_html( $this->get_field_id( 'nbdoc' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'nbdoc' ) ); ?>" type="number" step="1" min="1" max="10" value="<?php echo esc_html( $instance['nbdoc'] ); ?>" size="3">
		</p>
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'typetext' ) ); ?>"><?php esc_html_e( "Type d'affichage", 'lab-hal' ) . ' :'; ?></label>
			<select id="<?php echo esc_html( $this->get_field_id( 'typetext' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'typetext' ) ); ?>">
				<option id="<?php echo esc_html( $this->get_field_id( 'Title' ) ); ?>" value="title_s" <?php echo ( 'title_s' === $instance['typetext'] ) ? 'selected' : ''; ?>><label for="<?php echo esc_html( $this->get_field_id( 'Title' ) ); ?>"><?php esc_html_e( 'Titre', 'lab-hal' ); ?></label></option>
				<option id="<?php echo esc_html( $this->get_field_id( 'Citation' ) ); ?>" value="citationRef_s" <?php echo ( 'citationRef_s' === $instance['typetext'] ) ? 'selected' : ''; ?>><label for="<?php echo esc_html( $this->get_field_id( 'Citation' ) ); ?>"><?php esc_html_e( 'Citation', 'lab-hal' ); ?></label></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'select' ) ); ?>"><?php esc_html_e( "Type d'Id", 'lab-hal' ) . ' :'; ?></label>
			<select id="<?php echo esc_html( $this->get_field_id( 'select' ) ); ?>" name="<?php echo esc_html( $this->get_field_name( 'select' ) ); ?>">
				<option id="<?php echo esc_html( $this->get_field_id( 'Idhal' ) ); ?>" value="authIdHal_s" <?php echo ( 'authIdHal_s' === $instance['select'] ) ? 'selected' : ''; ?>><label for="<?php echo esc_html( $this->get_field_id( 'Idhal' ) ); ?>">Id Hal</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : laurent-capelli)', 'lab-hal' ); ?></span></option>
				<option id="<?php echo esc_html( $this->get_field_id( 'StructId' ) ); ?>" value="structId_i" <?php echo ( 'structId_i' === $instance['select'] ) ? 'selected' : ''; ?>><label for="<?php echo esc_html( $this->get_field_id( 'StructId' ) ); ?>">Struct Id</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : 413106)', 'lab-hal' ); ?></span></option>
				<option id="<?php echo esc_html( $this->get_field_id( 'AuthorStructId' ) ); ?>" value="authStructId_i" <?php echo ( 'authStructId_i' === $instance['select'] ) ? 'selected' : ''; ?>><label for="<?php echo esc_html( $this->get_field_id( 'AuthorStructId' ) ); ?>">AuthorStruct Id</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : 413106)', 'lab-hal' ); ?></span></option>
				<option id="<?php echo esc_html( $this->get_field_id( 'Anrproject' ) ); ?>" value="anrProjectId_i" <?php echo ( 'anrProjectId_i' === $instance['select'] ) ? 'selected' : ''; ?>><label for="<?php echo esc_html( $this->get_field_id( 'Anrproject' ) ); ?>">anrProject Id</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : 1646)', 'lab-hal' ); ?></span></option>
				<option id="<?php echo esc_html( $this->get_field_id( 'Europeanproject' ) ); ?>" value="europeanProjectId_i" <?php echo ( 'europeanProjectId_i' === $instance['select'] ) ? 'selected' : ''; ?>><label for="<?php echo esc_html( $this->get_field_id( 'Europeanproject' ) ); ?>">europeanProject Id</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : 17877)', 'lab-hal' ); ?></span></option>
				<option id="<?php echo esc_html( $this->get_field_id( 'Collection' ) ); ?>" value="collCode_s" <?php echo ( 'collCode_s' === $instance['select'] ) ? 'selected' : ''; ?>><label for="<?php echo esc_html( $this->get_field_id( 'Collection' ) ); ?>">Collection</label><span style="font-style: italic;"> <?php esc_html_e( '(Exemple : TICE2014)', 'lab-hal' ); ?></span></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'idhal' ) ); ?>"><?php esc_html_e( 'Id', 'lab-hal' ) . ' :'; ?></label>
			<input value="<?php echo esc_html( $instance['idhal'] ); ?>" name="<?php echo esc_html( $this->get_field_name( 'idhal' ) ); ?>" id="<?php echo esc_html( $this->get_field_id( 'idhal' ) ); ?>" type="text"/>
		</p>
		<?php
	}
}
