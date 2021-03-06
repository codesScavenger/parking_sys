<?php
//-- written by Ariela Epstein --//
include_once 'functions.php';

//retrieves all reservations, between the start and end date 
    function getAllReservations($start, $end) {
    try {    
    global $db;
    $query = $db->prepare("SELECT * FROM reservation WHERE Rsrv_Date BETWEEN :start AND :end");
    $query->bindValue(':start', $start);
    $query->bindValue(':end', $end);  
    $query->execute();
    $result = $query->fetchAll();
  
    if ($result) {
    $length = sizeof($result);
    $l = sizeof($result[$length -1]);
    $length2 =  $l / 2;
    for($i = 0; $i <$length; $i++) {
    for($m = 0; $m<$length2; $m++) {
    $stuff[$i][$m] = $result[$i][$m]; 
    }}
    return $stuff; 
    }
    else {
    return FALSE;}
    }
    catch (PDOException $ex)
    {
    echo "problem getting all reservations database".$ex->GetMessage();
    exit;
    }
}

//deletes from the database this reservation, and updates the lot_by_day type amount to its previous amount.
function deleteFromDb($date, $uid, $pid) {
    try {
    global $db;
    $query = $db->prepare("DELETE FROM reservation WHERE Rsrv_Date = :date AND Rsrv_UserID = :uid AND Rsrv_ParkingLotID = :pid");
    $query->bindValue(':date', $date);
    $query->bindValue(':uid', $uid);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();
    $rowcount = $query->rowCount() ? "success" : "fail";
    
    if($rowcount == "success") {
    $type = getUserType($uid);
    if(restoreReserve($date, $pid, $type) == true) {
        return true;
    }
    else {return "failed to restore availability amount";}
    }
    else {return "failed to delete reservation and restore availability amount";}
    }
    catch (PDOException $ex)
    {
    echo "problem deleting reservation from database".$ex->GetMessage();
    exit;
    }  
}

//gets the user's parking type (for function deletefromdb)
function getUserType ($uid) {
    try {
    global $db;
    $query = $db->prepare("SELECT User_ParkingType FROM user WHERE User_ID = :uid");
    $query->bindValue(':uid', $uid);
    $query->execute();
    $result = $query->fetch();
    return $result;
    }
    catch (PDOException $ex)
    {
        echo "problem getting user's type from database".$ex->GetMessage();
        exit;
    }  
}

//restores the parking/type availability amount after a reservation was canceled (for function deletefromdb)
function restoreReserve($date, $pid, $type) {
    try {
    global $db;
    if(in_array("M", $type)) {
    $query = $db->prepare("UPDATE lot_by_day SET M_Available=  M_Available + 1 WHERE Lot_ID = :pid AND Date = :date"); 
    $query->bindValue(':date', $date);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();

    return $query->rowCount() ? true : false;
    }
    if(in_array("E", $type)) {
    $query = $db->prepare("UPDATE lot_by_day SET E_Available=  E_Available + 1 WHERE Lot_ID = :pid AND Date = :date"); 
    $query->bindValue(':date', $date);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();
   
    return $query->rowCount() ? true : false;
    }
    if(in_array("R", $type)) {
    $query = $db->prepare("UPDATE lot_by_day SET R_Available=  R_Available + 1 WHERE Lot_ID = :pid AND Date = :date"); 
    $query->bindValue(':date', $date);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();
   
    return $query->rowCount() ? true : false;
    }
    if(in_array("D", $type)) {
    $query = $db->prepare("UPDATE lot_by_day SET D_Available=  D_Available + 1 WHERE Lot_ID = :pid AND Date = :date");
    $query->bindValue(':date', $date);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();
   
    return $query->rowCount() ? true : false;
    }
    }
    catch (PDOException $ex)
    {
        echo "problem updating availability in database".$ex->GetMessage();
        exit;
    }  
}

//gets all users
function getUsers() {
    try {
    global $db;
    $query = $db->prepare("SELECT * FROM user");
    $query->execute();
    $result = $query->fetchAll();
    
    if($result) {return $result;}
    else{ return FALSE;}
    }
    catch (PDOException $ex)
    {
    echo "problem getting all users from database".$ex->GetMessage();
    exit;
    }
}

