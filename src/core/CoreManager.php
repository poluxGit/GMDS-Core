<?php
namespace GMDS\Core;

// Uses ...
// *****************************************************************************
use GMDS\Core\DatabaseManager;
use GMDS\Core\ModelManager;

use GMDS\Core\Data\Database\DatabaseHandler;
use GMDS\Core\Tools\Logger;

use GMDS\Core\Exception\CoreException;
// *****************************************************************************

/**
 * CoreManager
 *
 * Classe statique principale de coordination du framework.
 *
 * - Chargement des paramètres 'applicatifs'
 * - Initialisation des Handlers DB, Logs ...
 *
 * @static
 */
class CoreManager
{
  // ***************************************************************************
  // Private Attributes
  // ***************************************************************************
  private static $_oLogger      = null;
  private static $_oDBLogger    = null;
  private static $_sLogFilepath = null;
  private static $_sSystemName = null;

  /**
   * Core initialization
   *
   * Instanciaition des logs ...
   *
   */
  public static function initialize()
  {
    self::$_sLogFilepath = realpath(dirname(__FILE__).'./../../logs');
  }//end initializeFramework()

  // ***************************************************************************
  // Accessors related static methods
  // ***************************************************************************
  /**
   * Set System name
   * @param string $sSystemName   System Name to set
   */
  public static function setSystemName($sSystemName){
    self::$_sSystemName = $sSystemName;
  }//end setSystemName()

  /**
   * Get System name
   * @return string   System Name set
   */
  public static function getSystemName()
  {
    return self::$_sSystemName;
  }//end getSystemName()

  // ***************************************************************************
  // DB related static methods
  // ***************************************************************************
  /**
   * Return  application' DB connection from his name
   *
   * @param string $sConnectionName   Nom de la connexion.
   * @return GMDS\Core\Data\DatabaseHandler
   */
  public static function getDatabaseConnection($sConnectionName)
  {
    return DatabaseManager::getConnectionByName($sConnectionName);
  }//end getDatabaseConnection()

  /**
   * Return default application' DB connection
   *
   * @return GMDS\Core\Data\DatabaseHandler
   */
  public static function getDefaultDatabaseConnection():DatabaseHandler
  {
    return DatabaseManager::getDefaultConnection();
  }//end getDefaultDatabaseConnection()

  // ***************************************************************************
  // Application settings related static methods
  // ***************************************************************************
  /**
   * Load system settings from a JSON file
   *
   * @param  string $sSettingsJSONFilepath    Path of file to load.
   */
  public static function loadSystemSettingsFromJSONFile($sSettingsJSONFilepath)
  {
    // local vars!
    $lbFileValid = true;
    $lbFirst = true;

    // file exists ?
    if (!\file_exists($sSettingsJSONFilepath)) {
      throw new CoreException(
        "loadSystemSettingsFromJSONFile",
        "Fichier des parametres applicatifs non trouve : %s",
        [$sSettingsJSONFilepath]
      );
    }
    // Reading settings ...
    $loSettings = \json_decode(\file_get_contents($sSettingsJSONFilepath));

    if (\json_last_error() != JSON_ERROR_NONE) {
      throw new CoreException(
        "loadSystemSettingsFromJSONFile",
        "Décodage JSON échoué : '%s' (fichier:'%s').",
        [\json_last_error_msg(),$sSettingsJSONFilepath]
      );
    }
    // Mandatory properties existances checks!
    $laPropsToCheck = [
      'system_name',
      'databases',
      'vaults'
    ];

    foreach ($laPropsToCheck as $lsPropToCheck) {
      if (!\property_exists($loSettings,$lsPropToCheck)) {
        throw new CoreException(
          "loadSystemSettingsFromJSONFile",
          "Paramètre obligatoire '%s' non défini (fichier:'%s').",
          [$lsPropToCheck,$sSettingsJSONFilepath]
        );
      }
    }

    // Properties definition !
    self::setSystemName($loSettings->system_name);

    // Databases initialisation !
    if (is_array($loSettings->databases) && sizeof($loSettings->databases) > 0) {
      $laPropsToCheck = [
        'id',
        'dbtype',
        'host',
        'port',
        'schema',
        'user',
        'pass'
      ];

      // For each DB connection !
      foreach ($loSettings->databases as $loDBConnection) {
        // Properties check !
        foreach ($laPropsToCheck as $lsPropToCheck) {
          if (!\property_exists($loDBConnection,$lsPropToCheck)) {
            throw new CoreException(
              "loadSystemSettingsFromJSONFile",
              "Paramètre obligatoire de la connection DB '%s' non défini (fichier:'%s').",
              [$lsPropToCheck,$sSettingsJSONFilepath]
            );
          }
        }

        // DatabaseHandler instanciation!
        $loDBObj = new DatabaseHandler(
          $loDBConnection->id,
          $loDBConnection->dbtype,
          $loDBConnection->schema,
          $loDBConnection->host,
          intval($loDBConnection->port),
          $loDBConnection->user,
          $loDBConnection->pass
        );

        DatabaseManager::addConnection(
          $loDBConnection->id,
          $loDBObj,
          $lbFirst
        );

        if ($lbFirst===true) {
          $lbFirst = false;
        }
      }
    }
    // TODO DEV - Vaults init
  }//end loadSystemSettingsFromJSONFile()

