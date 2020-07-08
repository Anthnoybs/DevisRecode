<?php
require "./vendor/autoload.php";
require "./App/twigloader.php";
session_start();

 //URL bloqué si pas de connexion :
 if (empty($_SESSION['user'])) 
 {
    header('location: login');
 }
 if ($_SESSION['user']->user__devis_acces < 10 ) 
 {
   header('location: noAccess');
 }

 unset($_SESSION['Contact']);
 unset($_SESSION['Client']);
 unset($_SESSION['livraison']);
 unset($_SESSION['ModifierDevis']);

 //déclaration des instances nécéssaires :
 $user= $_SESSION['user'];
 $Database = new App\Database('devis');
 $Database->DbConnect();
 $Keyword = new App\Tables\Keyword($Database);
 $Client = new App\Tables\Client($Database);
 $Contact = new \App\Tables\Contact($Database);
 $Cmd = new App\Tables\Cmd($Database);
 $listOfStatus = $Keyword->getStat();
 $devisList = [];

 // Si un devis a été validé: 
if (!empty($_POST['clientSelect'])) 
{
   //deserialize du json contenenant la data: 
   $devisData = json_decode($_POST["dataDevis"]);

   //date du jour:
   $date = date("Y-m-d H:i:s");

   //client facturé du devis: 
   $client = $Client->getOne($_POST['clientSelect']);

   // corrige la notice lié a l'accesion d'un non objet -> : si certain champs sont NUll: 
   $contactId = NULL;
   $livraisonId = NULL;
   $livraisonContact = NULL;

   //si un contact de livraison a été choisi: 
   if (!empty( $_POST['contactSelect'])) 
   {
     $contactId = $_POST['contactSelect'];
     $contact = $Contact->getOne($_POST['contactSelect']);
   }

   //si une adresse de livraison differente a ete spécifiée:
   if (!empty($_POST['livraisonSelect'])) 
   {
      $livraisonId = $_POST['livraisonSelect'];
   }

   //si un contact est associé a cette meme livraison: 
   if (!empty($_POST['contact_livraison'])) 
   {
      $livraisonContact = $_POST['contact_livraison'];
   }

   //satus par default est en attente:
   $status = 'ATN';

   //traite l'affichage du total :
   if (isset($_POST['ShowTotal'])) 
   {
    $total = $_POST['ShowTotal'];
   }
   else
   { 
     $total = 'STT'; 
   }

   // traitement du titre du devis  :  
   if (!empty($_POST['titreDevis'])) 
   {
    $accents = array('/[áàâãªäÁÀÂÃÄ]/u'=>'a','/[ÍÌÎÏíìîï]/u'=>'i','/[éèêëÉÈÊË]/u'=>'e','/[óòôõºöÓÒÔÕÖ]/u'=>'o','/[úùûüÚÙÛÜ]/u'=>'u','/[çÇ]/u' =>'c');
    $titre = preg_replace(array_keys($accents), array_values($accents), $_POST['titreDevis']); 
    $titre  = strtoupper($titre);
    $titre = preg_replace('/([^.a-z0-9]+)/i', '-', $titre);
   }
   else 
   {
       $titre = '' ;
   }

  //si c'est une modification de devis: 
   if (!empty($_POST['ModifierDevis']))
    {
     $devis = $Cmd->Modify(
        intval($_POST['ModifierDevis']),
        $date,
        $_SESSION['user']->id_utilisateur,
        $_POST['clientSelect'],
        $livraisonId,
        $contactId,
        $_POST['globalComClient'],
        $_POST['globalComInt'],
        $status,
        $total,
        $devisData , 
        $livraisonContact  , 
        $titre
     );
     header('location: mesDevis');
   }
  //sinon je cree un nouveau devis:
 else
  {
    $devis = $Cmd->insertOne(
        $date,
        $_SESSION['user']->id_utilisateur,
        $_POST['clientSelect'],
        $livraisonId,
        $contactId,
        $_POST['globalComClient'],
        $_POST['globalComInt'],
        $status,
        $total,
        $devisData,
        $livraisonContact , 
        $titre 
    );
    header('location: mesDevis');
  }
}

