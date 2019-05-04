<?php

namespace GMDS\Core\Tools;

/**
 * Logger class
 *
 */
class Logger
{
  /**
   * Fichier de sortie du Logger
   *
   * @var string
   */
  private $_loggerFilepath = null;

  /**
   * Handler du fichier de sortie du Logger
   *
   * @var object
   */
  private $_LoggerFileHandler = null;

  /**
   * Format des enregistrements du logger
   *
   * @var string
   * @example TODO Date('dd-mm-YY') - %LoggerMessage% ... TODO
   */
  private $_loggerRowPattern = null;

  /**
   * Tableau interne des enregistrements du logger
   *
   * @var array
   */
  private $_loggerRows = [];

  /**
   * Constructeur
   *
   * @param string $sLoggerFilepath   Fichier du logger
   * @param string $sRowPattern       Pattern des enregistrements
   */
  public function __construct($sLoggerFilepath, $sRowPattern)
  {
    $this->_loggerFilepath      = $sLoggerFilepath;
    $this->_LoggerFileHandler   = \fopen($sLoggerFilepath,'a+');
    $this->_loggerRowPattern    = $sRowPattern;
  }//end __construct()


  function __destruct()
  {
    $this->flushLogsToFile();
    if (!\is_null($this->_LoggerFileHandler)) {
      \fclose($this->_LoggerFileHandler);
    }
  }//end __destruct()

  /**
   * Retourne le fichier du Logger
   *
   * @return string Fichier
   */
  public function getFilepath()
  {
    return $this->_loggerFilepath;
  }//end getFilepath()

  /**
   * Ajout Message au Logger
   *
   * @param string $sMessage Message à logger
   */
  public function addLog($sMessage,$aMsgParams=[])
  {
    $laLog = [date('c'),\vsprintf($sMessage,$aMsgParams)];
    \array_push($this->_loggerRows,$laLog);
  }//end addLog()

  /**
   * Ajout d'un séparateur
   *
   * @param string $sMessage Message à logger
   */
  public function addLogSep()
  {
    $laLog = [
      date('c'),
      "***********************************************************************".
      "***********************************************************************"
    ];
    \array_push($this->_loggerRows,$laLog);
  }//end addLog()

  /**
   * Vide le tableau interne en réalisant l'écriture des enregistrements
   * dans le fichier de sortie
   *
   */
  public function flushLogsToFile():void
  {
    while(count($this->_loggerRows)>0){
      $laRow = \array_shift($this->_loggerRows);
      $lsLogFinal = '#'.implode(' | ',$laRow)."\n";
      \fwrite($this->_LoggerFileHandler, $lsLogFinal);
    }
  }//end flushLogsToFile()

}//end class

?>
