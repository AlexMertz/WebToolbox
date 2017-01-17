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

function insertProposal ($bdd, $direction, $time, $nbSeats, $dayOfTheWeek, $usrLogin) {
    $req = $bdd->prepare("INSERT INTO Proposal (direction, `time`, dayOfTheWeek, usrLogin, nbSeats) VALUES (:direction, :time, :dayOfTheWeek, :usrLogin, :nbSeats)") or die("Erreur lors de la requette à la BDD : " . print_r($bdd->errorInfo()));
    $req->execute(array(
        "direction" => $direction,
        "time" => $time,
        "dayOfTheWeek" => $dayOfTheWeek,
        "nbSeats" => $nbSeats,
        "usrLogin" => $usrLogin
    ));
}

function insertRide($bdd, $ProposalId, $date) {
    $req2 = $bdd->prepare("INSERT INTO Ride (ProposalId, `date`) VALUES (:ProposalId, :date)") or die("Erreur lors de la requette à la BDD : " . print_r($bdd->errorInfo()));
    $req2->execute(array(
        "ProposalId" => $ProposalId,
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

function extractPostParameters ($toGet)  {
    $values = array();
    foreach ($toGet as $key) {
        $values[$key] = $_POST[$key];
    }
    return $values;
}

function getDaysForReccurency ($recurrency, $dayOfTheWeek, $ponctual_date) {
    $days = array();
    if ($recurrency == "all")
        $days = getDateForSpecificDayBetweenDates(time(), getDateEndCurrentSemester(), $dayOfTheWeek);
    else if ($recurrency == "ponctual") {
        $days[0] = strtotime($ponctual_date);
    }
    else
        myDie("No valid recurrency");
    return $days;
}

function getRide($bdd, $direction, $time, $day) {
    $req = $bdd->prepare("SELECT * FROM V_Trips WHERE direction=:direction AND time=:time AND date=:date AND isFull=0");
    $req->execute(array(
        "direction" => $direction,
        "time" => $time,
        "date" => $day
    ));
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function getProposal($bdd, $id) {
    $req = $bdd->prepare("SELECT v.id, v.driver, v.direction, v.time, v.dayOfTheWeek, v.passengers, v.nbPassengers, v.nbSeats, u.firstName, u.lastName, u.email FROM V_Trips v INNER JOIN Usr u On v.driver = u.login WHERE id=:id");
    $req->execute(array(
        "id" => $id
    ));
    return $req->fetch(PDO::FETCH_ASSOC);
}

function getTripsAsDriver($bdd, $login) {
    $req = $bdd->prepare("SELECT v.id, v.driver, v.direction, v.time, passengers,  group_concat(DISTINCT r.date separator ',') AS `dates` FROM `V_Trips` v INNER JOIN Ride r ON v.id=r.proposalId WHERE v.driver=:login GROUP BY v.id");
    $req->execute(array(
        "login" => $login
    ));
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function getTripsAsPassenger($bdd, $login) {
    $login = "%" . $login . "%";
    $req = $bdd->prepare("SELECT v.id, v.driver, v.direction, v.time, passengers,  group_concat(DISTINCT r.date separator ',') AS `dates` FROM `V_Trips` v INNER JOIN Ride r ON v.id=r.proposalId WHERE v.passengers LIKE :login GROUP BY v.id");
    $req->execute(array(
        "login" => $login
    ));
    return $req->fetchAll(PDO::FETCH_ASSOC);
}

function book ($bdd, $id, $date, $login) {
    $req = $bdd->prepare('INSERT INTO Reservation (usrLogin, rideId) SELECT id, ":usrLogin" FROM Rides WHERE id=:id AND date=date');
    $req->execute(array(
        "id" => $id,
        "date" => $date,
        "usrLogin" => $login
    ));
}