//updates a user with new input
function updateUser($id,$name,$email,$password,$ptype,$license,$admin) {
    try {
    global $db;
        $query = $db->prepare("UPDATE user SET User_Name = :name, User_Password = :password, User_ParkingType = :ptype,
        User_License = :license, admin = :admin WHERE User_Email = :email AND User_ID = :id");
	$query->bindValue(':id', $id);
        $query->bindValue(':name', $name);
        $query->bindValue(':email', $email);
        $query->bindValue(':password', $password);
        $query->bindValue(':ptype', $ptype);
        $query->bindValue(':license', $license);
        $query->bindValue(':admin', $admin);
        $query->execute(); 
    } 
    catch (PDOException $ex)
    {
        echo "problem updating user in database".$ex->GetMessage();
        exit;
    }
    
}

//deletes a user from the database, by the user's id
function deleteUser($id) {
    try {
    global $db;
    deleteUserReserve($id);
    $query = $db->prepare("DELETE FROM user WHERE User_ID = :id");
    $query->bindValue(':id', $id);
    $query->execute(); 
    
    $rowcount=$query->rowCount();
    $query->closeCursor();
    return $rowcount;
    }
    catch (PDOException $ex)
    {
    echo "problem deleting user from database".$ex->GetMessage();
    exit;
    }
}

//add into parking type a new row
function addPType($pid, $pdes) {
    try {
    global $db;
    $query = $db->prepare("INSERT INTO parking_type VALUES (:pid, :pdes)");
    $query->bindValue(':pid', $pid);
    $query->bindValue(':pdes', $pdes);
    return $query->execute();
    
    $rowcount = $query->rowCount();
    $query->closeCursor();
    return $rowcount;
    }
    catch (PDOException $ex)
    {
    echo "problem adding a new parking type to parking_type table in database".$ex->GetMessage();
    exit;
    }
}

//add into parking lot a new row
function addPLot($plid, $plname, $plcity, $plstreet, $plbuilding, $pllocation, $plm, $ple, $plr, $pld) {
    try {
    global $db;
    $query = $db->prepare("INSERT INTO parking_lot VALUES ( :plid, :plname, :plcity, :plstreet, :plbuilding, :pllocation, :plm, :ple, :plr, :pld)");
    $query->bindValue(':plid', $plid);
    $query->bindValue(':plname', $plname);
    $query->bindValue(':plcity', $plcity);
    $query->bindValue(':plstreet', $plstreet);
    $query->bindValue(':plbuilding', $plbuilding);
    $query->bindValue(':pllocation', $pllocation);
    $query->bindValue(':plm', $plm);
    $query->bindValue(':ple', $ple);
    $query->bindValue(':plr', $plr);
    $query->bindValue(':pld', $pld);
    return $query->execute();
    
    $rowcount = $query->rowCount();
    $query->closeCursor();
    return $rowcount;
    }     
    catch (PDOException $ex)
    {
    echo "problem adding a new parking lot to parking_lot table in database".$ex->GetMessage();
    exit;
    }
}

function getallLots () {
    try{
    global $db;
    $query = $db->prepare("SELECT Lot_ID, Lot_Name FROM parking_lot");
    $query->execute();
    $result = $query->fetchAll();
    
    if($result) {return $result;}
    else{ return FALSE;}
    }
    catch (PDOException $ex)
    {
    echo "problem getting all parking lot id's from database".$ex->GetMessage();
    exit;
    }
   
}

function getLotDetails ($thislot) {
    try {
    global $db;
    $query = $db->prepare("SELECT M_Total, E_Total, R_Total, D_Total FROM parking_lot WHERE Lot_ID = :thislot ");
    $query->bindValue(':thislot', $thislot);  
    $query->execute();
    $result = $query->fetch();
    
    if($result) {return $result;}
    else{ return FALSE;}
    }
    catch (PDOException $ex)
    {
    echo "problem getting this lot's details from database".$ex->GetMessage();
    exit;
    }
}
    
function addPDay($lbday2, $lbid, $lbmotor , $lbemerg, $lbreg, $lbdis) {
    try {
    global $db;
    $query = $db->prepare("INSERT INTO `lot_by_day`(`Date`, `Lot_ID`, `M_Available`, `E_Available`, `R_Available`, `D_Available`)
    VALUES ( :lbday2, :lbid, :lbmotor, :lbemerg, :lbreg, :lbdis)");
    $query->bindValue(':lbday2', $lbday2);
    $query->bindValue(':lbid', $lbid);
    $query->bindValue(':lbmotor', $lbmotor);
    $query->bindValue(':lbemerg', $lbemerg);
    $query->bindValue(':lbreg', $lbreg);
    $query->bindValue(':lbdis', $lbdis);
    return $query->execute();
    
    $rowcount = $query->rowCount();
    $query->closeCursor();
    return $rowcount;
    }
    catch (PDOException $ex)
    {
    echo "problem adding a new parking lot to lot_by_day table in database".$ex->GetMessage();
    exit;
    }
}
//// All below added by Avi 
///delete user's reservation when deleting the user
function deleteUserReserve($uid) {
    $date=$pid="";
    try {    
    global $db;
    $query = $db->prepare("SELECT * FROM reservation WHERE Rsrv_UserID = :uid");
    $query->bindValue(':uid', $uid);
    $query->execute();
    $reservations = $query->fetchAll();
    if ($reservations) {
        foreach ($reservations as $row) {
            foreach ($row as $value=>$item) {
                switch ($value) {
                case 'Rsrv_Date':
                  $date=$item;
                  break;
                case 'Rsrv_ParkingLotID':
                  $pid=$item;
                  break;
                default:
                    break;}
            }
            deleteFromDb($date, $uid, $pid);
       }
    }
    else {
    return FALSE;}
    }
    catch (PDOException $ex)
    {
    echo "problem in database".$ex->GetMessage();
    exit;
    }
}
//for my reservations page
function getReserve($rUid){
    try{
        global $db;
        $query = $db->prepare("SELECT Rsrv_Date, Rsrv_ParkingLotID FROM reservation WHERE Rsrv_UserID = :rUid");
        $query->bindValue(':rUid', $rUid);
        $query->execute();
        $result = $query->fetchAll();
        if($result) {return $result;}
    else{ return FALSE;}
    }
    catch (PDOException $ex)
    {
        echo "Problem Getting User Reservations".$ex->GetMessage();
    exit;
    }
}
/// get info about Parking
function getParkingName($lid){
    try{
        global $db;
        $query = $db->prepare("SELECT Lot_Name, City, Street_Name, Building_Number FROM parking_lot WHERE Lot_ID = :lid");
        $query->bindValue(':lid',$lid);
        $query->execute();
        $result = $query->fetchAll();
        if($result) {return $result;}
    else{ return FALSE;}
    }
    catch (PDOException $ex)
    {
        echo "Problem Getting User Reserbations".$ex->GetMessage();
    exit;
    }
}
/// Functions for reserve page
//add reservation
function addReserve($rDate, $rUid, $rPid, $rType) {
    try {
    global $db;
    $query = $db->prepare("INSERT INTO `reservation`(`Rsrv_Date`, `Rsrv_UserID`, `Rsrv_ParkingLotID`)
    VALUES ( :rDate, :rUid, :rPid)");
    $query->bindValue(':rDate', $rDate);
    $query->bindValue(':rUid', $rUid);
    $query->bindValue(':rPid', $rPid);
    return $query->execute();
    
    $rowcount = $query->rowCount();
    $query->closeCursor();
    return $rowcount;
    
    reduceAvailability($rDate, $rPid, $rType);
    }
    catch (PDOException $ex)
    {
    echo "problem adding a new reservation to reservation table in database".$ex->GetMessage();
    exit;
    }
}
// reduce lot from availability (lot by day)after reserve
function reduceAvailability($date, $pid, $type) {
    try {
    global $db;
    if(in_array("M", $type)) {
    $query = $db->prepare("UPDATE lot_by_day SET M_Available=  M_Available - 1 WHERE Lot_ID = :pid AND Date = :date"); 
    $query->bindValue(':date', $date);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();

    return $query->rowCount() ? true : false;
    }
    if(in_array("E", $type)) {
    $query = $db->prepare("UPDATE lot_by_day SET E_Available=  E_Available - 1 WHERE Lot_ID = :pid AND Date = :date"); 
    $query->bindValue(':date', $date);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();
   
    return $query->rowCount() ? true : false;
    }
    if(in_array("R", $type)) {
    $query = $db->prepare("UPDATE lot_by_day SET R_Available=  R_Available - 1 WHERE Lot_ID = :pid AND Date = :date"); 
    $query->bindValue(':date', $date);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();
   
    return $query->rowCount() ? true : false;
    }
    if(in_array("D", $type)) {
    $query = $db->prepare("UPDATE lot_by_day SET D_Available=  D_Available - 1 WHERE Lot_ID = :pid AND Date = :date");
    $query->bindValue(':date', $date);
    $query->bindValue(':pid', $pid);
    $query->execute();
    $query->closeCursor();
   
    return $query->rowCount() ? true : false;
    }
    }
    catch (PDOException $ex)
    {
        echo "problem updating availability in database".$ex->GetMessage();
        exit;
    }  
}
//gets all lot by day
function getAllLBD() {
    try {
    global $db;
    $query = $db->prepare("SELECT * FROM lot_by_day");
    $query->execute();
    $result = $query->fetchAll();
    
    if($result) {return $result;}
    else{ return FALSE;}
    }
    catch (PDOException $ex)
    {
    echo "problem getting all users from database".$ex->GetMessage();
    exit;
    }
}
// Get Only available lots
function availableLots($lots, $usrID, $usrPT){
    $result=array();
    $availability=TRUE;
    // adjust car type to ease search
    switch ($usrPT) {
        case "M":
            $usrPT='M_Available';
            break;
        case "E":
            $usrPT='E_Available';
            break;
        case "R":
            $usrPT='R_Available';
            break;
        case "D":
            $usrPT='D_Available';
            break;
        default:
            $usrPT='';
            break;
    }
    // get usr other reserves to avoid doubles
    $usrReserves=getReserve($usrID);
    if(isset($lots) && is_array($lots)) {
    foreach ($lots as $lbd=>$vals) {
        if($vals["$usrPT"]>0){
            if(isset($usrReserves) && is_array($usrReserves)) {
                foreach ($usrReserves as $rsrv) {
                    if(in_array( $vals['Date'],$rsrv)){
                    $availability=FALSE;
                }}}
            //enter to list if available
            if($availability){array_push($result,$vals);}
       }
       $availability=TRUE;
    } 
}
return $result;
}
///// get the dates(with no repetition) from LBD's
function getDates($lots){
    $result=array();
    if(isset($lots) && is_array($lots)) {
        foreach ($lots as $lbd=>$vals) {
                    if(!(in_array( $vals['Date'],$result))){
                    array_push($result,$vals['Date']);
                }}}
   return $result; 
}

