<?php
    session_start();
    header("Cache-Control: no-cache");
    header("Expires: -1");
    require_once("conn.php");
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $sql = "SELECT * FROM venues WHERE venueID = (SELECT venueID FROM fixtures WHERE matchID = ?)";
    $statement = $dbConn->prepare($sql);
    $statement->bind_param("i", $_GET["matchID"]);
    $statement->execute();
    if ($venue = $statement->get_result()) {
        $venue = $venue->fetch_object();
        $venueName = $venue->venueName;
    }
    else {
        $error = $dbConn->errno . ' ' . $dbConn->error;
        echo $error;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!--
        Younes Azamiyan
        20017620
        Wednesday 9-11 Practical
    -->
    <meta charset="UTF-8">
    <title><?php echo "A-League Venue | " . $venueName ?></title>
    <link rel="stylesheet" href="css/projectMaster.css">
    <style type="text/css">
        /* Set the size of the div element that contains the map */
        #map {
            height: 400px;
            /* The height is 400 pixels */
            width: 400px;
            /* The width is the width of the web page */
        }
    </style>
    <script>
        // The following function is from https://developers.google.com/maps/documentation/javascript/adding-a-google-map#key
        // Initialize and add the map
        function initMap() {
            // The location of the venue
            var venue = { lat: <?php echo $venue->latitude ?>, lng: <?php echo $venue->longitude ?> };
            // The map, centered at the venue
            var map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: venue,
            });
            // The marker, positioned at the venue
            var marker = new google.maps.Marker({
                position: venue,
                map: map,
            });
        }
    </script>
</head>
<body>
    <header>
      <nav>
          <a href='index.php'>Home</a>
          <a href='ladder.php'>Ladder</a>
          <a href='fixture.php'>Fixtures</a>
          <a href='scoreEntry.php'>Score Entry</a>
          <a href='login.php'>Login</a>
          <a href='logoff.php'>Logoff</a>
      </nav>
        <h1><?php echo $venueName ?></h1>
    </header>

    <p><strong>Venue Address: </strong><?php echo $venue->address ?></p>

    <!--The div element for the map -->
    <div id="map"></div>

    <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC9Uv3GBri_zroYJx3XStQlF3etFM_9LHw&callback=initMap&libraries=&v=weekly"
        async
    ></script>
</body>
</html>
