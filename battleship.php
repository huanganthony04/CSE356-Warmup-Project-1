<?php

    $ROWS = 5;
    $COLS = 7;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    function main() {

        echo '<h1>Battleship!</h1>';

        if (isset($_POST['name'])) {
            $name = htmlspecialchars($_POST['name']);
            $date = date("m-d-Y");
            echo "<h1>Hello $name, $date</h1>";

            //If the game has not been initialized, or the game has been reset, create a new board
            if (!isset($_SESSION['board']) || isset($_POST['replay'])) {
                $_SESSION['board'] = create_new_board();
                $_SESSION['turns_left'] = ceil($GLOBALS['ROWS'] * $GLOBALS['COLS'] * 0.60);
            }

            //If a move has been provided, update the board with the move
            if(isset($_POST['move'])){
                $old_board = $_SESSION['board'];
                $move = explode(",", urldecode($_POST['move']));
                $x = $move[0];
                $y = $move[1];

                if($old_board[$x][$y] == 's'){
                    $old_board[$x][$y] = 'x';
                }else{
                    $old_board[$x][$y] = 'o';
                }
                $_SESSION['turns_left']--;
                $_SESSION['board'] = $old_board;
            }

            $board = $_SESSION['board'];
            $turns_left = $_SESSION['turns_left'];

            //Render the game
            render_battleship_board($board, $turns_left);

        }
        else {
            echo '
                <form class="name" action="battleship.php" method="post">
                        <label for="name">Enter your name:</label>
                        <input type="text" name="name" id="name" required>
                        <input type="submit" value="Submit" id="submit">
                </form>
            ';

        }

    
    }

    function create_new_board() {
        //"" -> empty
        //"s" -> ship
        //"x" -> hit
        //"o" -> miss
        $new_board = array_fill(0, $GLOBALS['ROWS'], array_fill(0, $GLOBALS['COLS'], ""));
        $new_board = place_ship($new_board, 2);
        $new_board = place_ship($new_board, 3);
        $new_board = place_ship($new_board, 4);
        return $new_board;
    }

    function place_ship($board, $length) {
        //0 -> horizontal, 1 -> vertical
        $direction = rand(0, 1);
        if ($direction == 0) {
            //Find all possible places where this ship can go
            $possible_starts = [];
            $starts_counter = 0;
            for($i = 0; $i < $GLOBALS['ROWS']; $i++){
                for($j = 0; $j < $GLOBALS['COLS'] - $length; $j++){
                    //Check if the ship can be placed here without overlapping another ship
                    for($k = $j; $k < $j + $length; $k++){
                        if($board[$i][$k] != ""){
                            continue 2;
                        }
                    }
                    $possible_starts[$starts_counter] = [$i, $j];
                    $starts_counter++;
                }
            }
            $start = $possible_starts[rand(0, $starts_counter - 1)];
            for($k = $start[1]; $k < $start[1] + $length; $k++){
                $board[$start[0]][$k] = 's';
            }
        }
        else {
            //Find all possible places where this ship can go
            $possible_starts = [];
            $starts_counter = 0;
            for($i = 0; $i < $GLOBALS['ROWS'] - $length; $i++){
                for($j = 0; $j < $GLOBALS['COLS']; $j++){
                    //Check if the ship can be placed here without overlapping another ship
                    for($k = $i; $k < $i + $length; $k++){
                        if($board[$k][$j] != ""){
                            continue 2;
                        }
                    }
                    $possible_starts[$starts_counter] = [$i, $j];
                    $starts_counter++;
                }
            }
            $start = $possible_starts[rand(0, $starts_counter - 1)];
            for($k = $start[0]; $k < $start[0] + $length; $k++){
                $board[$k][$start[1]] = 's';
            }
        }

        return $board;
    }

    function render_battleship_board($board, $turns_left){

        $winner = check_victory();
        echo '<h1> Moves left: ' . $turns_left . ' </h1>';

        echo '<table>';
        for($i = 0; $i < $GLOBALS['ROWS']; $i++){
            echo '<tr>';
            for($j = 0; $j < $GLOBALS['COLS']; $j++ ){
                echo '<td>';

                if($board[$i][$j] == 'x'){
                    //If it is a hit, mark with X
                    echo 'X';
                }else if($board[$i][$j] == 'o'){
                    //If it is a miss, mark with O
                    echo 'O';
                }else if($winner == true || $turns_left == 0) {
                    if($board[$i][$j] == 's'){
                        echo 'S';
                    }
                    else {
                        echo '.';
                    }
                }
                else {
                    echo '
                        <form action="battleship.php" method="post">
                            <input type="hidden" name="name" id="name" value="' . $_POST['name'] . '">
                            <input type="hidden" name="move" id="move" value="' . urlencode("$i,$j") . '">
                            <button type="submit">?</button>
                        </form>
                    ';
                }
                echo '</td>';
            }
            echo '</tr>';
            
            
        }
        echo '</table>';

        if($winner == true){
            echo '<h2>You win!</h2>';
        }
        else if ($turns_left == 0){
            echo '<h2>You lose!</h2>';
        }
        if($winner == true || $turns_left == 0){
            echo '
                <form action="battleship.php" method="post">
                    <input type="hidden" name="name" id="name" value="' . $_POST['name'] . '">
                    <input type="hidden" name="replay" id="replay" value="yes">
                    <button type="submit">Play again</button>
                </form>
            ';
        }
        
    }

    function check_victory(){
        for($i = 0; $i < $GLOBALS['ROWS']; $i++){
            for($j = 0; $j < $GLOBALS['COLS']; $j++){
                if($_SESSION['board'][$i][$j] == 's'){
                    return false;
                }
            }
        }
        return true;
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Battleship!</title>
        <link rel="stylesheet" href="battleship.css">
    </head>
    <body>
        <?php 
            main();
        ?>
    </body>
</html>
