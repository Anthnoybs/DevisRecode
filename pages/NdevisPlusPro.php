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

//Connexion et Entités : 
$Database = new App\Database('devis');
$Client = new App\Tables\Client($Database);
$Keywords = new App\Tables\Keyword($Database);
$Contact = new App\Tables\Contact($Database);
$Article = new App\Tables\Article($Database);
$General = new App\Tables\General($Database);
$Cmd = new App\Tables\Cmd($Database);
$Database->DbConnect();

//listes  : 
$clientList = $Client->get_client_devis();
$modeleList = $Keywords->getModele();

//alertes et variables : 
$alertClient = false;
$contactList = null;
$contactLivraison = null;

//traitement de l'alerte : 
if(isset($_SESSION['alertV2'])) 
{
    switch ($_SESSION['alertV2']) 
    {
        case 'Aucun':
            $_SESSION['alertV2'] = '';
            $alertClient = 'Aucun Client selectionné';
            break;

        case 'Modif':
            $_SESSION['alertV2'] = '';
            $alertClient = 'Opération annulée.. impossible de modifier le devis sans client facturé';
            break;
           
        default:
            $_SESSION['alertV2'] = '';
            break;
    }
}


//traitement du duplicata :
if (!empty($_POST['DupliquerDevis'])) 
{
    $devis_source = $Cmd->GetById($_POST['DupliquerDevis']);
    $lignes_sources = $Cmd->devisLigne($_POST['DupliquerDevis']);
    $devis_duplicata = $Cmd->duplicate_devis($devis_source);

    foreach ($lignes_sources as $ligne) 
    {
       
        $ligne_nouveau_devis  = $Cmd->insert_ligne_duplicata($devis_duplicata , $ligne);
        $tableau_extensions =  $Cmd->xtenGarantie($ligne->devl__id);

        foreach ($tableau_extensions as $extension) 
        {
            $nouvelle_extension = $Cmd->duplicate_extension_garantie($extension ,$ligne_nouveau_devis ); 
        }
    }
    if (!empty($devis_source->cmd__mode_remise)) 
    {
        $General->updateAll('cmd', 1 , 'cmd__mode_remise' , 'cmd__id',$devis_source->devis__id );
    }
    if (!empty($devis_source->cmd__report_xtend)) 
    {
        $General->updateAll('cmd', 1 , 'cmd__report_xtend' , 'cmd__id',$devis_source->devis__id );
    }
    $_POST['modif'] = $devis_duplicata;
}

//traitement de la modification 
if(!empty($_POST['modif'])) 
{
    $modif = $Cmd->GetById($_POST['modif']);   
    //contact : 
    if (!empty($modif->client__id)) 
    {   
        $contactList = $Contact->getFromLiaison($modif->client__id);
    } 
    //livraison :
    if (!empty($modif->devis__id_client_livraison)) 
    {
        $contactLivraison = $Contact->getFromLiaison($modif->devis__id_client_livraison);
    } 
}else $modif =  false;

// Donnée transmise au template : 
echo $twig->render('NdevisPlusPro.twig',[
   'user'=>$_SESSION['user'],
   'clientList' => $clientList,
   'modeleList' => $modeleList,
   'alertClient' => $alertClient,
   'modif'=> $modif,
   'contactList' => $contactList,
   'contactLivraison' => $contactLivraison
]);;
