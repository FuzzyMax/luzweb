<?php
require_once('in/user.php');
require_once('in/zitate.db.php');
$u = new user('tolu','765198666,573419818,412568550,-196795232,-45473808,-379295378,1772061183,-1168965682');

if ($u->is_loggedin()) {
    echo 'You are logged in !!!';
    $d = $u->get_data();
    $zitate = new zitate( $u );
    $e = $zitate->get_byID( 1 );
    $fields = $u->dbFieldnames('zitate');
    var_dump($fields);
}
else {

}

$u->logout();

?>