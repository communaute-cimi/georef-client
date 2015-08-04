<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">

		<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
		Remove this if you use the .htaccess -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

		<title>index</title>
		<meta name="description" content="">
		<meta name="author" content="CIMI - Eric">

		<meta name="viewport" content="width=device-width; initial-scale=1.0">

		<!-- Replace favicon.ico & apple-touch-icon.png in the root of your domain and delete these references -->
		<link rel="shortcut icon" href="/favicon.ico">
		<link rel="apple-touch-icon" href="/apple-touch-icon.png">

		<script src="res/js/jquery/jquery-2.1.4.js"></script>
		<script>
            function watchPosition() {
                if (navigator.geolocation) {
                    return navigator.geolocation.watchPosition(displayGeolocPosition, showGeolocWatchError, {});
                } else {
                    console.log("Geolocation is not supported by this browser.");
                }
            }

            function displayGeolocPosition(pos) {
            	var lat = String(pos.coords.latitude);
            	var lon = String(pos.coords.longitude);
                $('#pos').empty().append(lat.substr(0,7) + ", " + lon.substr(0,7) + " / (prec. " + Math.round(pos.coords.accuracy) + "m)");

                $.get("http://localhost/www/projets/georef/api/api.php/georef/admin", {
                    y : pos.coords.latitude,
                    x : pos.coords.longitude
                }).done(function(data) {
                    $('#commune').empty().append(data.nom + ", " + data.pop + " hab.,  " + data.z_moyen + "m");
                    $('#unite').empty().append("Zone : " + data.competence + " " + data.type_unite + " " + data.lib_unite);
                });
                
                $.get("http://localhost/www/projets/georef/api/api.php/georef/proxyStation", {
                    y : pos.coords.latitude,
                    x : pos.coords.longitude
                }).done(function(data) {
                    $('#proxStation #pn .distance').empty().append(data[1].distkm + " km");
                    $('#proxStation #pn .station').empty().append(data[1].service);
                    $('#proxStation #pn .tel').empty().append(data[1].tel);
                    
                    $('#proxStation #gn .distance').empty().append(data[0].distkm + " km");
                    $('#proxStation #gn .station').empty().append(data[0].service);
                    $('#proxStation #gn .tel').empty().append(data[0].tel);
                    
                    // $('#unite').empty().append("Zone : " + data.competence + " " + data.type_unite + " " + data.lib_unite);
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

	<body onload="watchPosition();">
		<div>
			<header>
				<h1 align="center">Géopositionnement</h1>
			</header>
			<nav>
			</nav>

			<div style="border:2px solid navy;">
				<div id='pos' style="font-size: 2em;text-align: center;"></div>
				<div id='result'>
					<div id='commune' style="font-size: 2em;text-align: center;">
						
					</div>
					<div id='unite' style="font-size: 2em;text-align: center">
						
					</div>
					<div id='proxStation' style="font-size: 2em;text-align: center">
                        <table align="center">
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
                    
				</div>
			</div>

			<footer>
				<p>
					&copy; Copyright  by CIMI
				</p>
			</footer>
		</div>
	</body>
</html>
