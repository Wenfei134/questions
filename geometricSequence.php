<?php 

/**
 * I figure since I'll likely be coding in PHP, I'd use it for the coding examples 
 * PHP is an interesting language with the way it deals with scope, and the way it 
 * can display HTML right in the file. Normally there would be different files for 
 * the HTML, Javascript, and PHP, but for the purpose of being brief, I thought I 
 * would simply place them all in one file.
 */

if( isset($_GET['input']) ){
    $numMinutes = filter_var($_GET['input'], FILTER_VALIDATE_INT);
    echo json_encode(calculateSequences( $numMinutes ));
    exit();
}


function withinBounds( $thirdDigit, $fourthDigit ){
    if( $thirdDigit < 6 && $thirdDigit >= 0 && $fourthDigit < 10 && $fourthDigit >= 0 ){
        return true;
    }
    return false;
}

function calculateSequences( $numMinutes ){
    if( $numMinutes <= 33 ){
        return 0;
    }

    /**
     * Hard coded the first value for 12:34 because I would like to start with 1 o'clock instead of 12
     */
    if( $numMinutes >= 34 && $numMinutes <= 60 ){
        return 1;
    }

    $numMinutes = $numMinutes - 60;
    $numSequences = 1;

    /**
     * The number of times we will repeat the 12 hour (720 minute) cycle 
     * Got the number by running the current algorithm with 719 as input and a dummy placement value
     */
    $repeats = intdiv($numMinutes, 720);
    $numSequences += $repeats * 31;

    /**
     * The number of remainding minutes we have 
     */
    $remainingMinutes = $numMinutes % 720;


    /**
     * calculating the number of geometric sequences between 1 o'clock and 12 o'clock 
     * and why 12:34 is hardcoded in the beginning, otherwise the for loop would start in a 
     * rather awkward position
     */
    for( $i = 1; $i <= 12; $i++ ){
        $firstDigit = intdiv($i, 10); 
        $secondDigit = $i % 10;

        /**
         * sequences stores an array of all the possible third and fourth digits 
         */
        $sequences = [];

        // either 10, 11, or 12. 10 has 0, 11:11, and 12:34 are the only geometric sequences. 
        if($firstDigit != 0 ){ 
            if( $secondDigit == 1 ){
                $sequences = [11];
            } else if( $secondDigit == 2){
                $sequences = [34];
            }
        } else{ // then 1 to 9

            for( $difference = -4; $difference <= 4; $difference++ ){ // 9:51 and 1:59 gives the +/- 4 bounds
                $thirdDigit = $secondDigit - $difference;
                $fourthDigit = $thirdDigit - $difference;

                if( withinBounds($thirdDigit, $fourthDigit) ){
                    array_push( $sequences, $thirdDigit * 10 + $fourthDigit );
                }
            }

        }

        /**
         * if there was more than an hour remaining then all the sequences were within the 
         * given time, and we move onto the next hour. Otherwise, we filter out the possible
         * sequences which exceed the time we're given, add only the ones remaining, 
         * and break out of the for loop
         */

        if( $remainingMinutes >= 60 ){
            $remainingMinutes = $remainingMinutes - 60;
            $numSequences += count($sequences);
        } else {
            $sequences = array_filter( $sequences, function($minuteValue){
                return $minuteValue <= $remainingMinutes;
            });
            $numSequences += count( $sequences );
            break;
        }
    }

    return $numSequences;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geometric Sequences</title>
</head>
<body>
    <form onsubmit="mySubmit(event)">
        Input an integer:
        <input name="input" type="number"></input>
        <button type="submit" >Submit</button>
    </form>
    <div>Output: <span id="output"></span></div>
</body>
</html>

<script>
    function mySubmit(e){
        e.preventDefault();
        const input = e.target.elements['input'].value;
        fetch( "http://localhost:3000/geometricSequence.php?input=" + input, {
            method: 'GET'
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById("output").innerHTML = data;
        });
    }
</script>



