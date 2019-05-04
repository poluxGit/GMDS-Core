<?php
namespace GMDS\Core;

use GMDS\Core\Data;

/**
 * DatabaseManager - Classe de gestion des connections aux bases de données
 */
class DatabaseManager
{
  /**
   * Connexions aux bases de données
   *
   * @var array(GMDS\Core\Data\DatabaseHandler)
   */
  private static $_aConnections = [];
  /**
   * Connexion DB par défaut
   *
   * @var GMDS\Core\Data\DatabaseHandler
   */
  private static $_oDBDefaultConnection = null;

  /**
   * Ajoute une connexion DB
   *
   * @throws Exception  Si une connexion avec le même nom est définie
   *
   * @param string                          $sConnectionName  Nom Interne de la connection (Unique)
   * @param GMDS\Core\Data\DatabaseHandler  $oDBHanler        DB Handler
   * @param bool                            $bIsDefault       Connexion par défaut ? (default : false)
   */
  public static function addConnection($sConnectionName, $oDBHanler, $bIsDefault=false){
    if ($bIsDefault) {
      self::$_oDBDefaultConnection = $oDBHanler;
    }

    if (!\array_key_exists($sConnectionName,self::$_aConnections)) {
      self::$_aConnections[$sConnectionName] = $oDBHanler;
    } else {
      // TODO Exception applicative - TO DEV
      $lsMsg = sprintf(
        "Une connexion DB nommée '%s' existe déjà. Impossible de la redéfinir.",
        $sConnectionName
      );
      throw new \Exception($lsMsg);
    }
  }//end setConnection()

  /**
   * Retourne une connection PDO
   *
   * @param string $sConnectionName   Nom de la connection
   *
   * @return GMDS\Core\Data\DatabaseHandler
   */
  public static function getConnectionByName($sConnectionName){
    if (\array_key_exists($sConnectionName,self::$_aConnections)) {
      return self::$_aConnections[$sConnectionName];
    } else {
      return null;
      // // TODO Exception applicative - TO DEV
      // $lsMsg = sprintf(
      //   "La connexion DB nommée '%s' n'existe pas.",
      //   $sConnectionName
      // );
      // throw new \Exception($lsMsg);
    }
  }//end getConnectionByName()

  /**
   * Retourne la connexion par défaut.
   *
   * @return GMDS\Core\Data\DatabaseHandler   NULL si non définie
   */
  public static function getDefaultConnection(){
    return self::$_oDBDefaultConnection;
  }//end getDefaultConnection()

}//end class

?>
