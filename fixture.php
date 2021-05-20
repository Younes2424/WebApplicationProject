<!DOCTYPE html>
<html lang="en">
<head>
    <!--
        Younes Azamiyan
        20017620
        Wednesday 9-11 Practical
    -->
    <meta charset="utf-8">
    <title>Week 10 Exercise 3</title>
    <link rel="stylesheet" href=" ../css/week10Styles.css">
</head>
<body>
    <?php
        require_once("conn.php");
        // Select the name, quantity, and price of each product
        // that has more than 10 in stock in ascending order of
        // the quantity in stock
        $sql = "SELECT homeTeam AS id, teamName, matchDate, (score1 - score2) AS difference FROM fixtures, teams
                WHERE (score1 IS NOT NULL) AND (score2 IS NOT NULL) AND (homeTeam = teamID)
                UNION ALL
                SELECT awayTeam AS id, teamName, matchDate, (score2 - score1) AS difference FROM fixtures, teams
                WHERE (score1 IS NOT NULL) AND (score2 IS NOT NULL) AND (awayTeam = teamID)
                ORDER BY id, matchDate DESC";
        // Save the result of the SQL query or terminate database
        // connection with an appropriate message
        $results = $dbConn->query($sql)
        or die ('Problem with query: ' . $dbConn->error);
    ?>
    <h1>Last Five Matches</h1>
    <table>
        <tr>
            <th>id</th>
            <th>teamName</th>
            <th>matchDate</th>
            <th>----------</th>
            <th>difference</th>
        </tr>
        <?php
            // Provide the associated data for each cell while the
            // query has data
            while ($row = $results->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row["id"]?></td>
                <td><?php echo $row["teamName"]?></td>
                <td><?php echo $row["matchDate"]?></td>
                <td>----------</td>
                <td><?php echo $row["difference"]?></td>
            </tr>
        <?php }
            $dbConn->close(); ?>
    </table>
</body>
</html>
