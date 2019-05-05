<?php

namespace GDTA\Core\Data\Business;

use GDTA\Core\Data\Database\DatabaseObject;
use GDTA\Core\Data\Database\DatabaseHandler;
use GDTA\Core\Model\Traits\DatabaseObjectUidKeyField;
use GDTA\Core\Model\Traits\DatabaseObjectVersionFields;

/**
 * InternalObject - Classe générique d'objet métier.
 */
class InternalObject extends DatabaseObject
{
  use DatabaseObjectUidKeyField, DatabaseObjectVersionFields;

  //////////////////////////////////////////////////////////////////////////////
  // Constructeurs par défaut
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Constructeur par défaut
   * @param int             (Optionel) $iUid Uid interne de l'objet
   * @param DatabaseHandler $oDB  Database Handler
   */
  function __construct($iUid=null,DatabaseHandler $oDB=null)
  {
    parent::__construct('a1000_objects',$oDB);
    $this->setObjectKeyDBFields();
    $this->initSpecificObjectFieldsDefintion();

    if (!\is_null($iUid)) {
      $this->loadObjectFromCondition(["uid = $iUid"]);
    }
  }//end __construct()

  protected function initSpecificObjectFieldsDefintion()
  {
      // Other fields definition ...
      $this->addNewKeyFieldDefinition('ObjectDefinitionUid','uid_objd');
      $this->addNewKeyFieldDefinition('ModelUid','uid_mdl');
      $this->addNewFieldDefinition('Bid','bid_obj');
      $this->addNewFieldDefinition('ObjectUid','uid_obj');
      $this->addNewFieldDefinition('Version','ver_obj');
      $this->addNewFieldDefinition('Revision','rev_obj');
  }//end initSpecificObjectFieldsDefintion()


}//end class

?>
