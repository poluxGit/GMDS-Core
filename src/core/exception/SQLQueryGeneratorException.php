<?php

namespace GMDS\Core\Exception;

/**
 * SQLQueryGeneratorException
 *
 */
class SQLQueryGeneratorException extends \Exception
{
  public function __construct($sMessage,$aParams){
  
    $lsMessage = 'SQL Generator Exception: ';
    if (\is_null($aParams)) {
      $lsMessage .= $sMessage;
    } else {
      $lsMessage .= vsprintf($sMessage,$aParams);
    }
    parent::__construct($lsMessage);

  }//end __construct()

}//end class

?>
