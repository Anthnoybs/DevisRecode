<?php
require "./vendor/autoload.php";

session_start();
$Database = new App\Database('devis');
$Database->DbConnect();
$Article = new App\Tables\Article($Database);


if (empty($_SESSION['user']->id_utilisateur)) {
    echo 'Connection failed';
} else {
    //requete renvoi le pn selectionné de la ligne ainsi qu' un tableau des autre pn dispo pour cet id fmm 
    if (!empty($_POST['pn'])) {
        $pnList = json_encode($Article->get_pn_for_devis($_POST['pn']));
        echo $pnList;
    } else {
        echo 'request failed';
    }
}
