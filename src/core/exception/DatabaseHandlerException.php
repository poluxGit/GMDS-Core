<?php

namespace GDTA\Core\Exception;

/**
 * DatabaseHandlerException - Exception applicative.
 *
 */
class DatabaseHandlerException extends \Exception
{
  /**
   * Constructeur par défaut
   * @param string  $sMethodName          Nom de la méthode ou activité.
   * @param string  $sMessage             Message d'erreur (syntaxe : sprintf).
   * @param array   $sMessageParameters   Valeurs paramétrées du message.
   */
  public function __construct($sMethodName,$sSQL,$sPDOMessage)
  {
    $lsMessage = sprintf("DB Handler Exception - '%s' \n-> MsgPDO : '%s' \n-> SQL : '%s'\n",$sMethodName,$sPDOMessage,$sSQL);
    parent::__construct($lsMessage);
  }//end __construct()

}//end class

?>
