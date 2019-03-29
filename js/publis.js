/**
 * Plugin lab-hal
 *
 * @package     lab-hal
 * @since       0.0
 * @author      Christophe Seguinot
 * @link        https://github.com/cnrs-webkit/lab-hal
 * @license     GPL-3.0
 */
 

var app = angular.module('PublisApp',["ngSanitize"]);

app.factory('PublisFactory', function($http, $q){
	var factory = {
		publis : false,
		lastquery : "",
		getPublis : function ($filtre, $nbMaxEntrees) {
			var nbMaxEntrees = $nbMaxEntrees || 300;
			var deferred = $q.defer();
			if (factory.lastquery !== $filtre) {
				factory.lastquery = $filtre;
			}
			factory.publis = lab_hal_publis.response.docs;
			deferred.resolve(factory.publis);
			return deferred.promise;
		},

	}
	return factory;
});
		
app.controller("PublisEquipeCtrl",function ($scope, PublisFactory) {
	var showAll=false; 
	$scope.query = lab_hal_query;
	$scope.lastquery = "";
	$scope.debut = true;
	$scope.docTypeFilter = null; // Unused ?? 
	$scope.nbPublis = 0;
	$scope.loading = true;
	$scope.docTypes = null;
	$scope.annees = null;
	$scope.auteurs = null;
	var currentTime = new Date();
	$scope.currentYear = currentTime.getFullYear();
	$scope.yearfrom = lab_hal_yearfrom;
	$scope.lab_hal_max_authors = lab_hal_max_authors;
	$scope.nbItemsToDisplay = function(data) {
	        return showAll? $scope.auteurs.length : 10;
	    };
	$scope.showAllLessItems = function() {
	        showAll = !showAll;       
	    };

	PublisFactory.getPublis($scope.query, 99999).then(
		function(publis){
			$scope.docTypeFilter = null;
			
			$scope.publis = publis;
			$scope.nbPublis = $scope.publis.length;
			$scope.lastquery = $scope.query;
			$scope.docTypes = [];
			$scope.annees = [];
			$scope.auteurs = [];
			$scope.totalPardocTypes = [];
			$scope.totalParannees = [];
			$scope.totalParauteurs = [];
			$scope.limitValue = 120;
			$scope.totallastyears = 0;
			$scope.fullist= ($scope.nbPublis < lab_hal_nbMaxEntrees);
			
					
			publis.forEach(function(publi){
				if ($scope.docTypes.indexOf(publi.docType_s) == -1) {
					$scope.docTypes.push(publi.docType_s);
					$scope.totalPardocTypes[publi.docType_s]=0;
				}
				$scope.totalPardocTypes[publi.docType_s]++ ; 

				if ($scope.annees.indexOf(publi.producedDateY_i) == -1) {
					$scope.annees.push(publi.producedDateY_i);
					$scope.totalParannees[publi.producedDateY_i]=0;
				}
				$scope.totalParannees[publi.producedDateY_i]++ ; 
				
				if (publi.producedDateY_i >= $scope.yearfrom) {
					publi.lastyears=1;
					$scope.totallastyears++;
				} else {
					publi.lastyears=0;
				}
			
				publi.authFullName_s.forEach(function(auteur){
					if ($scope.auteurs.indexOf(auteur) == -1) {
						$scope.auteurs.push(auteur);
						$scope.totalParauteurs[auteur]=0;
					}
					$scope.totalParauteurs[auteur]++ ; 
				});
			});
			var tuples = [];

			for (var key in $scope.totalParauteurs) tuples.push([key, $scope.totalParauteurs[key]]);
			
			tuples.sort(function(a, b) {
			    a = a[1];
			    b = b[1];
			    return a < b ? 1 : (a > b ? -1 : 0);
			});
			
			$scope.auteurs=[]; 
			for (var i = 0; i < tuples.length; i++) {
			    var key = tuples[i][0];
			    var value = tuples[i][1];
			    $scope.auteurs.push(tuples[i][0]);
			}
			
			$scope.loading = false;
			$scope.debut = false;
			
		},
		function(msg) {
			alert(msg);
		}
	);
	$scope.docTypeLabel = {
			"ART":"Journal articles",
			"COMM":"Conference papers",
			"POSTER":"Poster communications",
			"PRESCONF":"Documents associated with scientific events",
			"OUV":"Books",
			"COUV":"Book sections",
			"DOUV":"Directions of work or proceedings",
			"PATENT":"Patents",
			"OTHER":"Other publications",
			"UNDEFINED":"Preprints, Working Papers, ...",
			"REPORT":"Reports",
			"THESE":"Theses",
			"HDR":"HDR",
			"MEM":"Master thesis",
			"LECTURE":"Lectures",
			"IMG":"Photos",
			"VIDEO":"Videos",
			"SON":"Audio",
			"MAP":"Maps",
			"MINUTES":"Minutes",
			"NOTE":"Book reviews",
			"OTHERREPORT":"Other reports",
			"OTHER": "Other"
	}

	
});


