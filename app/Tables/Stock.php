<?php

namespace App\Tables;
use App\Tables\Table;
use App\Database;
use PDO;


class Stock extends Table 
{
  public Database $Db;

  //constructeur
  public function __construct($db) 
  {
    $this->Db = $db;
  }

  public function insert_attr_pn($pn , $aap__cle , $aap__valeur ) : bool
  {
    $request = $this->Db->Pdo->prepare('INSERT INTO art_attribut_pn (aap__pn, aap__cle , aap__valeur )
        VALUES (:aap__pn , :aap__cle , :aap__valeur)');
    $request->bindValue(":aap__pn", $pn);
    $request->bindValue(":aap__cle", $aap__cle);
    $request->bindValue(":aap__valeur", $aap__valeur);
    $request->execute();
    return true;
  }


  public function get_famille_forms($famil) : array 
  {
    $request = $this->Db->Pdo->query('SELECT   
    c.aac__famille , c.aac__cle , c.aac__ordre , c.aac__cle_txt, c.aac__option 
    FROM art_attribut_cle as c
    WHERE aac__famille = "'. $famil .'"
    ORDER BY c.aac__ordre DESC LIMIT 1500 ');
    $data = $request->fetchAll(PDO::FETCH_OBJ);

    foreach ($data as $clef) 
    {
      $request = $this->Db->Pdo->query('SELECT   
      v.aav__cle , v.aav__valeur, v.aav__ordre , v.aav__valeur_txt
      FROM art_attribut_valeur as v
      WHERE v.aav__cle = "'. $clef->aac__cle .'"
      ORDER BY v.aav__ordre DESC LIMIT 1500 ');
      $responses = $request->fetchAll(PDO::FETCH_OBJ);
      $clef->key_responses = $responses;
    }
    return $data;
  }


  public function get_attribut($pn) : array 
  {
    $request = $this->Db->Pdo->query('SELECT   
    a.aap__pn , a.aap__cle , a.aap__valeur
    FROM art_attribut_pn as a
    LEFT JOIN art_attribut_valeur as v ON a.aap__cle =  v.aav__valeur
    WHERE a.aap__pn = '. $pn  .'
    ORDER BY c.aac__ordre DESC LIMIT 1500 ');
    $data = $request->fetchAll(PDO::FETCH_OBJ);
    return $data;
  }



 


}