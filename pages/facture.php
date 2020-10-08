<?php
require "./vendor/autoload.php";
require "./App/twigloader.php";
session_start();


 //URL bloqué si pas de connexion :
 if (empty($_SESSION['user'])) 
 {
    header('location: login');
 }
 if ($_SESSION['user']->user__facture_acces < 15 ) 
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
 $Article = new App\Tables\Article($Database);
 

 $articleTypeList = $Article->getModels();
 $prestaList = $Keyword->getPresta();
 $keywordList = $Keyword->get2_icon();
 $tvaList = $Keyword->getAllFromParam('tva');
 $garantiesList = $Keyword->getGaranties();
 $etatList = $Keyword->getEtat();

 
 //variables de sessions  pour alert : 
 if (!empty($_SESSION['facture'])) 
 {
  $alertFacture = $_SESSION['facture'];
  $_SESSION['facture'] = "";
 }
 else 
 {
  $alertFacture = null ;
 }

 if (!empty($_SESSION['archive'])) 
 {
 $alertArchive = $_SESSION['archive'];
 $_SESSION['archive'] = "";
 }
 else 
 {
  $alertArchive = null ;
 }

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


 //si une mise a jour du modal tva/commentaires/code_cmd à été effectué: 
 if (!empty($_POST['hiddenTVA'])) 
 {
   $updateTVA = $General->updateAll('cmd' , $_POST['selectTVA'] , 'cmd__tva' , 'cmd__id' , $_POST['hiddenTVA'] );
   $updateCodeCmd = $General->updateAll('cmd' , $_POST['codeCmdTVA'] , 'cmd__code_cmd_client' , 'cmd__id' , $_POST['hiddenTVA'] );
   $updateComClient = $General->updateAll('cmd' , $_POST['comTVA'] , 'cmd__note_client' , 'cmd__id' , $_POST['hiddenTVA'] );
   $_POST['recherche-fiche'] = 'id-fiche';
   $_POST['rechercheF'] = $_POST['hiddenTVA'];
 }


 //si une mise a jour de ligne a été effectuée: 
 if ( !empty($_POST['prixLigne']) && !empty($_POST['idCMDL']))
 {
 
  
  $Cmd->updateLigneFTC(intval($_POST['idCMDL']), intval($_POST['qteCMD']) , intval($_POST['qteLVR']), intval($_POST['qteFTC']),  $_POST['comFacture'] ,floatval($_POST['prixLigne']));

    if (!empty($_POST['designationLigne'])) 
    {
      $update = $General->updateAll('cmd_ligne' , $_POST['designationLigne'] , 'cmdl__designation' , 'cmdl__id' , $_POST['idCMDL']);
    }
  
   $return = $Cmd->returnDevis(intval($_POST['idCMDL']));
   $_POST['recherche-fiche'] = 'id-fiche';
   $_POST['rechercheF'] = $return->devis__id;
    
 }



 // si une commande à été postée: 
 if (!empty($_POST['hiddenCommentaire'])) 
 {
   
   //  2  changer le status de la commande et attribuer un numero de facture:
   $Cmd->commande2facture($_POST['hiddenCommentaire']);
   //  4 activer une alert pour indiquer le bon fonctionnement du logiciel 
   $Cmd->classicReliquat($_POST['hiddenCommentaire']);
   //  3 enregistrer la facture au format pdf dans un folder 
   
 }


 

 // si un rajout de ligne à été effectué:
if (!empty($_POST['idDevisAddLigne']) && !empty($_POST['prestationChoix']) ) 
{
 $idDuPost = $_POST['idDevisAddLigne'];
 $object = new stdClass;
 $object->idDevis = $_POST['idDevisAddLigne'];
 $object->prestation = $_POST['prestationChoix'];
 $object->designation = $_POST['referenceS'];
 $object->idfmm = $_POST['choixDesignation'];
 $object->etat = $_POST['etatSelect'];
 $object->garantie = $_POST['garantieSelect'];
 $object->comClient = $_POST['comClientLigne'];
 $object->quantite = $_POST['quantiteLigne'];
 $object->prix = floatVal($_POST['prixLigne']);
 $object->extension = $_POST['extensionGarantie'];
 $object->prixGarantie = floatval($_POST['prixGarantie']);
 $object->cmdl__ordre = '';
 $Cmd->insertLine($object);
 $_POST['recherche-fiche'] = 'id-fiche';
 $_POST['rechercheF'] = $idDuPost;
 
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
        
          case 'id-fiche';
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

$visuelFiche = null;
if (empty($devisList) && strlen($_POST['rechercheF']) == 7 ) 
{
  $alertFiche = $Cmd->GetById($_POST['rechercheF']);
  if (!empty($alertFiche)) 
  {
    switch ($alertFiche->devis__etat) 
    {
      case 'VLD':
        $visuelFiche = ' déja facturée';
        break;

      case 'ARH':
          $visuelFiche = ' une Garantie ou un reliquat';
          break;

      case 'NFT':
            $visuelFiche = 'archivée';
            break;

      case 'PBL':
            $visuelFiche = 'a supprimer ( le signaler à François ) ';
            break;
            
      case 'PLL':
            $visuelFiche = 'a supprimer ( le signaler à François ) ';
              break;
    }
  
  }
}

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
'keywordList' => $keywordList,
'tvaList' => $tvaList,
'prestaList'=> $prestaList ,
'articleList' => $articleTypeList,
'garantiesList' => $garantiesList ,
'etatList' => $etatList , 
'alertFacture' => $alertFacture , 
'alertArchive' => $alertArchive , 
'visuelFiche' => $visuelFiche
]);