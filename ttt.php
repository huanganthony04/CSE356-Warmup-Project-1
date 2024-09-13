<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Submit Your Name</h1>
        <form action="ttt.php" method="GET">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <input type="submit" value="Submit">
        </form>

        <?php
        if (isset($_GET['name'])) {
            $name = htmlspecialchars($_GET['name']);
            echo "<p>Hello, " . $name . "!</p>";
        }
        ?>
    </div>
</body>
</html>