//traitement des status de devis:
if (!empty($_POST['ValiderDevis'])) 
{
  $Cmd->updateStatus($_POST['statusRadio'],$_POST['ValiderDevis']);
}
if (!empty($_POST['RefuserDevis'])) 
{
  $Cmd->updateStatus('RFS',$_POST['RefuserDevis']);
}

//accès au button Voir mes devis si droit ok:
//par default j'affiche uniquement mes devis: 
$_SESSION['vueDevis'] = "MINE";

//le buttons affiche:
$AllDevis = "Voir tous";

//si les droits d'acces de l'utilisateur sont plus gand ou égal a 15 j'affiche le button
if ($_SESSION['user']->user__devis_acces >= 15 ) 
{
  //si le formulaire est conforme j'affiche tous les devis:
  if (!empty($_POST['MyDevis']) && $_POST['MyDevis'] == "Voir tous") {
  $_SESSION['vueDevis'] = "ALL";}   
}

//variable du résultat de la recherche:
$recherche = false ; 
if (!empty($_POST['rechercheP'])) 
{
  $recherche = $_POST['rechercheP'];
}

//affichage de la liste de devis:
//si je consulte tous les devis:
if ( $_SESSION['vueDevis'] == "ALL")
{
    //Si une recherche a ete demandée
    if (!empty($_POST['rechercheP']))
    {
      //je recherche dans toute la base de donnée
      $devisList = $Cmd->magicRequest($_POST['rechercheP']);
    }
    else 
    {
      //sinon je recupere tous les devis:
      $devisList = $Cmd->getAll();
    }
    //le button contient:
    $AllDevis = "Voir mes devis";
}

//si l'utilisateur consulte uniquement ses devis:
elseif ($_SESSION['vueDevis'] == "MINE") 
{
    if (!empty($_POST['rechercheP'])) 
    {
      //je recherche dans ses devis:
      $devisList = $Cmd->magicRequestUser($_POST['rechercheP'], intval($_SESSION['user']->id_utilisateur));
    }
    else 
    {
      $devisList = $Cmd->getUserDevis($_SESSION['user']->id_utilisateur);
    }
  //le button affiche:
  $AllDevis = "Voir tous"; 
}


$notifValid = 0 ;
foreach ($devisList as $devis) 
{
  if ($devis->kw__lib == "Valide") 
  {
   $notifValid +=1 ;
  }
}

//compte le nombre de devis resultant de la recherche:
$NbDevis = count($devisList);

//remplace la date brute par une date adapté à l'utilisateur:
foreach ($devisList as $devis) 
{
  $devisDate = date_create($devis->devis__date_crea);
  $date = date_format($devisDate, 'Y/m/d');
  $devis->devis__date_crea = $date;
}

// si une validation de devis a été effectuée : 
if(!empty($_POST['devisCommande']))
{
  $date = date("Y-m-d H:i:s");
  $Cmd->updateStatus('CMD',$_POST['devisCommande']);
  $Cmd->updateDate('cmd__date_cmd' , $date , $_POST['devisCommande'] );
  $Cmd->updateAuthor('cmd__user__id_cmd' , $_SESSION['user']->id_utilisateur , $_POST['devisCommande']);
  if (!empty($_POST['arrayLigneDeCommande'])) 
  {
    $validLignes = json_decode($_POST['arrayLigneDeCommande']);
    foreach ($validLignes as $lignes) 
    {
    
      $Cmd->updateGarantie(
        $lignes->devl__prix_barre[0],
        $lignes->devl__prix_barre[1],
        $lignes->devl__note_interne,
        $lignes->devl_quantite,
        $lignes->cmdl__cmd__id,
        $lignes->devl__ordre );
    } 
  }
  header('Location: mesDevis');
}
 
// Donnée transmise au template : 
echo $twig->render('mesDevis.twig',
[
'user'=>$user,
'devisList'=> $devisList,
'listOfStatus'=> $listOfStatus ,
'AllDevis'=> $AllDevis , 
'notifValid'=> $notifValid , 
'NbDevis' => $NbDevis, 
'recherche' => $recherche ,
'debug'=> $_SESSION['vueDevis']
]);