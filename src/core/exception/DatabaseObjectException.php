<?php

namespace GMDS\Core\Exception;

/**
 * DatabaseObjectException - Exception applicative.
 *
 */
class DatabaseObjectException extends \Exception
{
  public function __construct($sMessage,$aParams){
    $lsMessage = '';
    if (\is_null($aParams)) {
      $lsMessage .= $sMessage;
    } else {
      $lsMessage .= vsprintf($sMessage,$aParams);
    }
    parent::__construct($lsMessage);

  }//end __construct()

}//end class

?>
