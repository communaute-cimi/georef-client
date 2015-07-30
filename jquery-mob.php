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
                    
                    return navigator.geolocation.watchPosition(displayGeolocPosition, showGeolocWatchError, {});
                } else {
                    console.log("Geolocation is not supported by this browser.");
                }
            }

            function displayGeolocPosition(pos) {

                var lat = String(pos.coords.latitude);
                var lon = String(pos.coords.longitude);

                $('#admin #pos #lat').empty().append(lat.substr(0, 7));
                $('#admin #pos #lon').empty().append(lon.substr(0, 7));
                $('#admin #pos #prec').empty().append(Math.round(pos.coords.accuracy));

                $.get("http://localhost/www/projets/georef-api/api.php/layers/pointInCommune", {
                    y : pos.coords.latitude,
                    x : pos.coords.longitude
                }).done(function(data) {
                    $('#admin #commune #name').empty().append(data.nom);
                    $('#admin #commune #pop').empty().append(data.pop);
                });

                $.get("http://localhost/www/projets/georef-api/api.php/layers/pointInCompetenceTerr", {
                    y : pos.coords.latitude,
                    x : pos.coords.longitude
                }).done(function(data) {
                    $('#admin #compet #competence').empty().append(data.competence);
                    $('#admin #compet #type_unite').empty().append(data.type_unite);
                    $('#admin #compet #lib_unite').empty().append(data.lib_unite);
                });

                $.get("http://localhost/www/projets/georef-api/api.php/layers/commissariats", {
                    y : pos.coords.latitude,
                    x : pos.coords.longitude
                }).done(function(data) {
                    $('#proxStation #pn .distance').empty().append(data.distkm + " km");
                    $('#proxStation #pn .station').empty().append(data.service);
                    $('#proxStation #pn .tel').empty().append(data.tel);
                });

                $.get("http://localhost/www/projets/georef-api/api.php/layers/gendarmeries", {
                    y : pos.coords.latitude,
                    x : pos.coords.longitude
                }).done(function(data) {
                    $('#proxStation #gn .distance').empty().append(data.distkm + " km");
                    $('#proxStation #gn .station').empty().append(data.service);
                    $('#proxStation #gn .tel').empty().append(data.tel);
                    
                    $.mobile.loading("hide");
                    $("#admin").show();
                    $("#proxStation").show();
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
		</script>

	</head>

	<body onload="afterLoad();">
		<div data-role="page">
			<div data-role="header">
				<h1>CIMI Géoref</h1>
			</div>
			<div role="main" class="ui-content">
				<table id="admin" data-role="table" data-mode="column"
				class="ui-body-d ui-shadow table-stripe ui-responsive"
				data-column-btn-theme="b" data-column-popup-theme="a">
					<tr>
						<th colspan="2">Position, commune et compétence PN/GN</th>
					</tr>
					<tr id="pos">
						<td class="title"> Position : </td>
						<td class="value"><span id="lat"></span>/<span id="lon"></span> (Prec. <span id="prec"></span>m)</td>
					</tr>
					<tr id="commune">
						<td class="title">Commune : </td>
						<td class="value"><span id="name"></span>, <span id="pop"></span> hab. <span id="alt"></span>m.</td>
					</tr>
					<tr id="compet">
						<td class="title">Compétence : </td>
						<td class="value">Zone <span id="competence"></span>, <span id="type_unite"></span> <span id="lib_unite"></span></td>
					</tr>
				</table>
				<br/>
				<table id="proxStation" data-role="table" data-mode="column"
				class="ui-body-d ui-shadow table-stripe ui-responsive"
				data-column-btn-theme="b" data-column-popup-theme="a">
					<tr>
						<th colspan="3">Points accueil PN/GN les plus proches</th>
					</tr>
					<tr>
						<th>Dist.</th>
						<th>Unité</th>
						<th>Tel</th>
					</tr>
					<tr id="pn">
						<td class="distance"></td>
						<td class="station"></td>
						<td class="tel"></td>
					</tr>
					<tr id="gn">
						<td class="distance"></td>
						<td class="station"></td>
						<td class="tel"></td>
					</tr>
				</table>
			</div>
			<div data-role="footer">
				<h3>By CIMI</h3>
			</div>
		</div>

	</body>
</html>
