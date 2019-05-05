<?php

namespace GDTA\Core\Exception;

/**
 * BusinessObjectException - Exception sur objet Métier
 *
 */
class LinkObjectException extends \Exception
{
  /**
   * Constructeur par défaut
   *
   * @param string  $sMessage             Message de l'exception.
   * @param array   $sMessageParameters   Valeur Paramétrées du message.
   */
  public function __construct($sMessage,$sMessageParameters=null)
  {
    $lsMessage = sprintf("Link Object Exception - %s.",$sMessage);
    if (\is_null($sMessageParameters)) {
      $lsMessage .= $sMessage;
    } else {
      $lsMessage .= vsprintf($sMessage,$sMessageParameters);
    }
    parent::__construct($lsMessage);
  }//end __construct()

}//end class

?>
