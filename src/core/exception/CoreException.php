<?php

namespace GMDS\Core\Exception;

/**
 * ApplicationException - Exception applicative.
 *
 */
class CoreException extends \Exception
{
  /**
   * Constructeur par défaut
   *
   * @param string  $sMethodName          Nom de la méthode ou activité.
   * @param string  $sMessage             Message d'erreur (syntaxe : sprintf).
   * @param array   $sMessageParameters   Valeurs paramétrées du message.
   */
  public function __construct($sMethodName,$sMessage,$sMessageParameters=null)
  {
    $lsMessage = sprintf("Exception durant '%s' - ",$sMethodName);
    if (\is_null($sMessageParameters)) {
      $lsMessage .= $sMessage;
    } else {
      $lsMessage .= vsprintf($sMessage,$sMessageParameters);
    }
    parent::__construct($lsMessage);
  }//end __construct()

}//end class

?>
