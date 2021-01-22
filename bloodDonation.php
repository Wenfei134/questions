<?php 

if( isset($_GET['input']) ){
    $input = $_GET['input'];
    echo json_encode(calculatePatients( $input ));
    exit();
}

function canReceiveBlood( $donorIndex, $patientIndex ){
    if( ($patientIndex % 2) == 1){ // is + blood type 

        if( $patientIndex == 5 ){ // type B+ 
            if( $donorIndex == 2 || $donorIndex == 3 ){ // is type A
                return false;
            }
        }

        return $donorIndex <= $patientIndex; // can take any blood lower than it 
    } else { // is - blood type 

        if( ($donorIndex % 2) == 1){ // donor is + blood type 
            return false;
        }

        if( $patientIndex == 4 ){ // type B-
            if( $donorIndex == 3 || $donorIndex == 2){
                return false;
            }
        }

        return $donorIndex <= $patientIndex; // filtered out the + blood types
    }
}

function calculatePatients( $patientString ){

    $patientsSaved = 0;

    //This doesn't work because of the way I set up the input
    $patientsAndDonors = explode( "\n", $patientString ); // take input string and turn it into an array 
    $donors = explode(' ', $patientsAndDonors[0]);
    $patients = explode(' ', $patientsAndDonors[1]);

    /*
    $patientsAndDonors = explode( " ", $patientString ); // take input string and turn it into an array 
    $donors = array_slice( $patientsAndDonors, 0, 8);
    $patients = array_slice( $patientsAndDonors, 8, 16);
    */

    /**
     * The idea behind this for loop is that if there is a patient that exactly 
     * matched the donor's blood type, that patient should get the blood. Otherwise, we should 
     * search the next level down. We can categorize blood types like so: 
     * O < A < AB, and O can only take O type, A can take O and A, AB can take 
     * O and A and AB. (For simplicity's sake we'll ignore B and the +/- for now). 
     * I'll refer to O as being the "lowest" type and AB as the "highest". Now
     * if O requires more blood, it has to be of type O. Clearly, then, O ought to 
     * match first with O, before any other blood type does. Also, AB ought to match with 
     * AB, since excess AB blood can't be used for any other type. Now that leaves A to
     * match with A as well. This generalizes to all 8 blood types, as generally a 
     * patient taking a "lower" type than themselves means possibly leaving excess of a "higher" 
     * type that the "lower" patients that can't receve. So we try to match each blood type to the 
     * "highest" possible replacement blood, if its own can't be found. And for our purposes, 
     * if two patients are in competition for the same blood type, it doesn't really 
     * matter who it goes to, as we will save one life and not the other.
     */

    for( $i = 0; $i < 8; $i++ ){
        if( $patients[$i] > 0 ){ // patients need more blood
            for( $j = $i; $j >=0; $j-- ){ // for loop to go through potential donors
                if( $donors[$j] > 0 ){ // if donors have blood 
                    if( canReceiveBlood($j, $i) ){ // if patient can recieve the blood 
                        if( $patients[$i] > $donors[$j] ){ // needs more blood
                            //update number of patients, continue on to next potential donor
                            $patientsSaved += $donors[$j];
                            $patients[$i] -= $excessDonors[$j];
                            $donors[$j] = 0;
                        } else { // if don't need more blood, break out of for loop of potential donors
                            $patientsSaved += $patients[$i];
                            $donors[$j] -= $patients[$i];
                            $patients[$i] = 0;
                            break;
                        }
                    }
                }
                
            }
        }
    }

    return $patientsSaved;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donations</title>
</head>
<body>
    <form onsubmit="mySubmit(event)">
        Input a string:
        <textarea name="input"></textarea>
        <button type="submit" >Submit</button>
    </form>
    <div>Output: <span id="output"></span></div>
</body>
</html>

<script>
    function mySubmit(e){
        e.preventDefault();
        const input = encodeURI(e.target.elements['input'].value);

        fetch( "http://localhost:3000/bloodDonation.php?input=" + input, {
            method: 'GET'
        })
        .then(res => res.json())
        .then(data => {
            console.log( data );
            document.getElementById("output").innerHTML = data;
        });
    }
</script>

