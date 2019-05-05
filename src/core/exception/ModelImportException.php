<?php

namespace GDTA\Core\Exception;

/**
 * ModelImportException - Exception lors de l'import d'un modèle.
 *
 */
class ModelImportException extends \Exception
{
  /**
   * Constructeur par défaut
   * @param string  $sMethodName          Nom de la méthode ou activité.
   * @param string  $sMessage             Message d'erreur (syntaxe : sprintf).
   * @param array   $sMessageParameters   Valeurs paramétrées du message.
   */
  public function __construct($sImportStepPart,$sMessageErreur, $sSourceFile = null)
  {
    $lsMessage = sprintf(
      "\nModel Import Exception - Phase '%s' \n-> %s \n-> Source file : '%s'\n",
      $sImportStepPart,
      $sMessageErreur,
      $sSourceFile
    );
    parent::__construct($lsMessage);
  }//end __construct()

}//end class

?>