  // ***************************************************************************
  // Logs management related static methods
  // ***************************************************************************
  /**
   * Log Application Event
   *
   * @param  string $sMessage [description]
   * @return [type]           [description]
   */
  public static function logApplicationEvent($sMessage):void
  {
    self::$_oLogger->addLog('ApplicationEvent : %s',[$sMessage]);
  }//end logApplicationEvent()

  /**
   * Définie le fichier destination du logger applicatif principal
   *
   * @param string $sLoggerFile [description]
   */
  public static function setApplicationLoggerFilepath($sLoggerFile)
  {
    $loLogger = new Logger($sLoggerFile,'test');
    self::$_oLogger = $loLogger;
  }//end setApplicationLogger()

  /**
   * Définie le fichier destination du logger applicatif principal
   *
   * @param string $sLoggerFile [description]
   */
  public static function setApplicationDatabaseLoggerFilepath($sLoggerFile)
  {
    $loLogger = new Logger($sLoggerFile,'test');
    self::$_oDBLogger = $loLogger;
    DatabaseHandler::setLogger(self::$_oDBLogger);
  }//end setApplicationDatabaseLoggerFilepath()

  /**
   * Retourne le Logger applicatif
   *
   * @return Logger Logger par défaut de l'application
   */
  public static function getApplicationDefaultLogger()
  {
    return self::$_oLogger;
  }//end getApplicationDefaultLogger()

  /**
   * Retourne un nom de fichier généré pour logger
   *
   * @return Logger Logger par défaut de l'application
   */
  public static function generateLoggerFileNameWithDate($lsFilenamePattern)
  {
    return self::$_sLogFilepath.'/'.date('Y_m_d').'-'.$lsFilenamePattern.'.log';
  }//end generateLoggerFileNameWithDate()

  // ***************************************************************************
  // Deployment of core Database schema
  // ***************************************************************************
  /**
   * Déploiement de la structure de la base de données Core
   *
   * Génération à la volée du script de génération de la base de données.
   *
   * @param  string $sDBInstanceName Nom interne de l'instance de base de données.
   */
  public static function deployDatabaseSchemaIntoDatabase($sDBInstanceName='maindb') {
    $loDBHandler = self::getDatabaseConnection($sDBInstanceName);
    // Connexion non existante ?
    if (\is_null($loDBHandler)) {
      throw New CoreException(
        'deployDatabaseSchemaIntoDatabase',
        "La connexion nommée '%s' n'existe pas.",
        [$sDBInstanceName]
      );
    } else {
      // SQL Script about deployment generation !
      $lsTargetSchemaName = $loDBHandler->getSchemaName();
      $loSmarty = new \Smarty();
      $loSmarty->assign('TargetSchema',$lsTargetSchemaName);

      $lsSQLFilePath = realpath(dirname(__FILE__).'./../../data/');
      $lsSQLScript = $loSmarty->fetch($lsSQLFilePath.'/core-db_create-script.sql.tpl');

      // DB execution !
      $loDBHandler->execScript($lsSQLScript);
    }
  }//end deployDatabaseSchemaIntoDatabase()

}//end class

?>
