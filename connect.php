<?php
    function main() {
        echo '<h1>Connect Four</h1>';

        if (isset($_POST['name'])) {
            $name = $_POST['name'];
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
                <form class="name" action="connect.php" method="post">
                    <label for="name">Enter your name:</label>
                    <input type="text" name="name" id="name" required>
                    <input type="submit" value="Submit">
                </form>
            ';
        }
    }

    function bottom_most_vacant_cell($board_values) {
        $bmvc = [];
        for($i = 0; $i < 7; $i++) {
            $bmvc[$i] = -1;
            for($j = 4; $j >= 0; $j--) {
                if ($board_values[$j][$i] == "") {
                    $bmvc[$i] = $j;
                    break;
                }
            }
        }
        return $bmvc;
    }

    function generate_board($board_values) {

        $winner = check_win($board_values);
        $bmvc = bottom_most_vacant_cell($board_values);

        echo '<table>';
        //Generate the 'drop' buttons on top of each column
        echo '<tr>';
        for($i = 0; $i < 7; $i++) {
            echo '<td class="drop-button-cell">';
            if ($winner == null && $bmvc[$i] != -1) {

                //Get the board state if the drop button is clicked
                $board_if_clicked = $board_values;
                $board_if_clicked[$bmvc[$i]][$i] = "X";
                $bmvc_if_clicked = $bmvc;
                $bmvc_if_clicked[$i]--;

                //If the play has played a winning move, we don't need to calculate a response
                if (get_score($board_if_clicked, 'X') != INF) {

                    //Get the best possible response
                    $best_move = find_best_move($board_if_clicked, $bmvc_if_clicked);
                    if(!isset($best_move)) {
                        echo 'Error: Best move not found';
                    }
                    else if (!isset($bmvc_if_clicked[$best_move])) {
                        echo 'Error: Best move column not found';
                    }
                    $board_if_clicked[$bmvc_if_clicked[$best_move]][$best_move] = "O";

                }

                $board_str = "";
                foreach($board_if_clicked as $row) {
                    $board_str .= implode(" ", $row) . ".";
                }
                echo '
                    <form action="connect.php" method="post">
                        <input type="hidden" name="name" id="name" value="' . $_POST['name'] . '">
                        <input type="hidden" name="board" id="board" value="' . $board_str . '">
                        <button type="submit">Drop</button>
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

        if($winner != null) {
            if($winner == "X") {
                echo "<h2>You won!</h2>";
            }
            else if($winner == "O") {
                echo "<h2>I won!</h2>";
            }
            else {
                echo "<h2>Draw!</h2>";
            }
            echo '
                <form class="play_again" action="connect.php" method="post">
                    <input type="hidden" name="name" id="name" value="' . $_POST['name'] . '">
                    <input type="hidden" name="board" id="board" value=" . . . . ">
                    <button type="submit">Play Again</button>
                </form>
            ';
        }
    }

    function get_score($board_values, $player) {
        $score = 0;
        /*  
            dp will be a 7 length array where dp[i] consists of three elements [LD, D, RD, R] where 
            LD is the length of the left diagonal, D is the length of the column, RD is the length of the right diagonal, and R is the length of the current row (including the current cell).
            Length is the number of consecutive cells in that direction that are occupied by the current player.
            If Length == 4, then we know the player has won.
        */

        //Keeps track of the topmost dp cells of each column. We will calculate score by summing up these values.
        $score_cache = array_fill(0, 7, 0);
        $dp = [];
        for($i = 4; $i >= 0; $i--) {
            $next_dp = [];
            for($j = 0; $j < 7; $j++) {
                $current_cell = $board_values[$i][$j];
                if($current_cell != $player) {
                    $next_dp[$j] = [0, 0, 0, 0];
                    continue;
                }
                $left = 1;
                $down = 1;
                $right = 1;
                $row = 1;
                if(isset($dp[$j - 1])) {
                    $left += $dp[$j - 1][0];
                    if($left == 4) {
                        return INF;
                    }
                }
                if(isset($dp[$j])) {
                    $down += $dp[$j][1];
                    if($down == 4) {
                        return INF;
                    }
                }
                if(isset($dp[$j + 1])) {
                    $right += $dp[$j + 1][2];
                    if($right == 4) {
                        return INF;
                    }
                }
                if(isset($next_dp[$j - 1])) {
                    $row += $next_dp[$j - 1][3];
                    if($row == 4) {
                        return INF;
                    }
                }
                $next_dp[$j] = [$left, $down, $right, $row];
                $score_cache[$j] += $left + $down + $right + $row;
            }
            $dp = $next_dp;
        }
        foreach($score_cache as $cell_score) {
            $score += $cell_score;
        }
        return $score;
    }

    function check_win($board_values) {

        $X_score = get_score($board_values, 'X');
        $O_score = get_score($board_values, 'O');
        
        if ($X_score == INF) {
            return 'X';
        }
        else if ($O_score == INF) {
            return 'O';
        }

        //Check if the top row of the board is full
        $is_full = true;
        for($j = 0; $j < 7; $j++) {
            if ($board_values[0][$j] == "") {
                $is_full = false;
                break;
            }
        }
        if($is_full) {
            return "Tie";
        }

        return null;

    }

    function find_best_move($board_values, $bmvc) {
        $best_move = null;
        $best_score = -INF;

        for($i = 0; $i < 7; $i++) {
            if($bmvc[$i] == -1) {
                continue;
            }
            if ($board_values[$bmvc[$i]][$i] == "") {
                $board_values[$bmvc[$i]][$i] = 'O';
                $score = minimax($board_values, 3, false);
                $board_values[$bmvc[$i]][$i] = "";

                if ($score > $best_score || $best_move == null) {
                    $best_score = $score;
                    $best_move = $i;
                }
            }
        }
        return $best_move;
    }

    function minimax($board_values, $depth, $is_maximizing) {

        $winner = check_win($board_values);
        $bmvc = bottom_most_vacant_cell($board_values);

        if ($winner == 'X') {
            return -INF;
        }
        else if ($winner == 'O') {
            return INF;
        }
        else if ($winner == 'Tie') {
            return 0;
        }
        
        if($depth == 0) {
            return get_score($board_values, 'O') - get_score($board_values, 'X');
        }

        //Playing as O
        if ($is_maximizing) {
            $best_score = -INF;

            for($i = 0; $i < 7; $i++) {
                if($bmvc[$i] == -1) {
                    continue;
                }
                if ($board_values[$bmvc[$i]][$i] == "") {
                    $board_values[$bmvc[$i]][$i] = 'O';
                    $score = minimax($board_values, $depth - 1, false);
                    $board_values[$bmvc[$i]][$i] = "";
                    $best_score = max($score, $best_score);
                }
            }

            return $best_score;
        }
        //Playing as X
        else {
            $best_score = INF;

            for($i = 0; $i < 7; $i++) {
                if($bmvc[$i] == -1) {
                    continue;
                }
                if ($board_values[$bmvc[$i]][$i] == "") {
                    $board_values[$bmvc[$i]][$i] = 'X';
                    $score = minimax($board_values, $depth - 1, true);
                    $board_values[$bmvc[$i]][$i] = "";
                    $best_score = min($score, $best_score);
                }
            }

            return $best_score;
        }
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
