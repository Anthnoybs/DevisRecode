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

 

 //déclaration des instances nécéssaires :
 $user= $_SESSION['user'];
 $Database = new App\Database('devis');
 $Database->DbConnect();
 $Keyword = new App\Tables\Keyword($Database);
 $Client = new App\Tables\Client($Database);
 $Contact = new \App\Tables\Contact($Database);
 $Cmd = new App\Tables\Cmd($Database);
 $General = new App\Tables\General($Database);


 $keywordList = $Keyword->get2_icon();


 //si une creation de societe à été effectué:
 if (!empty($_POST['societe']) && !empty($_POST['ville']) && !empty($_POST['nouveauClientId']))
 {

   $responseClient  = $Client->insertOne($_POST['societe'],$_POST['adr1'],$_POST['adr2'],$_POST['cp'],$_POST['ville']);
    $crea = $Cmd->updateClientContact($responseClient , null , $_POST['nouveauClientId'] );

   $_POST['recherche-fiche'] = 'id-fiche';
   $_POST['rechercheF'] = $_POST['nouveauClientId'];
 }


 // Si une creation de contact à été effectuée:
 if (!empty($_POST["contactCreaPost"]) && !empty($_POST["fonctionContact"]) && !empty($_POST["nomContact"]) && !empty($_POST["idCmdContactCrea"])) 
 {
   $contact = $Contact->insertOne($_POST['fonctionContact'],$_POST['civiliteContact'],$_POST['nomContact'],$_POST['prenomContact'],
   $_POST['telContact'],$_POST['faxContact'],$_POST['mailContact'],$_POST['contactCreaPost']);

   $updateCmd = $General->updateAll('cmd', $contact , 'cmd__contact__id_fact' , 'cmd__id' , $_POST["idCmdContactCrea"] );

   $_POST['recherche-fiche'] = 'id-fiche';
   $_POST['rechercheF'] = $_POST['idCmdContactCrea'];

 }
 
 
 //si un changement de client ou de contact a été effectué : 
 if (!empty($_POST['postSociety']) && !empty($_POST['postCmd'])) 
 {
    
      $contact = null;
      if (!empty($_POST['selectContact'])) 
      {
        $contact = $_POST['selectContact'];
      }

      $Cmd->updateClientContact($_POST['postSociety'] , $contact , $_POST['postCmd'] );
      $_POST['recherche-fiche'] = 'id-fiche';
      $_POST['rechercheF'] = $_POST['postCmd'];
 }


 //si une mise a jour de ligne a été effectuée: 
 if (!empty($_POST['qteFTC']) && !empty($_POST['qteLVR']) && !empty($_POST['prixLigne']) && !empty($_POST['idCMDL']))
 {
   
  $Cmd->updateLigneFTC(intval($_POST['idCMDL']), intval($_POST['qteCMD']) , intval($_POST['qteLVR']), intval($_POST['qteFTC']), floatval($_POST['prixLigne']));
  
   $return = $Cmd->returnDevis(intval($_POST['idCMDL']));
   $_POST['recherche-fiche'] = 'id-fiche';
   $_POST['rechercheF'] = $return->devis__id;
    
 }




 
 
//par défault le champ de recherche est égal a null:
 $champRecherche = null;
 
// variable qui determine la liste des devis à afficher:
if (!empty($_POST['recherche-fiche'])) 
{
    switch ($_POST['recherche-fiche']) {
        case 'search':
            if ($_POST['rechercheF'] != "") 
            {
               $devisList = $Cmd->magicRequestStatus($_POST['rechercheF'] , 'IMP');
               $champRecherche = $_POST['rechercheF'];
               break;
            }
            else 
            {
               $devisList = $Cmd->getFromStatusAll('IMP');
               $champRecherche = $_POST['rechercheF'];
               break;
            }
           
        case 'id-fiche':
           $devisList = [];
           $devisSeul = $Cmd->GetById(intval($_POST['rechercheF']));
           $champRecherche = $_POST['rechercheF'];
           array_push($devisList, $devisSeul);
           break;
        
        default:
           $devisList = $Cmd->getFromStatusAll('IMP');
           break;
    }
   
} else $devisList = $Cmd->getFromStatusAll('IMP');

//nombre des fiches dans la liste 
$NbDevis = count($devisList);

//liste des transporteur :
$TransportListe = $Keyword->getTransporteur();

//formatte la date pour l'utilisateur:
 foreach ($devisList as $devis) 
 {
   $devisDate = date_create($devis->cmd__date_cmd);
   $date = date_format($devisDate, 'd/m/Y');
   $devis->devis__date_crea = $date;
   if ($devis->cmd__date_envoi) 
   {
      $envoiDate = date_create($devis->cmd__date_envoi);
      $envoiDate = date_format($envoiDate, 'd/m/Y');
      $devis->cmd__date_envoi = $envoiDate;
   }

   $devis->DataLigne = json_encode($Cmd->devisLigne($devis->devis__id));
  
 }


  
// Donnée transmise au template : 
echo $twig->render('facture.twig',
[
'user'=>$user,
'devisList'=>$devisList,
'NbDevis'=>$NbDevis,
'champRecherche'=>$champRecherche,
'transporteurs'=>$TransportListe,
'keywordList' => $keywordList


]);