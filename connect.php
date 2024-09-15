<?php
    function main() {
        echo '<h1>Connect Four</h1>';

        if (isset($_GET['name'])) {
            $name = $_GET['name'];
            $date = date("m-d-Y");
            echo "<h1>Hello $name, $date</h1>";
            if(isset($_POST['board'])) {
                $board = $_POST['board'];
            }
            else {
                //Each of the 5 rows is separated by a dot. Every cell of each row is separated by a space.
                $board = " . . . . ";
            }
            $board_rows = explode(".", $board);
            $board_values = [];
            for($i = 0; $i < 5; $i++) {
                $board_row = explode(" ", $board_rows[$i]);
                for($j = 0; $j < 7; $j++) {
                    if(isset($board_row[$j])) {
                        $board_values[$i][$j] = $board_row[$j];
                    }
                    else {
                        $board_values[$i][$j] = "";
                    }
                }
            }
            generate_board($board_values);
        }
        else {
            echo '
                <form class="name" action="connect.php" method="get">
                    <label for="name">Enter your name:</label>
                    <input type="text" name="name" id="name" required>
                    <input type="submit" value="Submit">
                </form>
            ';
        }
    }

    function generate_board($board_values) {

        echo '<table>';

        //Generate the 'drop' buttons on top of each column
        echo '<tr>';
        for($i = 0; $i < 7; $i++) {
            echo '<td class="drop-button-cell">';
            if (!isset($board_values[0][$i]) || $board_values[0][$i] == "") {
                $board_if_clicked = $board_values;
                //Get the bottom-most empty cell in the column
                $bottom_most_cell = 4;
                for($j = 4; $j >= 0; $j--) {
                    if ($board_values[$j][$i] == "") {
                        $bottom_most_cell = $j;
                        break;
                    }
                }
                $board_if_clicked[$bottom_most_cell][$i] = "X";
                $board_str = "";
                foreach($board_if_clicked as $row) {
                    $board_str .= implode(" ", $row) . ".";
                }
                echo '
                    <form action="connect.php?name=' . $_GET['name'] . '" method="post">
                        <input type="hidden" name="board" id="board" value="' . $board_str . '">
                        <input class="drop" type="submit" value="Drop">
                    </form>
                ';
            }
            else {
                echo '
                    <form class="drop">
                        <input class="drop" type="submit" value="Drop" disabled>
                    </form>
                ';
            }
            echo '</td>';
        }
        echo '</tr>';

        //Generate the board
        for($i = 0; $i < 5; $i++) {
            echo '<tr>';
            for($j = 0; $j < 7; $j++) {
                $cell_value = isset($board_values[$i][$j]) ? $board_values[$i][$j] : "";
                echo "<td>$cell_value</td>";
            }
            echo '</tr>';
        }
        echo '</table>';
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connect Four</title>
        <link rel="stylesheet" href="connect.css">
    </head>
    <body>
        <?php 
            main();
        ?>
    </body>
</html>
