<?php

namespace GDTA\Core\Data\Business;

use GDTA\Core\Data\Database\DatabaseObject;
use GDTA\Core\Data\Database\DatabaseHandler;
use GDTA\Core\Model\Traits\DatabaseObjectUidKeyField;
use GDTA\Core\Model\Traits\DatabaseObjectCreationUpdateFields;

/**
 * LinkObjectMeta - Classe représentant un attribut/metadonnées sur lien entre objet Business
 */
class LinkObjectMeta extends DatabaseObject
{
  use DatabaseObjectUidKeyField, DatabaseObjectCreationUpdateFields;

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
    parent::__construct('a1000_links_meta',$oDB);
    $this->setObjectKeyDBFields();
    $this->setCreationUpdateDBFields();
    $this->initSpecificObjectFieldsDefintion();

    if (!\is_null($iUid)) {
      $this->loadObjectFromCondition(["uid = $iUid"]);
    }
  }//end __construct()

  protected function initSpecificObjectFieldsDefintion()
  {
      // Other fields definition ...
      $this->addNewKeyFieldDefinition('LinkMetaDefinitionUid','uid_lnkd');
      $this->addNewFieldDefinition('LinkUid','uid_lnk');
      $this->addNewFieldDefinition('Value','value');
      $this->addNewFieldDefinition('isDeleted','deleted');

  }//end initSpecificObjectFieldsDefintion()

}//end class

?>
