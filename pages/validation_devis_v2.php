<?php
require "./vendor/autoload.php";
require "./App/twigloader.php";
session_start();

if ($_SESSION['user']->user__devis_acces < 10) {
  header('location: noAccess');
}

//URL bloqué si pas de connexion :
if (empty($_SESSION['user']->id_utilisateur) || empty($_POST['ValideCmd'])) {
  header('location: login');
} else {

  $user = $_SESSION['user'];

  //Connexion et requetes : 
  $Database = new App\Database('devis');
  $Devis = new App\Tables\Cmd($Database);
  $Database->DbConnect();
  $Stats = new App\Tables\Stats($Database);
  $_SESSION['user']->commandes_cours = $Stats->get_user_commnandes($_SESSION['user']->id_utilisateur);
  $alert_aux_gogols = false ;
  //recupere le devis:
  $devis = $Devis->GetById($_POST['ValideCmd']);

  //recupere les lignes de devis actives:
  $arrayOfDevisLigne = $Devis->devisLigne_sous_ref_actif($_POST['ValideCmd']);

  //recupere les garanties liees:
  foreach ($arrayOfDevisLigne as $ligne) 
  {
	 $xtendArray = $Devis->xtenGarantie($ligne->devl__id);
	 $ligne->devl__prix_barre = $xtendArray;

	 //si l'article est un article générique non crée en BDD :  
	if (intval($ligne->id__fmm) == 10 ) 
	{
		$alert_aux_gogols = 'L\' article non-communiqué est présent ! Impossible de valider le devis ';
	}  
  }

  //attention !!! 2 eme boucle avant l'encodage du json car l'utilisation d'unset dans la requete rend l'encode pourri {{}} à la place de [{}] : 
  $array_temp = [];
  foreach ($arrayOfDevisLigne as $value) 
  {
	 array_push($array_temp , $value );
  }
  //encode en json pour Javascript:
  $jsonPack = json_encode($array_temp);
 
  // Donnée transmise au template : 
  echo $twig->render('validation_devis_v2.twig', [
    'user' => $user,
    'user' => $user,
    'devis' => $devis,
    'devisLigne' => $arrayOfDevisLigne,
    'jsonPack' => $jsonPack,
    'alerte_aux_gogols' => $alert_aux_gogols
  ]);
}
