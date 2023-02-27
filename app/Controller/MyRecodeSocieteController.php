<?php

namespace App\Controller;
require_once  '././vendor/autoload.php';
use App\Controller\BasicController;
use App\Tables\Keyword;
use App\Tables\User;
use DateTime;
use App\Tables\Tickets;
use App\Apiservice\ApiTest;
use App\Tables\Article;
use App\Database;


class MyRecodeSocieteController extends BasicController {
    public static function displayList(){
        self::init();
        self::security();
        $Api = new ApiTest();
        if (empty($_SESSION['user']->refresh_token)) {
            $token = $Api->login($_SESSION['user']->email , 'test');
            if ($token['code'] != 200) {
                echo 'Connexion LOGIN à L API IMPOSSIBLE';
                die();
            }
            $_SESSION['user']->refresh_token = $token['data']['refresh_token'] ; 
            $token =  $token['data']['token'];
        }else{
            $refresh = $Api->refresh($_SESSION['user']->refresh_token);
            if ( $refresh['code'] != 200) {
                echo 'Rafraichissemnt de jeton API IMPOSSIBLE';
                die();
            }
            $token =  $refresh['token']['token'];
        }

        $list = $Api->PostListClient($token,false)['data'];
       
        return self::$twig->render(
            'display_societe_myrecode_list.html.twig',[
                'user' => $_SESSION['user'] , 
                'list' => $list
            ]
        );
    }


    public static function display()
    {
        self::init();
        self::security();
        $Database = new Database('devis');
        $Database->DbConnect();
        $Api = new ApiTest();
        $Article = new Article($Database);

        if ($_SESSION['user']->user__cmd_acces < 10 ){
            header('location: noAccess');
            die();
        }

        if (empty($_SESSION['user']->refresh_token)) {
            $token = $Api->login($_SESSION['user']->email, 'test');
            if ($token['code'] != 200) {
                echo 'Connexion LOGIN à L API IMPOSSIBLE';
                die();
            }
            $_SESSION['user']->refresh_token = $token['data']['refresh_token'];
            $token =  $token['data']['token'];
        } else {
            $refresh = $Api->refresh($_SESSION['user']->refresh_token);
            if ($refresh['code'] != 200) {
                echo 'Rafraichissemnt de jeton API IMPOSSIBLE';
                die();
            }
            $token =  $refresh['token']['token'];
        }

        if (empty($_GET['cli__id'])) {header('location SocieteMyRecode');die();}

        $client = $Api->PostListClient($token, $_GET['cli__id'])['data'];

        if (empty($client)){header('location SocieteMyRecode');die();}

        $pn_list = $Article->getModelsMyRecode();

        $body  = [
            'secret' => "heAzqxwcrTTTuyzegva^5646478§§uifzi77..!yegezytaa9143ww98314528" , 
            'shop_avendre' => true
        ];

        $list_avendre = $Api->getShopVendre($token,$body)['data'];
        
        header("Access-Control-Allow-Origin: *");
        return self::$twig->render(
            'display_societe_myrecode.html.twig',
            [
                'user' => $_SESSION['user'],
                'client' => $client , 
                'pn_list' => $pn_list , 
                'avendre_list' => $list_avendre
            ]
        );
    }
    
}