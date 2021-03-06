<?php
    session_start();
    header("Cache-Control: no-cache");
    header("Expires: -1");
    require_once("dbConn.php");

    $tableName = 'teams'; // Table name
    $ladder = array();

    $sql = "SET @row_num=0";
    $results = $dbConn->query($sql)
    or die ('Problem with query: ' . $dbConn->error);

    $sql = "SELECT teamID, (@row_num:=@row_num+1) '#', emblem, teamName 'Club',
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
    <link rel="stylesheet" href="css/ladderStyles.css">
</head>
<body>
    <header>
      <nav>
        <div class="user_tag">
          <?php if (!empty($_SESSION["who"])): ?>
              <p>Welcome <?php echo $_SESSION["firstname"]; ?>!</p>
          <?php endif; ?>
        </div>
        <div class="nav_links">
          <a href='index.php'>Home</a>
          <a href='ladder.php'>Ladder</a>
          <a href='fixture.php'>Fixtures</a>
          <a href='scoreEntry.php'>Score Entry</a>
          <?php if (empty($_SESSION["who"])): ?>
              <a href='login.php'>Login</a>
          <?php else: ?>
              <a href='logoff.php'>Logoff</a>
          <?php endif; ?>
        </div>
      </nav>
      <h1>2021 A-League Ladder</h1>
    </header>

    <!-- Actually useless but hard to get rid of; this is for printing multiple table; in this case you have only one table. -->
    <?php foreach ($ladder as $table): ?>
        <table>
            <!-- This (tr) is only the first row of the table; the headings. -->
            <tr>
                <?php
                    // Prints each heading of the table adding a column with its name to the table
                    foreach($table['fields'] as $field) {
                        if ($field->name === "teamID") {
                            continue;
                        }
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
            ?>
            <!-- After printing table headings (or adding columns), fill the table with its data -->
            <?php while($row = $results->fetch_assoc()): ?>
                <tr>
                    <?php
                        // output the value of each key; the data
                        foreach($row as $key => $value) {
                            if ($key == "teamID") {
                                continue;
                            }
                            // If the value matches to the regular expression of the emblem's file name, add emblem's image
                            if (preg_match($pattern, $value)) {
                                echo "<td><img src='images/" . $value . "' alt='Team's Logo' width='25'></td>";
                            } // else add the value (string) itself to the table
                            else {
                                echo "<td>" . $value . "</td>";
                            }
                        }

                        echo "<td>";
                            // Query to generate the last five matches of each team

                            $lastFive = "SELECT * FROM fixtures ";
                            $lastFive = $lastFive . "WHERE ((awayTeam = ?) OR (homeTeam = ?)) AND ((matchDate <= NOW()) AND (score1 IS NOT NULL)) ";
                            $lastFive = $lastFive . " ORDER BY matchDate DESC LIMIT 5";

                            $statement = $dbConn->prepare($lastFive);
                            $statement->bind_param("ii", $row["teamID"], $row["teamID"]);
                            $statement->execute();
                            $lastFiveMatches = $statement->get_result();

                            // The following loop goes through the last five matches to determine the match status
                            while($match = $lastFiveMatches->fetch_assoc()) {
                                if ($match["homeTeam"] == $row["teamID"]) {
                                    $difference = $match["score1"] - $match["score2"];
                                    // if difference is less than zero, the team has lost
                                    if (intval($difference) < 0) {
                                        echo "<img src='images/redcircle.png' alt='Lost' width='20'>";
                                    } // if difference is more than zero, the team has won
                                    elseif (intval($difference) > 0) {
                                        echo "<img src='images/greencircle.png' alt='Win' width='20'>";
                                    } // if difference is equal to zero, the has drawn
                                    else {
                                        echo "<img src='images/greycircle.png' alt='Draw' width='20'>";
                                    }
                                }
                                elseif ($match["awayTeam"] == $row["teamID"]) {
                                    $difference = $match["score2"] - $match["score1"];
                                    // if difference is less than zero, the team has lost
                                    if (intval($difference) < 0) {
                                        echo "<img src='images/redcircle.png' alt='Lost' width='20'>";
                                    } // if difference is more than zero, the team has won
                                    elseif (intval($difference) > 0) {
                                        echo "<img src='images/greencircle.png' alt='Win' width='20'>";
                                    } // if difference is equal to zero, the has drawn
                                    else {
                                        echo "<img src='images/greycircle.png' alt='Draw' width='20'>";
                                    }
                                }
                            }
                        echo "</td>";
                    ?>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endforeach; ?>
    <?php $dbConn->close(); ?>
</body>
</html>
