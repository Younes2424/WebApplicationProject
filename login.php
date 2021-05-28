<?php
   // ensure page is not cached
   header("Cache-Control: no-cache");

   // expire the page as soon as it is loaded
   header("Expires: -1");

   $errorMessage = '';
   // Connect to the database
   require_once('dbConn.php');

   // check that the form has been submitted
   if(isset($_POST['submit'])) {

     // check that username and password were entered
     if(empty($_POST['username']) || empty($_POST['password'])) {
        $errorMessage = "Both username and password are required!";
     }
     else {
        // parse username and password for special characters
        $username = $dbConn->escape_string($_POST['username']);
        $password = $dbConn->escape_string($_POST['password']);

        // hash the password so it can be compared with the db value
        $hashedPassword = hash('sha256', $password);

        // query the db
        $sql = "SELECT id, email, firstname, surname FROM leagueadmin WHERE (email = '$username') AND (password = '$hashedPassword')";
        $result = $dbConn->query($sql)
        or die ('Problem with query: ' . $dbConn->error);

        // check number of rows in record set. since each user is unique, only one record will be retrieved if the credentials are correct.
        if($result->num_rows) {
            // start a new session for the user
            session_start();

            // Store the user details in session variables
            $user = $result->fetch_assoc();
            $_SESSION["who"] = $user["id"];
            $_SESSION["firstname"] = $user["firstname"];
            // Redirect the user to the secure page
            header("location: scoreEntry.php");
        }
        else {
            $errorMessage = "Invalid Username or Password!";
        }
     }
   }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Login Form</title>
    <link rel="stylesheet" href="css/projectMaster.css">
    <link rel="stylesheet" href="css/loginStyles.css">
  </head>

  <body>
    <header>
      <nav>
        <div class="nav_links">
          <a href='index.php'>Home</a>
          <a href='ladder.php'>Ladder</a>
          <a href='fixture.php'>Fixtures</a>
        </div>
      </nav>
    </header>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
       <p><?php echo $errorMessage;?></p>
       <div class="input-box">
         <label for="username">Username:</label>
         <input type="text" name="username" maxlength="50" id="username">
       </div>
       <div class="input-box">
         <label for="password">Password:</label>
         <input type="password" name="password" maxlength="20" id="password">
       </div>
       <div class="input-box">
         <input type="submit" value="Login" name="submit">
       </div>
    </form>
    <?php $dbConn->close(); ?>
  </body>
</html>
