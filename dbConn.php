<!--
    Younes Azamiyan
    20017620
    Wednesday 9-11 Practical
-->

<!-- Setting up the connection to the electrical database -->
<?php
    $dbConn = new mysqli("localhost", "twa348", "twa348C4", "A_League2021_348");
        // Terminate connection and display the error
        if($dbConn->connect_error) {
            die("Failed to connect to database " . $dbConn->connect_error);
    }
?>
