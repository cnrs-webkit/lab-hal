# LAB-HAL
- Contributors: Christophe Seguinot (lab-HAL), Baptiste Blondelle (wp-HAL), friz, CCSD , Emmanuel Leguy (partie Angular Js glanée sur le site https://www.cristal.univ-lille.fr)
- Tags: publication, HAL
- Requires at least: 4.9
- Tested up to: 5.0.3
- Stable tag: 0.0
- License: GPLv3
- License URI: http://www.gnu.org/licenses/gpl-3.0.html

## Description

This plugin allows authors or structures to display their HAL registered publications on a Wordpress article or page.
LAB-HAL publication data are directly extracted from HAL website (http://hal.archives-ouvertes.fr/).

Ce plugin est une adaptation du plugin wp-HAL de Baptiste Blondelle . Il Crée une page qui remonte les publications d'un auteur ou d'une structure en relation avec HAL et un widget des dernières publications d'un auteur ou d'une structure.

- Ce plugin est en cours de développement, quelques fonctionnalités du plugin original ne sont pas encore implémentées. 
- le widget dernières publications n'a pas été impléménte/testé
- le graphique affichant les statistiques par type de publications n'a pas été impléménte/testé
- Merci de remonter les bugs et propositions à l'auteur christophe.seguinot@univ-lille.fr

 
## TODO
- scope.docTypeFilter = null; // Unused ??
- implement version /update... 
- test oldest plugin, remove all non used js/php code
- languages: originaux en français ou anglais?  construire les fichiers de langue
- remove CURL  

## Changelog

Version 0.1 release on ???

- Improve: php file splitted in frontend and admin files 
- Fix : unclosed div cause some template (Lectura) to be unusable 
- Fix : "All" filters not working
- New : Possibility to load truncated publication list to speed up page loading
- Fix : Publications list not loaded when lastyears not initialized
- New : lab-hal now uses angularjs 1.7.7
* New : Truncate long authors list while adding an "et al.." button
* Removed : duplicated javascript code
* 

## Version
Version 0.0 release on 12 february 2019
 
## Installation

# Requires :
- PHP 7 or higher
- WordPress 4.0 or higher

# Automated Upgrade procedure :
not available in version 0.0

# Manual installation/upgrade procedure :

1. Desactivate lab-hal plugin if you have the previous version installed.
2. Unzip "lab-hal" archive and put all files into folder "/wp-content/plugins/lab-hal".
3. Activate "lab-hal" plugin via 'Plugins' menu in WordPress admin menu.

# How to display publication list on the site ?

You need to create your own page with wordpress and put the shortcode [lab-hal-list] on the content.
Further information goto settings/lab-hal and open upper right help tab.

