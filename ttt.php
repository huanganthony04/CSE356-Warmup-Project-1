<?php

    function main() {

        echo '<h1>Tic-Tac-Toe</h1>';

        if (isset($_GET['name'])) {
            $name = htmlspecialchars($_GET['name']);
            $date = date("m-d-Y");
            echo "<h1>Hello $name, $date</h1>";

            //Should have 8 spaces separating whatever is in the board
            $board = "        ";
            if (isset($_GET['board'])) {
                $board = htmlspecialchars($_GET['board']);
            }
            $board_values = explode(" ", $board);
            generate_board($board_values);
        }
        else {
            echo '
                <form class="name" action="ttt.php" method="get">
                    <label for="name">Enter your name:</label>
                    <input type="text" name="name" id="name" required>
                    <input type="submit" value="Submit">
                </form>
            ';
        }
    }

    function generate_board($board_values) {

        $winner = check_win($board_values);

        echo '<table>';
        for($i = 0; $i < 3; $i++) {
            echo '<tr>';
            for($j = 0; $j < 3; $j++) {
                $cell_value = $board_values[$i * 3 + $j];
                echo '<td>';
                if ($cell_value == "" && $winner == null) {
                    $board_if_clicked = $board_values;
                    $board_if_clicked[$i * 3 + $j] = 'X';
                    $board_if_clicked[find_best_move($board_if_clicked)] = 'O';
                    echo '<a href="ttt.php?name=' . $_GET['name'] . '&board=' . urlencode(implode(" ", $board_if_clicked)) . '"> </a>';
                }
                else {
                    echo $cell_value;
                }
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';

        if ($winner != null) {
            if ($winner == 'X') {
                echo "<h2>You won!</h2>";
            }
            else if ($winner == 'O') {
                echo "<h2>I won!</h2>";
            }
            else if ($winner == 'Tie') {
                echo "<h2>WINNER: NONE. A STRANGE GAME. THE ONLY WINNING MOVE IS NOT TO PLAY.</h2>";
            }

            if($winner != 'Tie') {
                echo '
                    <form class="play_again" action="ttt.php" method="get">
                        <input type="hidden" name="name" value="' . $_GET['name'] . '">
                        <input type="submit" value="Play Again">
                    </form>
                ';
            }
        }

    }

    function find_best_move($board_values) {
        $best_move = null;
        $best_score = -INF;

        for($i = 0; $i < 9; $i++) {
            if ($board_values[$i] == "") {
                $board_values[$i] = 'O';
                $score = minimax($board_values, 0, false);
                $board_values[$i] = "";

                if ($score > $best_score) {
                    $best_score = $score;
                    $best_move = $i;
                }
            }
        }

        return $best_move;
    }

    function minimax($board_values, $depth, $is_maximizing) {
        $winner = check_win($board_values);

        if ($winner == 'X') {
            return -1;
        }
        else if ($winner == 'O') {
            return 1;
        }
        else if ($winner == 'Tie') {
            return 0;
        }

        if ($is_maximizing) {
            $best_score = -INF;

            for($i = 0; $i < 9; $i++) {
                if ($board_values[$i] == "") {
                    $board_values[$i] = 'O';
                    $score = minimax($board_values, $depth + 1, false);
                    $board_values[$i] = "";
                    $best_score = max($score, $best_score);
                }
            }

            return $best_score;
        }
        else {
            $best_score = INF;

            for($i = 0; $i < 9; $i++) {
                if ($board_values[$i] == "") {
                    $board_values[$i] = 'X';
                    $score = minimax($board_values, $depth + 1, true);
                    $board_values[$i] = "";
                    $best_score = min($score, $best_score);
                }
            }

            return $best_score;
        }
    }

    function check_win($board_values) {
        $winning_combinations = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8],
            [0, 3, 6], [1, 4, 7], [2, 5, 8],
            [0, 4, 8], [2, 4, 6]
        ];

        foreach($winning_combinations as $combination) {
            $a = $combination[0];
            $b = $combination[1];
            $c = $combination[2];

            if ($board_values[$a] == $board_values[$b] && $board_values[$b] == $board_values[$c]) {
                return $board_values[$a];
            }
        }

        //Make sure there are still moves available
        foreach($board_values as $cell) {
            if ($cell == "") {
                return null;
            }
        }

        return "Tie";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tic-Tac-Toe</title>
    <link rel="stylesheet" href="ttt.css">
</head>
<body>
    <?php 
        main();
    ?>
</body>
</html>
