<?php

namespace GDTA\Core\Data\Business;

use GDTA\Core\Data\Database\DatabaseObject;
use GDTA\Core\Data\Database\DatabaseHandler;
use GDTA\Core\Model\Traits\DatabaseObjectUidKeyField;
use GDTA\Core\Model\Traits\DatabaseObjectVersionFields;

/**
 * InternalObject - Classe générique d'objet métier.
 */
class GenericSimpleObject extends DatabaseObject
{

  //////////////////////////////////////////////////////////////////////////////
  // Constructeurs par défaut
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Constructeur par défaut
   * @param int             (Optionel) $iUid Uid interne de l'objet
   * @param DatabaseHandler $oDB  Database Handler
   */
  function __construct($sTablename,DatabaseHandler $oDB=null)
  {
    parent::__construct($sTablename,$oDB);
  }//end __construct()

  protected function initSpecificObjectFieldsDefintion()
  {
      // Other fields definition ...

  }//end initSpecificObjectFieldsDefintion()

  public function getAllRows()
  {
    //print_r("on passe");
    $laResult = $this->searchObjectsWithoutSelectFields('1 = 1');
    //print_r($laResult);
    return $laResult;
  }//end getAllRows()


}//end class

?>
