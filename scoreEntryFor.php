<?php
    session_start();
    if (empty($_SESSION["who"])) {
        header("location: login.php"); // add the condition to redirect
    }
    header("Cache-Control: no-cache");
    header("Expires: -1");
    require_once("dbConn.php");
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if (isset($_GET["matchID"])) {
        $_SESSION["matchID"] = sanitiseInput($_GET["matchID"]);
        $matchID = $_SESSION["matchID"];
    }
    else {
        $matchID = $_SESSION["matchID"];
    }

    $sql = "SELECT matchID, weekID, homeTeam, awayTeam, venueID, ";
    $sql = $sql . "DATE_FORMAT(matchDate, '%d %M %Y') 'matchDate', DATE_FORMAT(matchTime, '%H:%i') AS 'matchTime', ";
    $sql = $sql . "score1, score2 FROM fixtures WHERE matchID = ?";
    $statement = $dbConn->prepare($sql);
    $statement->bind_param("i", $matchID);
    $statement->execute();
    $selectedMatch = $statement->get_result();

    function sanitiseInput($data) {
        // function from https://www.w3schools.com/php/php_form_validation.asp
        $data = trim($data); // remove spaces, tabs, etc
        $data = stripslashes($data); // remove backslash
        $data = htmlspecialchars($data); // converts special characters to their html code
        return $data;
    }

    function validateScore($score) {
        if (($score < 0) || (intval($score))) {
            echo "Score must be a numeric natural number!";
        }

        if (empty($score)) {
            echo "All fields are mandatory!";
        }

    }

    function isScoreValid($score) {
        $valid = true;
        if (($score < 0) || (!intval($score))) {
            $valid = false;
        }

        if (empty($score)) {
            $valid = false;
        }

        return $valid;
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
    <link rel="stylesheet" href="css/scoreEntryForStyles.css">
    <script defer src="javascript/scoreValidation.js"></script>
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
      <h1>2021 A-League Score Entry</h1>
    </header>

    <?php while ($match = $selectedMatch->fetch_assoc()): ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" onsubmit="return validateForm(<?php echo strval($match["homeTeam"]); ?>, <?php echo strval($match["awayTeam"]); ?>)">
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
                        <td colspan="5">Full-time</td>
                        <td><?php echo $match["matchDate"]; ?></td>
                    </tr>
                    <tr>
                        <td colspan="7">
                            <span id="error0"><?php if (isset($_POST[$match["homeTeam"]])) {validateScore($_POST[$match["homeTeam"]]);} ?></span>
                            <span id="error1"><?php if (isset($_POST[$match["awayTeam"]])) {validateScore($_POST[$match["awayTeam"]]);} ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $homeClub->teamName ?></td>
                        <td><img src="images/<?php echo $homeClub->emblem; ?>" alt="Home Team's Logo" width="50"></td>
                        <td>
                            <label for="<?php echo $match['homeTeam'] ?>">Score</label>
                            <input type="text" name="<?php echo $match['homeTeam']; ?>" id="<?php echo $match['homeTeam']; ?>" value="<?php
                                if(isset($match['score1'])) {
                                    echo $match['score1'];
                                }
                                elseif (isset($_POST[$match['homeTeam']])) {
                                    echo $_POST[$match['homeTeam']];
                                }
                            ?>" size="2" onblur="validateScore(document.getElementById(<?php echo $match["homeTeam"] ?>), document.getElementById('error0'));">

                        </td>
                        <td>-</td>
                        <td>
                            <label for="<?php echo $match['awayTeam'] ?>">Score</label>
                            <input type="text" name="<?php echo $match['awayTeam']; ?>" id="<?php echo $match['awayTeam']; ?>" value="<?php
                                if(isset($match['score2'])) {
                                    echo $match['score2'];
                                }
                                elseif (isset($_POST[$match['awayTeam']])) {
                                    echo $_POST[$match['awayTeam']];
                                }
                            ?>" size="2" onblur="validateScore(document.getElementById(<?php echo $match["awayTeam"] ?>), document.getElementById('error1'));">

                        </td>
                        <td><img src="images/<?php echo $awayClub->emblem; ?>" alt="Away Team's Logo" width="50"></td>
                        <td><?php echo $awayClub->teamName ?></td>
                    </tr>
                    <tr>
                        <td colspan="7"><?php echo $venue->venueName ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <table class="fixture_box scheduled">
                    <tr>
                        <td><?php echo $match["weekID"]; ?></td>
                        <td colspan="5"></td>
                        <td><?php echo $match["matchDate"]; ?></td>
                    </tr>
                    <tr>
                        <td colspan="7">
                            <span id="error0"><?php if (isset($_POST[$match["homeTeam"]])) {validateScore($_POST[$match["homeTeam"]]);} ?></span>
                            <span id="error1"><?php if (isset($_POST[$match["awayTeam"]])) {validateScore($_POST[$match["awayTeam"]]);} ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo $homeClub->teamName ?></td>
                        <td><img src="images/<?php echo $homeClub->emblem; ?>" alt="Home Team's Logo" width="50"></td>
                        <td>
                            <label for="<?php echo $match['homeTeam'] ?>">Score</label>
                            <input type="text" name="<?php echo $match['homeTeam']; ?>" id="<?php echo $match['homeTeam']; ?>" value="<?php
                                if(isset($match['score1'])) {
                                    echo $match['score1'];
                                }
                                elseif (isset($_POST[$match['homeTeam']])) {
                                    echo $_POST[$match['homeTeam']];
                                }
                            ?>" size="2" onblur="validateScore(document.getElementById(<?php echo $match["homeTeam"] ?>), document.getElementById('error0'));">
                        </td>
                        <td><?php echo $match["matchTime"] ?></td>
                        <td>
                            <label for="<?php echo $match['awayTeam'] ?>">Score</label>
                            <input type="text" name="<?php echo $match['awayTeam']; ?>" id="<?php echo $match['awayTeam']; ?>" value="<?php
                                if(isset($match['score2'])) {
                                    echo $match['score2'];
                                }
                                elseif (isset($_POST[$match['awayTeam']])) {
                                    echo $_POST[$match['awayTeam']];
                                }
                            ?>" size="2" onblur="validateScore(document.getElementById(<?php echo $match["awayTeam"] ?>), document.getElementById('error1'));">
                        </td>
                        <td><img src="images/<?php echo $awayClub->emblem; ?>" alt="Away Team's Logo" width="50"></td>
                        <td><?php echo $awayClub->teamName ?></td>
                    </tr>
                    <tr>
                        <td colspan="7"><?php echo $venue->venueName ?></td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php
                if (isset($_POST[$match["homeTeam"]]) && isset($_POST[$match["awayTeam"]])) {
                    if (isScoreValid($_POST[$match["homeTeam"]]) && isScoreValid($_POST[$match["awayTeam"]])) {
                        $homeScore = sanitiseInput($_POST[$match["homeTeam"]]);
                        $awayScore = sanitiseInput($_POST[$match["awayTeam"]]);

                        $updateQuery = "UPDATE fixtures ";
                        $updateQuery = $updateQuery . "SET score1 = ?, score2 = ? ";
                        $updateQuery = $updateQuery . "WHERE matchID = ?";

                        if ($updateFixture = $dbConn->prepare($updateQuery)) {
                            $updateFixture->bind_param("iii", $homeScore, $awayScore, $match["matchID"]);
                            $updateFixture->execute();
                        }
                        else {
                            $error = $dbConn->errno . ' ' . $dbConn->error;
                            echo $error;
                        }

                        if ($homeScore > $awayScore) {
                            $updateQuery = "UPDATE teams ";
                            $updateQuery = $updateQuery . "SET played = (played + 1), won = (won + 1), goalsFor = (goalsFor + ?), goalsAgainst = (goalsAgainst + ?), goalDiff = (goalsFor - goalsAgainst), points = (points + 3) ";
                            $updateQuery = $updateQuery . "WHERE teamID = ?";

                            if ($updateHomeTeam = $dbConn->prepare($updateQuery)) {
                            $updateHomeTeam->bind_param("iii", $homeScore, $awayScore, $match["homeTeam"]);
                            $updateHomeTeam->execute();
                            }
                            else {
                                $error = $dbConn->errno . ' ' . $dbConn->error;
                                echo $error;
                            }

                            $updateQuery = "UPDATE teams ";
                            $updateQuery = $updateQuery . "SET played = (played + 1), lost = (lost + 1), goalsFor = (goalsFor + ?), goalsAgainst = (goalsAgainst + ?), goalDiff = (goalsFor - goalsAgainst) ";
                            $updateQuery = $updateQuery . "WHERE teamID = ?";

                            if ($updateAwayTeam = $dbConn->prepare($updateQuery)) {
                            $updateAwayTeam->bind_param("iii", $homeScore, $awayScore, $match["awayTeam"]);
                            $updateAwayTeam->execute();
                            }
                            else {
                                $error = $dbConn->errno . ' ' . $dbConn->error;
                                echo $error;
                            }
                        }
                        elseif ($homeScore < $awayScore) {
                            $updateQuery = "UPDATE teams ";
                            $updateQuery = $updateQuery . "SET played = (played + 1), won = (won + 1), goalsFor = (goalsFor + ?), goalsAgainst = (goalsAgainst + ?), goalDiff = (goalsFor - goalsAgainst), points = (points + 3) ";
                            $updateQuery = $updateQuery . "WHERE teamID = ?";

                            if ($updateHomeTeam = $dbConn->prepare($updateQuery)) {
                            $updateHomeTeam->bind_param("iii", $homeScore, $awayScore, $match["awayTeam"]);
                            $updateHomeTeam->execute();
                            }
                            else {
                                $error = $dbConn->errno . ' ' . $dbConn->error;
                                echo $error;
                            }

                            $updateQuery = "UPDATE teams ";
                            $updateQuery = $updateQuery . "SET played = (played + 1), lost = (lost + 1), goalsFor = (goalsFor + ?), goalsAgainst = (goalsAgainst + ?), goalDiff = (goalsFor - goalsAgainst) ";
                            $updateQuery = $updateQuery . "WHERE teamID = ?";

                            if ($updateAwayTeam = $dbConn->prepare($updateQuery)) {
                            $updateAwayTeam->bind_param("iii", $homeScore, $awayScore, $match["homeTeam"]);
                            $updateAwayTeam->execute();
                            }
                            else {
                                $error = $dbConn->errno . ' ' . $dbConn->error;
                                echo $error;
                            }
                        }
                        else {
                            $updateQuery = "UPDATE teams ";
                            $updateQuery = $updateQuery . "SET played = (played + 1), drawn = (drawn + 1), goalsFor = (goalsFor + ?), goalsAgainst = (goalsAgainst + ?) , goalDiff = (goalsFor - goalsAgainst), points = (points + 1) ";
                            $updateQuery = $updateQuery . "WHERE teamID = ?";

                            if ($updateHomeTeam = $dbConn->prepare($updateQuery)) {
                            $updateHomeTeam->bind_param("iii", $homeScore, $awayScore, $match["homeTeam"]);
                            $updateHomeTeam->execute();
                            }
                            else {
                                $error = $dbConn->errno . ' ' . $dbConn->error;
                                echo $error;
                            }

                            if ($updateAwayTeam = $dbConn->prepare($updateQuery)) {
                            $updateAwayTeam->bind_param("iii", $homeScore, $awayScore, $match["awayTeam"]);
                            $updateAwayTeam->execute();
                            }
                            else {
                                $error = $dbConn->errno . ' ' . $dbConn->error;
                                echo $error;
                            }
                        }
                    }
                }
            ?>
        <?php endwhile; ?>
        <input type="submit" name="submit" value="submit">
    </form>
    <?php $dbConn->close(); ?>
</body>
</html>
