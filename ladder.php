<?php
    session_start();
    header("Cache-Control: no-cache");
    header("Expires: -1");
    require_once("conn.php");
    $tableName = 'teams';
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
        'name' => $tableName,
        'fields' => $results->fetch_fields(),
        'data' => $results
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

    <?php foreach ($ladder as $table): ?>
        <table>
            <tr>
                <?php
                    foreach($table['fields'] as $field) {
                        if ($field->name === "emblem") {
                            echo "<th>Club</th>";
                        }
                        elseif ($field->name === "Club") {
                            echo "<th></th>";
                        }
                        else {
                            echo "<th>" . $field->name . "</th>";
                        }
                    }
                ?>
                <th>Last Five</th>
            </tr>
            <?php
                $pattern = "/^[a-zA-Z0-9]*(.png)$/";
                $teamsPatterns = array("/^(Adelaide United)$/", "/^(Brisbane Roar)$/", "/^(Central Coast Mariners)$/",
                                       "/^(Macarthur FC)$/", "/^(Melbourne City)$/", "/^(Melbourne Victory)$/",
                                       "/^(Newcastle Jets)$/", "/^(Perth Glory)$/", "/^(Sydney FC)$/",
                                       "/^(Wellington Phoenix)$/", "/^(Western Sydney Wanderers)$/", "/^(Western United FC)$/");
            ?>
            <?php while($row = $table['data']->fetch_assoc()): ?>
                <tr>
                    <?php
                        foreach($row as $key => $value) {
                            if (preg_match($pattern, $value)) {
                                echo "<td><img src='images/" . $value . "' alt='Team Logo' width='25'></td>";
                            }
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
