<?php


function getDateForSpecificDayBetweenDates($startDate, $endDate, $weekdayNumber)
{
    $dateArr = array();

    do
    {
        if(date("w", $startDate) != $weekdayNumber)
        {
            $startDate += (24 * 3600); // add 1 day
        }
    } while(date("w", $startDate) != $weekdayNumber);


    while($startDate <= $endDate)
    {
        $dateArr[] = date('Y-m-d', $startDate);
        $startDate += (7 * 24 * 3600); // add 7 days
    }

    return($dateArr);
}

function getDateEndCurrentSemester () {
    switch (date('m')) {
        case 7:
        case 8:
        case 9:
        case 10:
        case 11:
        case 12:
        case 01:
            return strtotime("30 January");
            break;
        default:
            return strtotime("30 June");
            break;
    }
}

function insertProposal ($bdd, $direction, $time, $usrLogin) {
    $req = $bdd->prepare("INSERT INTO Proposal (direction, `time`, usrLogin) VALUES (:direction, :time, :usrLogin)") or die("Erreur lors de la requette à la BDD : " . print_r($bdd->errorInfo()));
    $req->execute(array(
        "direction" => $direction,
        "time" => $time,
        "usrLogin" => $usrLogin
    ));
}

function insertRide($bdd, $tripId, $date) {
    $req2 = $bdd->prepare("INSERT INTO Ride (tripId, `date`) VALUES (:tripId, :date)") or die("Erreur lors de la requette à la BDD : " . print_r($bdd->errorInfo()));
    $req2->execute(array(
        "tripId" => $tripId,
        "date" => $date
    ));
}

function getDirections ($bdd) {
    $req = $bdd->query("SELECT column_type FROM information_schema.columns WHERE table_name = 'Proposal' AND column_name = 'direction' ");
    $input_line = $req->fetch();
    return parseEnum($input_line[0]);
}    

function getTimes ($bdd) {
    $req = $bdd->query("SELECT column_type FROM information_schema.columns WHERE table_name = 'Proposal' AND column_name = 'time' ");
    $input_line = $req->fetch();
    return parseEnum($input_line[0]);
}    

function parseEnum ($input_line) {
    preg_match("/^enum\((.*)\)$/", $input_line, $output_array);
    $line = str_replace("'", "", $output_array[1]);
    return explode(",", $line);
}

function duplicateDetected ($bdd, $login, $time, $direction, $date) {
    $req = $bdd->prepare("SELECT driver FROM V_Trips WHERE driver=:login AND time=:time AND direction=:direction AND date=:date");
    $req->execute(array(
        "login" => $login,
        "time" => $time,
        "direction" => $direction,
        "date" => $date
    ));
    return $req->rowCount() != 0;
}