<?php
    session_start();
    header("Cache-Control: no-cache");
    header("Expires: -1");
    require_once("conn.php");
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $sql = "SELECT * FROM fixtures WHERE matchID = ?";
    $statement = $dbConn->prepare($sql);
    $statement->bind_param("i", $_GET["matchID"]);
    $statement->execute();
    $selectedMatch = $statement->get_result();

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
    <title><?php echo "A-League Score Entry | " . date("M d") ?></title>
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

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <?php while ($match = $selectedMatch->fetch_assoc()): ?>
            <?php
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
                <table class="fixture_box fulltime">
                    <tr>
                        <td><?php echo $match["weekID"]; ?></td>
                        <td></td>
                        <td></td>
                        <td>Full-time</td>
                        <td></td>
                        <td></td>
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
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo $venue->venueName ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            <?php else: ?>
                <table class="fixture_box scheduled">
                    <tr>
                        <td><?php echo $match["weekID"]; ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo $match["matchDate"]; ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $homeClub->teamName ?></td>
                        <td><img src="images/<?php echo $homeClub->emblem ?>" alt="Home Team's Logo" width="50"></td>
                        <td>
                            <label for="<?php echo $match['homeTeam'] ?>"></label>
                            <input type="text" name="<?php echo $match['homeTeam'] ?>" id="<?php echo $match['homeTeam'] ?>">
                        </td>
                        <td><?php echo $match["matchTime"] ?></td>
                        <td>
                            <label for="<?php echo $match['awayTeam'] ?>"></label>
                            <input type="text" name="<?php echo $match['awayTeam'] ?>" id="<?php echo $match['awayTeam'] ?>">
                        </td>
                        <td><img src="images/<?php echo $awayClub->emblem ?>" alt="Away Team's Logo" width="50"></td>
                        <td><?php echo $awayClub->teamName ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?php echo $venue->venueName ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            <?php endif; ?>
        <?php endwhile; ?>
        <input type="submit" name="submit" value="submit">
    </form>
</body>
</html>
