<?php 
$header_name='';
$val=$_SERVER['PHP_SELF'];
switch ($val) {
    case '/parking_reservation/about.php':
    echo('About Us');
        break;
    
    case '/parking_reservation/myaccount.php':
    echo('My Account');
        break;
    
    case  '/parking_reservation/reserve.php':
    echo('Reserve a Spot');
        break;
    
    case  '/parking_reservation/myreservations.php':
    echo('My Reservations');
        break;
    
    case  '/parking_reservation/login.php':
    echo('Login');
        break;
}