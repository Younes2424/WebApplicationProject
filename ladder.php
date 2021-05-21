<?php
    session_start();
    header("Cache-Control: no-cache");
    header("Expires: -1");
    require_once("conn.php");
    $tableName = 'teams'; // Table name
    $ladder = array();
    $week = $_SESSION["weekNo"];
    $sql = "SET @row_num=0";
    $results = $dbConn->query($sql)
    or die ('Problem with query: ' . $dbConn->error);
    $sql = "SELECT (@row_num:=@row_num+1) '#', emblem, teamName 'Club',
            played 'P', won 'W', drawn 'D', lost 'L', goalsFor 'GF', goalsAgainst 'GA',
            goalDiff 'GD', points AS 'Pts'";
    $sql = $sql . "FROM teams ORDER BY points DESC, goalDiff DESC";
    $results = $dbConn->query($sql)
    or die ('Problem with query: ' . $dbConn->error);
    array_push($ladder, array(
        'name' => $tableName, // Table name
        'fields' => $results->fetch_fields(), // Column headings
        'data' => $results // Actual Data
    ));

    $lastFive = "SELECT homeTeam AS id, teamName, matchDate, (score1 - score2) AS difference FROM fixtures, teams
                 WHERE (score1 IS NOT NULL) AND (score2 IS NOT NULL) AND (homeTeam = teamID)
                 UNION ALL
                 SELECT awayTeam AS id, teamName, matchDate, (score2 - score1) AS difference FROM fixtures, teams
                 WHERE (score1 IS NOT NULL) AND (score2 IS NOT NULL) AND (awayTeam = teamID)
                 ORDER BY id, matchDate DESC";
    // Save the result of the SQL query or terminate database
    // connection with an appropriate message
    $lastFiveMatches = $dbConn->query($lastFive)
    or die ('Problem with query: ' . $dbConn->error);

    // function getTeamName($rank) {
    //     while ($position = $results->fetch_assoc()) {
    //         if ($rank === intval($position["#"])) {
    //             return $position["Club"];
    //         }
    //     }
    // }
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
    <title><?php echo "A-League Ladder | " . date("M d") ?></title>
    <link rel="stylesheet" href="css/projectMaster.css">
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
    </header>

    <!-- Actually useless but hard to get rid of; this is for printing multiple table; in this case you have only one table. -->
    <?php foreach ($ladder as $table): ?>
        <table>
            <!-- This (tr) is only the first row of the table; the headings. -->
            <tr>
                <?php
                    // Prints each heading of the table adding a column with its name to the table
                    foreach($table['fields'] as $field) {
                        // If the field name is "emblem", name the heading as Club
                        if ($field->name === "emblem") {
                            echo "<th>Club</th>";
                        } // Else if the name of the field is "Club", leave the heading empty to merge the logo with team name
                        elseif ($field->name === "Club") {
                            echo "<th></th>"; // So the logo and team name appear as one column
                        } // Else print the rest of the headings as they are
                        else {
                            echo "<th>" . $field->name . "</th>";
                        }
                    }
                ?>
                <!-- After all the columns have been added, add the last five column at the end. -->
                <th>Last Five</th>
            </tr>
            <?php
                $pattern = "/^[a-zA-Z0-9]*(.png)$/"; // Regular expression for emblems' file names
                // An array of regular expressions for team names
                $teamsPatterns = array("/^(Adelaide United)$/", "/^(Brisbane Roar)$/", "/^(Central Coast Mariners)$/",
                                       "/^(Macarthur FC)$/", "/^(Melbourne City)$/", "/^(Melbourne Victory)$/",
                                       "/^(Newcastle Jets)$/", "/^(Perth Glory)$/", "/^(Sydney FC)$/",
                                       "/^(Wellington Phoenix)$/", "/^(Western Sydney Wanderers)$/", "/^(Western United FC)$/");
            ?>
            <!-- After printing table headings (or adding columns), fill the table with its data -->
            <?php while($row = $table['data']->fetch_assoc()): ?>
                <tr>
                    <?php
                        // output the value of each key; the data
                        foreach($row as $value) {
                            // If the value matches to the regular expression of the emblem's file name, add emblem's image
                            if (preg_match($pattern, $value)) {
                                echo "<td><img src='images/" . $value . "' alt='Team Logo' width='25'></td>";
                            } // else add the value (string) itself to the table
                            else {
                                echo "<td>" . $value . "</td>";
                            }
                        }

                        $index = 0;
                        echo "<td>"; // The order of teamNames in lastFiveMatches doesn't match the ladder
                                    //  Therefore the icons doesn't appear correctly. You're getting closer.
                        foreach($teamsPatterns as $element) {
                            while(($match = $lastFiveMatches->fetch_assoc()) && ($index < 5)) {
                                if (preg_match($element, $match["teamName"])) {
                                    if (intval($match["difference"]) < 0) {
                                        echo "<img src='images/redcircle.png' alt='Lost' width='20'>";
                                    }
                                    elseif (intval($match["difference"]) > 0) {
                                        echo "<img src='images/greencircle.png' alt='Win' width='20'>";
                                    }
                                    else {
                                        echo "<img src='images/greycircle.png' alt='Draw' width='20'>";
                                    }
                                }
                                $index++;
                            }
                        }
                        echo "</td>";
                    ?>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endforeach; ?>
</body>
</html>
