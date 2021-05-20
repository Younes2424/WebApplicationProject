<?php

  //start session here
  session_start();
  header("Cache-Control: no-cache");
  header("Expires: -1");
  require_once("conn.php");

  if ((isset($_POST["submit"])) && (!empty($_POST["choice"]))) {
    $choice = $_POST["choice"];
    $_SESSION["weekType"] = $choice;

    //set the week number based upon the users choice
    if ($choice == "server") {
      $today = strval(date("Y-m-d"));   //get the server date in the correct format
      $_SESSION["today"] = $today;
      //query the db here for the week number based on the server date
      $statement = $dbConn->prepare("SELECT weekID FROM weeks WHERE (startDate <= ?) AND (endDate >= ?)");
      $statement->bind_param("ss", $today, $today);
      $statement->execute();
      $result = $statement->get_result();
      $week = $result->fetch_object();
      $week = $week->weekID;
    }
    else {
      if (is_numeric($_POST["weekNum"])) {
        $week = $dbConn->escape_string($_POST["weekNum"]);
      }
    }

    //set up a session variable here to identify the week
    $_SESSION["weekNo"] = $week;
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
    <meta charset="utf-8">
    <?php
      if (!empty($_SESSION["weekNo"])) {
        echo "<title>A-League Assignment - Week " . $_SESSION["weekNo"] . "</title>";
      }
      else {
        echo "<title>A-League Assignment - Choose Week</title>";
      }
    ?>
    <link rel="stylesheet" href="css/projectMaster.css">

    <script>
      function changeSelectionList(){
      if (document.getElementById("weekForm").choice.value == "server")
        document.getElementById("weekNum").disabled = true;
      else
        document.getElementById("weekNum").disabled = false;
      }
    </script>

  </head>

  <body>

    <header>
      <nav>
        <?php
          if (!empty($_SESSION["weekNo"])) {
            echo "
                  <a href='index.php'>Home</a>
                  <a href='ladder.php'>Ladder</a>
                  <a href='fixture.php'>Fixtures</a>
                  <a href='scoreEntry.php'>Score Entry</a>
                  <a href='login.php'>Login</a>
                  <a href='logoff.php'>Logoff</a>
            ";
          }
        ?>
      </nav>
    </header>

    <h1>A-League Ladder Assignment</h1>

    <?php
      if ((!empty($_SESSION["weekNo"])) && (!empty($_SESSION["today"]))) {
        if ((!empty($_SESSION["today"])) && ($_SESSION["weekType"] == "server")) {
          echo "<p><strong>Date: </strong>" . $_SESSION["today"] . "</p>";
          echo "<p><strong>Week: </strong>" . $_SESSION["weekNo"] . "</p>";
        }
        else {
          echo "<p><strong>Week: </strong>" . $_SESSION["weekNo"] . "</p>";
        }
      }
     ?>

    <form id="weekForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <p>Do you want to use the Server Date or User Input for the current week?</p>

      <p>
        <input type="radio" id="Server" name="choice" value="server" onclick="changeSelectionList();">
        <label for="Server">Server Date</label>
      </p>

      <p>
        <input type="radio" id="User" name="choice" value="user" onclick="changeSelectionList();">
        <label for="User">User Input</label>
      </p>

      <p>
        <label for="weekNum">Week Number:</label>
        <select id="weekNum" name="weekNum" size="1" disabled>
          <script>
             for (i = 1; i <= 24; i++)
               document.write('<option value="' + i + '">' + i + '</option>');
          </script>
        </select>
      </p>
      <p><input type="submit" name="submit" value="submit"></p>
    </form>

  </body>
</html>
