<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">

		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>index</title>
		<meta name="description" content="">
		<meta name="author" content="CIMI">

		<meta name="viewport" content="width=device-width; initial-scale=1.0">

		<link rel="stylesheet" href="res/js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.css" />
		<script src="res/js/jquery/jquery-2.1.4.js"></script>
		<script src="res/js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.js"></script>

		<script>
            // Optimisation des mises à jour, attendre que toutes les updates soient terminées
            // avant de relancer une interro

            var layersUpdateState = {
                'pointInCommune' : 0,
                'pointInCompetenceTerr' : 0,
                'commissariats' : 0,
                'gendarmeries' : 0,
                'pointInEpci' : 0
            }

            function checkAllUpdatesDone() {
                if (layersUpdateState.pointInCommune == 0 && layersUpdateState.pointInCommune == 0 && layersUpdateState.commissariats == 0 && layersUpdateState.gendarmeries == 0) {
                    return true
                } else
                    return false;
            }

            var lastPos = {
                'x' : null,
                'y' : null
            }

            var currentPos = {
                'x' : null,
                'y' : null
            }

            // Après le chargement du DOM
            function afterLoad() {
                $("#admin").hide();
                $("#proxStation").hide();

                // Démarrer la récupération de la position
                watchPosition();
            }

            function watchPosition() {
                if (navigator.geolocation) {

                    $.mobile.loading("show", {
                        text : "Chargement des informations",
                        textVisible : true,
                        theme : "z",
                        html : ""
                    });

                    return navigator.geolocation.watchPosition(newGeolocPosition, showGeolocWatchError, {});
                } else {
                    console.log("Geolocation is not supported by this browser.");
                }
            }

            // A chaque nouvelle position détectée
            function newGeolocPosition(pos) {
                lastPos.x = currentPos.x;
                lastPos.y = currentPos.y;

                currentPos.x = String(pos.coords.longitude);
                currentPos.y = String(pos.coords.latitude);

                var dist = calculateDistance(lastPos.y, lastPos.x, currentPos.y, currentPos.x, 'K');

                $('#admin #loc #lat').empty().append(currentPos.y.substr(0, 7));
                $('#admin #loc #lon').empty().append(currentPos.x.substr(0, 7));
                $('#admin #loc #prec').empty().append(Math.round(pos.coords.accuracy));
                $('#admin #loc #dist').empty().append(Math.round(dist));

                $.mobile.loading("hide");

                if (checkAllUpdatesDone()) {
                    updateLayers();
                }
            }

            function updateLayers() {
                // Etat de mise à jour, tout mettre en cours de maj
                layersUpdateState.pointInCommune = 1;
                layersUpdateState.pointInCompetenceTerr = 1;
                layersUpdateState.commissariats = 1;
                layersUpdateState.gendarmeries = 1;
                layersUpdateState.pointInEpci = 1;
                
                $.get("http://localhost/www/projets/georef-api/api.php/layers/pointInCommune", {
                    y : currentPos.y,
                    x : currentPos.x
                }).done(function(data) {
                    layersUpdateState.pointInCommune = 0;
                    $('#admin #commune #name').empty().append(data.nom);
                    $('#admin #commune #pop').empty().append(data.pop);
                    console.log(layersUpdateState);
                }).error(function() {
                    layersUpdateState.pointInCommune = 0;
                });

                $.get("http://localhost/www/projets/georef-api/api.php/layers/pointInCompetenceTerr", {
                    y : currentPos.y,
                    x : currentPos.x
                }).done(function(data) {
                    layersUpdateState.pointInCompetenceTerr = 0;
                    $('#admin #compet #competence').empty().append(data.competence);
                    $('#admin #compet #type_unite').empty().append(data.type_unite);
                    $('#admin #compet #lib_unite').empty().append(data.lib_unite);
                }).error(function() {
                    layersUpdateState.pointInCompetenceTerr = 0;
                });

                $.get("http://localhost/www/projets/georef-api/api.php/layers/pointInEpci", {
                    y : currentPos.y,
                    x : currentPos.x
                }).done(function(data) {
                    layersUpdateState.pointInEpci = 0;
                    $('#admin #epci #type_epci').empty().append(data.type_epci);
                    $('#admin #epci #nom_epci').empty().append(data.nom_epci);
                }).error(function() {
                    layersUpdateState.pointInEpci = 0;
                });

                $.get("http://localhost/www/projets/georef-api/api.php/layers/commissariats", {
                    y : currentPos.y,
                    x : currentPos.x
                }).done(function(data) {
                    layersUpdateState.commissariats = 0;
                    $('#proxStation #pn .distance').empty().append(data.distkm + " km");
                    $('#proxStation #pn .service').empty().append(data.service);
                    $('#proxStation #pn .tel').empty().append(data.tel);
                }).error(function() {
                    layersUpdateState.commissariats = 0;
                });

                $.get("http://localhost/www/projets/georef-api/api.php/layers/gendarmeries", {
                    y : currentPos.y,
                    x : currentPos.x
                }).done(function(data) {
                    layersUpdateState.gendarmeries = 0;
                    $('#proxStation #gn .distance').empty().append(data.distkm + " km");
                    $('#proxStation #gn .service').empty().append(data.service);
                    $('#proxStation #gn .tel').empty().append(data.tel);

                    $("#admin").show();
                    $("#proxStation").show();

                }).error(function() {
                    layersUpdateState.gendarmeries = 0;
                });
            }

            function showGeolocWatchError(error) {
                switch(error.code) {
                case error.PERMISSION_DENIED:
                    console.error("User denied the request for Geolocation.");
                    break;
                case error.POSITION_UNAVAILABLE:
                    console.error("Location information is unavailable.");
                    break;
                case error.TIMEOUT:
                    console.error("The request to get user location timed out.");
                    break;
                case error.UNKNOWN_ERROR:
                    console.error("An unknown error occurred.");
                    break;
                }
            }

            if ( typeof (Number.prototype.toRad) === "undefined") {
                Number.prototype.toRad = function() {
                    return this * Math.PI / 180;
                }
            }
            
            function calculateDistance(lat1, lon1, lat2, lon2, unit) {
                var radlat1 = Math.PI * lat1/180
                var radlat2 = Math.PI * lat2/180
                var radlon1 = Math.PI * lon1/180
                var radlon2 = Math.PI * lon2/180
                var theta = lon1-lon2
                var radtheta = Math.PI * theta/180
                var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
                dist = Math.acos(dist)
                dist = dist * 180/Math.PI
                dist = dist * 60 * 1.1515
                if (unit=="K") { dist = dist * 1.609344 }
                if (unit=="N") { dist = dist * 0.8684 }
                return dist
            }          

		</script>

	</head>

	<body onload="afterLoad();">
		<div data-role="page">
			<div data-role="header">
				<h1>Géoref</h1>

				<a href="#" data-icon="gear" class="ui-btn-right">Options</a>

			</div>
			<div role="main" class="ui-content">
				<table id="admin" data-role="table" data-mode="column"
				class="ui-body-d ui-shadow table-stripe ui-responsive"
				data-column-btn-theme="b" data-column-popup-theme="a">
					<tr>
						<th colspan="2">Position, commune et compétence PN/GN</th>
					</tr>
					<tr id="loc">
						<td class="title"> Position :</td>
						<td class="value"><span id="lat"></span>/<span id="lon"></span> (Prec. <span id="prec"></span>m) - dist : <span id="dist"></span> km</td>
					</tr>
					<tr id="commune">
						<td class="title">Commune&nbsp;:</td>
						<td class="value"><span id="name"></span>, <span id="pop"></span> hab.</td>
					</tr>
					<tr id="compet">
						<td class="title">Compétence&nbsp;:</td>
						<td class="value">Zone <span id="competence"></span>, <span id="type_unite"></span> <span id="lib_unite"></span></td>
					</tr>
                    <tr id="epci">
                        <td class="title">EPCI&nbsp;:</td>
                        <td class="value"><span id="nom_epci"></span> </td>
                    </tr>
					
				</table>
				<br/>
				<table id="proxStation" data-role="table" data-mode="column"
				class="ui-body-d ui-shadow table-stripe ui-responsive"
				data-column-btn-theme="b" data-column-popup-theme="a">
					<tr>
						<th colspan="2">Accueil PN/GN le + proche</th>
					</tr>
					<tr id="pn">
						<td class="distance"></td>
						<td class="service"></td>
					</tr>
					<tr id="gn">
						<td class="distance"></td>
						<td class="service"></td>
					</tr>
				</table>
			</div>
			<div data-role="footer">
				<h3>By CIMI</h3>
			</div>
		</div>
	</body>
</html>
