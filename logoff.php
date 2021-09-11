<?php
   // tell the browser not to cache the page
   header("Cache-Control: no-cache");

   // expire the page as soon as it is loaded
   header("Expires: -1");

   // get access to the session variables
   session_start();

   // Unset all of the session variables.
   $_SESSION = array();

   // Now destroy the session
   session_destroy();

   // Redirect the user to the starting page (index.php)
   header("location: index.php");
?>
