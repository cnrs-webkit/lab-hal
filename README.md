# ATTENTION version de test et de développement
__N'utiliser pas la version téléchargeable sur cette page du plugin (lab-hal-master.zip).__

*Cette archive contient le suffixe -master* est installerait le plugin dans une répertoire /wp-content/plugins/lab-hal **-master** au lieu de /wp-content/plugins/lab-hal. Cette installation serait fonctionelle mais peu facilement upgradable. 

__Soyez patient une version opérationnelle sera prochainement disponible !__

# LAB-HAL
* Contributors: [Christophe Seguinot] (https://github.com/ChristopheSeguinot), [Baptiste Blondelle (wp-HAL)], [friz (wp-HAL)], [CCSD (wp-HAL)] , [Emmanuel Leguy (partie Angular Js glanée sur le site https://www.cristal.univ-lille.fr)]
* Tags: publication, HAL
* Requires at least: 4.9
* Tested up to: 5.1
* Requires PHP: 7 or higher
* Stable tag: [master](https://github.com/cnrs-webkit/lab-hal/releases/latest)
* License: GPLv3
* License URI: <http://www.gnu.org/licenses/gpl-3.0.html>

<!-- ## Description section NOT USED to display description in Wordpress Admin panel --> 

## Description

This plugin allows authors or structures to display their HAL registered publications on a Wordpress article or page.
LAB-HAL publication data are directly extracted from HAL website (http://hal.archives-ouvertes.fr/).

Ce plugin est une adaptation du plugin wp-HAL de Baptiste Blondelle . Il Crée une page qui remonte les publications d'un auteur ou d'une structure en relation avec HAL et un widget des dernières publications d'un auteur ou d'une structure.
 
## Installation / Usage


#### Automated Upgrade procedure :
not available in version 0.0

#### Manual installation/upgrade procedure :

1. Desactivate lab-hal plugin if you have the previous version installed.
2. Unzip "lab-hal" archive and put all files into folder "/wp-content/plugins/lab-hal".
3. Activate "lab-hal" plugin via 'Plugins' menu in WordPress admin menu.

#### How to display publication list on the site ?

You need to create your own page with wordpress and put the shortcode [lab-hal-list] on the content.
Further information goto settings/lab-hal and open upper right help tab.

