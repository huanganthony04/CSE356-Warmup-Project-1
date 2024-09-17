<?php


    enum game_state {
        case ongoing;
        case over ;
        case won;
    }

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
            

            //If replay is pressed, reset the game
            if(isset($_POST['replay'])){
                init_game();
            }
            //Check if move is made
            if(isset($_POST["move"])) {
                //Handle move
                handle_new_move();
            }
            
            check_game_state();

            session_write_close();
            
            //Render the game
            render_battleship_board();

            if($_SESSION['game_state'] !== game_state::ongoing){
                echo '<form action="battleship.php" method="post">
                        <input type="hidden" name="name" id="name" value="' . $_POST['name'] . '">
                        <input type="hidden" name="replay" id="name" value="yes">
                        <button type="submit">Play again</button>
                        </form>';
            }
        }
        else {

            init_game();
            
            echo '
                <form class="name" action="battleship.php" method="post">
                        <label for="name">Enter your name:</label>
                        <input type="text" name="name" id="name" required>
                        <input type="submit" value="Submit" id="submit">
                </form>
            ';

        }

    
    }

    function init_game() {
        //Initalize the game state and store it in the session
        //GAME INFO
        // . == empty
        // s == ship
        // x == hit
        // o == miss

        $_SESSION['board'] = [];
        $_SESSION['turns_left'] = ceil($GLOBALS['ROWS'] * $GLOBALS['COLS'] * 0.60);
        $_SESSION['game_state'] = game_state::ongoing;

        for($c = 0; $c < $GLOBALS['ROWS']; $c++){
            $_SESSION['board'][$c] = [];
            for($r = 0; $r < $GLOBALS['COLS']; $r++){
                $_SESSION['board'][$c][$r] = '.';
            }
        }

        place_ships();
        session_write_close();
    }

    function place_ships() {

        $ships = [2,3,4];
        $ship_index = 0;

        while($ship_index !== 3){
            $rand_direction = rand(0,1); //0 is vertically down, 1 is horizontial right
            $rand_x_position = rand(0,4);
            $rand_y_position = rand(0,6);

            if(attempt_to_place_ship($rand_direction, $rand_x_position, $rand_y_position,$ships[$ship_index]) == true){
                $ship_index++;
            }
        }
        
    }

    function attempt_to_place_ship($direction, $x_pos, $y_pos, $ship_length) {

        if($_SESSION['board'][$x_pos][$y_pos] != '.'){
            return false;
        }

        switch($direction)
        {
            case 0:
                //Place it vertically, test if placement exceeds the board
                if($x_pos + $ship_length > $GLOBALS['ROWS']){
                    return false;
                }


                //Test if the entire space is empty
                for($i = $x_pos; $i < $x_pos + $ship_length; $i++){
                    if($_SESSION['board'][$i][$y_pos] != '.'){
                        return false;
                    }
                }

                for($i = $x_pos; $i < $x_pos + $ship_length; $i++){
                    $_SESSION['board'][$i][$y_pos] = 's';
                }
                return true;
        
            case 1:
                //Place it horizontally, test if placement exceeds the board
                if($y_pos + $ship_length > $GLOBALS['COLS']){
                    return false;
                }
    
                //Test if the entire space is empty
                for($i = $y_pos; $i < $y_pos + $ship_length; $i++){
                    if($_SESSION['board'][$x_pos][$i] != '.'){
                        return false;
                    }
                }

                for($i = $y_pos; $i < $y_pos + $ship_length; $i++){
                    $_SESSION['board'][$x_pos][$i] = 's';
                }

                return true;

        }
    }

    function render_battleship_board(){

        $board = $_SESSION['board'];
        
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
                }else{
                    //Otherwise, generate a button that allows user to guess a square if the game is ongoing
                    if($_SESSION['game_state'] == game_state::ongoing){
                        echo '<form action="battleship.php" method="post">
                        <input type="hidden" name="name" id="name" value="' . $_POST['name'] . '">
                        <input type="hidden" name="move" id="move" value="' . urlencode("$i,$j") . '">
                        <button type="submit">?</button>
                        </form>';
                    }else{
                        if($board[$i][$j] == 's'){
                            echo 'S';
                        }else{
                            echo '.';
                        }
                    }
                }
                echo '</td>';
            }
            echo '</tr>';
            
            
        }
        echo '</table>';
        
    }

    //Handles a new move and changes the board to reflect the move
    function handle_new_move(){
        //GAME INFO
        // . == empty
        // s == ship
        // x == hit
        // o == miss

        $new_move = explode(',',urldecode($_POST['move']));
        if($_SESSION['board'][$new_move[0]][$new_move[1]] == '.'){
            $_SESSION['board'][$new_move[0]][$new_move[1]] = 'o';
        }else if($_SESSION['board'][$new_move[0]][$new_move[1]] == 's'){
            $_SESSION['board'][$new_move[0]][$new_move[1]] = 'x';
        }
        $_SESSION['turns_left']--;

    }

    function check_game_state(){
        //Victory condition: Eliminated all ships
        if(check_victory() == true){
            $_SESSION['game_state'] = game_state::won;
            echo '<h1>You win!</h1>';
        }else if($_SESSION['turns_left'] == 0){
            //Loss condition: Ran out of turns
            $_SESSION['game_state'] = game_state::over;
            echo '<h1>You lose!</h1>';
        }else{
            echo '<h1> Moves left: '. $_SESSION['turns_left'] . ' </h1>';
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
