<?php

namespace GMDS\Core\Data\Database;

use GMDS\Core\Exception\CoreException ;
use GMDS\Core\Exception\DatabaseHandlerException;
use GMDS\Core\Tools\Logger;


/**
 * DatabaseHandler - Class de gestion de l'interface avec une base de données.
 */
class DatabaseHandler
{
  //////////////////////////////////////////////////////////////////////////////
  // Attributs privés
  //////////////////////////////////////////////////////////////////////////////
  private $_sInternalName = null;
  private $_sHost     = null;
  private $_sDBType   = null;
  private $_iDBPort   = 3306;
  private $_sDBSchema = null;
  private $_sDSN      = null;
  private $_oPDDBObj  = null;

  /**
   * Logger de la classe
   *
   * @static
   * @var Logger
   */
  private static $_oLogger = null;

  //////////////////////////////////////////////////////////////////////////////
  // Constructeurs par défaut
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Constructeur depuis tous les paramètres
   *
   * @param string $sInternalName   Nom interne de la connexion
   * @param string $sDBType         Type de la connection du DSN.
   * @param string $sDBSchema       Schéma cible.
   * @param string $sDBHost         Hote cible.
   * @param string $iDBPort         Port cible.
   * @param string $sUserLogin      Login du compte utilisateur
   * @param string $sUserPassword   MDP du compte utilisateur
   */
  function __construct($sInternalName,$sDBType,$sDBSchema,$sDBHost,$iDBPort,$sUserLogin,$sUserPassword)
  {
    $this->_sInternalName = $sInternalName;
    $this->_sHost = $sDBHost;
    $this->_sDBType = $sDBType;
    $this->_iDBPort = $iDBPort;
    $this->_sDBSchema = $sDBSchema;

    // Calcul du DSN !
    $sDsn = self::buildDatabaseDSN($sDBType,$sDBSchema,$sDBHost,$iDBPort);
    $this->_sDSN = $sDsn;
    $this->_oPDODBObj = new \PDO(
      $this->_sDSN,
      $sUserLogin,
      $sUserPassword
    );
  }//end __construct()

  //////////////////////////////////////////////////////////////////////////////
  // Accesseurs
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Retourne l'Objet PDO de la connection établie
   *
   * @return \PDO
   */
  public function getPDOObject()
  {
    return $this->_oPDODBObj;
  }//end getPDOObject()

  public function getSchemaName(){
    return $this->_sDBSchema;
  }

  //////////////////////////////////////////////////////////////////////////////
  // Execution de requetes
  //////////////////////////////////////////////////////////////////////////////

  /**
   * Execute une requete SQL de type INSERT, UPDATE, DELETE
   *
   * @throw new GDTA\Core\Exception\CoreException ;("execQuery","Une erreur est survenue durant l'execution de la requete SQL : %s","SELECT 1 FROM;");
   * @param  string $pSQLQuery  Définition SQL de la requete à executer
   * @return int            Nombre d'enregistrements impactés
   */
  public function execQuery($pSQLQuery)
  {
    $liLastUid = null;
    $liNbRows = 0;

    self::$_oLogger->addLog('execQuery | SQL : %s.',[$pSQLQuery]);

    $loPDOStmt = $this->getPDOObject()->prepare($pSQLQuery);
    $liNbRows = $loPDOStmt->execute();
    self::$_oLogger->addLog('execQuery | NbRows : %s.',[strval($liNbRows)]);

    if ($loPDOStmt->errorCode() != '0000') {
      throw new DatabaseHandlerException(
        "execQuery",
        "$pSQLQuery",
        $loPDOStmt->errorInfo()[2]
      );
    }

    $liLastUid = $this->getPDOObject()->lastInsertId();
    self::$_oLogger->addLog('execQuery | LastUid : %s.',[strval($liLastUid)]);

    return ($liNbRows==1&&!\is_null($liLastUid)?$liLastUid:$liNbRows);
  }//end execQuery()

  /**
   * Execute un script SQL
   *
   * @throw new GDTA\Core\Exception\CoreException ;("execQuery","Une erreur est survenue durant l'execution de la requete SQL : %s","SELECT 1 FROM;");
   * @param  string $pSQLQuery  Définition SQL de la requete à executer
   * @return int            Nombre d'enregistrements impactés
   */
  public function execScript($pSQLQuery)
  {
    self::$_oLogger->addLog('execScript | SQL : %s.',[$pSQLQuery]);

    $loPDOStmt = $this->getPDOObject()->prepare($pSQLQuery);
    $loPDOStmt->execute();

    if ($loPDOStmt->errorCode() != '0000') {
      throw new DatabaseHandlerException(
        "execScript",
        "$pSQLQuery",
        $loPDOStmt->errorInfo()[2]
      );
    }

  }//end execScript()

  /**
   * Execute une requete SQL de type SELECT
   *
   * @throw new GDTA\Core\Exception\CoreException ;("query","Une erreur est survenue durant l'execution de la requete SQL : %s","SELECT 1 FROM;");
   * @param  string $pSQLQuery    Définition SQL de la requete SELECT à executer
   * @return PDOStatement                Nombre d'enregistrements impactés
   */
  public function queryAndFetch($pSQLQuery)
  {
    $laResult = null;
    try {

      self::$_oLogger->addLog('queryAndFetch | SQL : %s.',[$pSQLQuery]);
      self::$_oLogger->flushLogsToFile();
      $sth = $this->getPDOObject()->prepare($pSQLQuery);
      $sth->execute();
      $laResult = $sth->fetchAll(\PDO::FETCH_ASSOC);
      self::$_oLogger->addLog('queryAndFetch | Nb Results : %s.',[strval(count($laResult))]);

    } catch (\Exception $e) {
      throw new CoreException (
        "queryAndFetch",
        "Une erreur est survenue lors de l'interrogation SQL suivante : %s",
        [$pSQLQuery]
      );
    }
    return $laResult;
  }//end query()


  //////////////////////////////////////////////////////////////////////////////
  // Methodes statiques
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Retourne une chaine au format DSN
   *
   * @static
   * @example "mysql:dbname=GOM;host=localhost;port=3336"
   *
   * @param string $psDBType    Type de la connection du DSN.
   * @param string $psDBSchema  Schéma cible.
   * @param string $psDBHost    Hote cible.
   * @param string $piDBPort    Port cible.
   */
  static function buildDatabaseDSN($psDBType,$psDBSchema,$psDBHost,$piDBPort)
  {
    $lsDSN = sprintf(
        "%s:dbname=%s;host=%s;port=%s",
        $psDBType,
        $psDBSchema,
        $psDBHost,
        $piDBPort
      );
    return $lsDSN;
  }//end buildDatabaseDSN()

  /**
   * Définie le Logger de la classe
   *
   * @param Logger $oLogger [description]
   */
  public static function setLogger(Logger $oLogger)
  {
    self::$_oLogger = $oLogger;
  }//end setLogger()

}//end class

?>
