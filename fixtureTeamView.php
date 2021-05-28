<?php
    session_start();
    header("Cache-Control: no-cache");
    header("Expires: -1");
    require_once("dbConn.php");
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $today = strval(date("Y-m-d"));   //get the server date in the correct format
    //query the db here for the week number based on the server date
    $statement = $dbConn->prepare("SELECT weekID FROM weeks WHERE (startDate <= ?) AND (endDate >= ?)");
    $statement->bind_param("ss", $today, $today);
    $statement->execute();
    $result = $statement->get_result();
    if ($result->num_rows) {
        $currentWeek = $result->fetch_object();
        $currentWeek = $currentWeek->weekID;
    }
    else {
        echo "<p>The season has ended.</p>";
    }

    function sanitiseInput($data) {
        // function from https://www.w3schools.com/php/php_form_validation.asp
        $data = trim($data); // remove spaces, tabs, etc
        $data = stripslashes($data); // remove backslash
        $data = htmlspecialchars($data); // converts special characters to their html code
        return $data;
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
    <title><?php echo "A-League Fixtures | " . date("M d") ?></title>
    <link rel="stylesheet" href="css/projectMaster.css">
    <link rel="stylesheet" href="css/fixtureStyles.css">
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
      <h1>2021 A-League Fixtures</h1>
    </header>

    <div class="fixtureView">
        <a href="fixture.php"><button>Weekly View</button></a>
        <a href="fixtureTeamView.php"><button>Team View</button></a>
    </div>

    <div class="center">
        <div class="dateAndWeek">
            <?php
                echo "<p><strong>Date: </strong>" . $today . "</p>";
                if (!empty($currentWeek)) {
                    echo "<p><strong>Current Week: </strong>" . $currentWeek . "</p>";
                }

                $sql = "SELECT teamID, teamName FROM teams";
                $results = $dbConn->query($sql)
                or die ('Problem with query: ' . $dbConn->error);
            ?>
        </div>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="teamID">Team:</label>
            <select id="teamID" name="teamID" size="1" autofocus>
              <?php while ($team = $results->fetch_assoc()): ?>
                   <option value="<?php echo $team["teamID"] ?>"><?php echo $team["teamName"] ?></option>
               <?php endwhile; ?>
            </select>
            <br><input type="submit" name="submit" value="Submit">
        </form>
    </div>

    <?php if (!empty($currentWeek)): ?>
        <?php
            if (!empty($_POST["teamID"])) {
                $_SESSION["teamID"] = sanitiseInput($_POST["teamID"]);
            }

            if (!empty($_SESSION["teamID"])) {
                $teamID = $_SESSION["teamID"];
                $sql = "SELECT teamID, teamName, emblem FROM teams WHERE teamID = ?";
                $results = $dbConn->prepare($sql);
                $results->bind_param("i", $teamID);
                $results->execute();
                $results = $results->get_result();
                $results = $results->fetch_object();

                echo "
                    <div class='teamView'>
                        <img src='images/" . $results->emblem . "' alt='Team's Logo' width='100'>
                        <h2>  " . $results->teamName . " Fixtures</h2>
                    </div>
                ";

                $sql = "SELECT matchID, weekID, homeTeam, awayTeam, venueID, ";
                $sql = $sql . "DATE_FORMAT(matchDate, '%d %M %Y') 'matchDate', DATE_FORMAT(matchTime, '%H:%i') AS 'matchTime', ";
                $sql = $sql . "score1, score2 FROM fixtures WHERE (weekID >= ?) AND ((homeTeam = ?) OR (awayTeam = ?))";
                if ($statement = $dbConn->prepare($sql)) {
                    $statement->bind_param("iii", $currentWeek, $teamID, $teamID);
                    $statement->execute();
                    $matches = $statement->get_result();
                }
                else {
                    $error = $dbConn->errno . ' ' . $dbConn->error;
                    echo $error;
                }
            }
        ?>

        <?php if ((!empty($_SESSION["teamID"])) && (!empty($matches))): ?>
            <?php while ($match = $matches->fetch_assoc()): ?>
                <?php
                    if ($match["homeTeam"] === $teamID) {
                        $sql = "SELECT teamName, emblem FROM teams WHERE teamID = ?";
                        if ($dynamicStatement1 = $dbConn->prepare($sql)) {
                            $dynamicStatement1->bind_param("i", $match["homeTeam"]);
                            $dynamicStatement1->execute();
                            $homeClub = $dynamicStatement1->get_result();
                            $homeClub = $homeClub->fetch_object();
                        }
                        else {
                            $error = $dbConn->errno . ' ' . $dbConn->error;
                            echo $error;
                        }

                        $sql = "SELECT teamName, emblem FROM teams WHERE teamID = ?";
                        if ($dynamicStatement2 = $dbConn->prepare($sql)) {
                            $dynamicStatement2->bind_param("i", $match["awayTeam"]);
                            $dynamicStatement2->execute();
                            $awayClub = $dynamicStatement2->get_result();
                            $awayClub = $awayClub->fetch_object();
                        }
                        else {
                            $error = $dbConn->errno . ' ' . $dbConn->error;
                            echo $error;
                        }
                    }
                    else {
                        $sql = "SELECT teamName, emblem FROM teams WHERE teamID = ?";
                        if ($dynamicStatement1 = $dbConn->prepare($sql)) {
                            $dynamicStatement1->bind_param("i", $match["homeTeam"]);
                            $dynamicStatement1->execute();
                            $homeClub = $dynamicStatement1->get_result();
                            $homeClub = $homeClub->fetch_object();
                        }
                        elseif ($match["awayTeam"] === $teamID) {
                            $error = $dbConn->errno . ' ' . $dbConn->error;
                            echo $error;
                        }

                        $sql = "SELECT teamName, emblem FROM teams WHERE teamID = ?";
                        if ($dynamicStatement2 = $dbConn->prepare($sql)) {
                            $dynamicStatement2->bind_param("i", $match["awayTeam"]);
                            $dynamicStatement2->execute();
                            $awayClub = $dynamicStatement2->get_result();
                            $awayClub = $awayClub->fetch_object();
                        }
                        else {
                            $error = $dbConn->errno . ' ' . $dbConn->error;
                            echo $error;
                        }
                    }

                    $sql = "SELECT venueName FROM venues WHERE venueID = ?";
                    if ($dynamicStatement3 = $dbConn->prepare($sql)) {
                        $dynamicStatement3->bind_param("i", $match["venueID"]);
                        $dynamicStatement3->execute();
                        $venue = $dynamicStatement3->get_result();
                        $venue = $venue->fetch_object();
                    }
                    else {
                        $error = $dbConn->errno . ' ' . $dbConn->error;
                        echo $error;
                    }
                ?>

                <?php if (is_numeric($match["score1"])): ?>
                <?php echo "<a href='venue.php?matchID=" . $match["matchID"] . "'>" ?>
                        <table class="fixture_box fulltime">
                            <tr>
                                <td><?php echo $match["weekID"]; ?></td>
                                <td colspan="5">Full-time</td>
                                <td><?php echo $match["matchDate"]; ?></td>
                            </tr>
                            <tr>
                                <td><?php echo $homeClub->teamName ?></td>
                                <td><img src="images/<?php echo $homeClub->emblem ?>" alt="Home Team's Logo" width="50"></td>
                                <td><?php echo $match["score1"] ?></td>
                                <td>-</td>
                                <td><?php echo $match["score2"] ?></td>
                                <td><img src="images/<?php echo $awayClub->emblem ?>" alt="Away Team's Logo" width="50"></td>
                                <td><?php echo $awayClub->teamName ?></td>
                            </tr>
                            <tr>
                                <td colspan="7"><?php echo $venue->venueName ?></td>
                            </tr>
                        </table>
                    </a>
                <?php else: ?>
                <?php echo "<a href='venue.php?matchID=" . $match["matchID"] . "'>" ?>
                        <table class="fixture_box scheduled">
                            <tr>
                                <td><?php echo $match["weekID"]; ?></td>
                                <td colspan="3"></td>
                                <td><?php echo $match["matchDate"]; ?></td>
                            </tr>
                            <tr>
                                <td><?php echo $homeClub->teamName ?></td>
                                <td><img src="images/<?php echo $homeClub->emblem ?>" alt="Home Team's Logo" width="50"></td>
                                <td><?php echo $match["matchTime"] ?></td>
                                <td><img src="images/<?php echo $awayClub->emblem ?>" alt="Away Team's Logo" width="50"></td>
                                <td><?php echo $awayClub->teamName ?></td>
                            </tr>
                            <tr>
                                <td colspan="5"><?php echo $venue->venueName ?></td>
                            </tr>
                        </table>
                    </a>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php endif; ?>
    <?php else: ?>
        <p>There are no matches left to display for the chosen team.</p>
    <?php endif; ?>
    <?php $dbConn->close(); ?>
</body>
</html>
