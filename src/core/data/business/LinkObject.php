<?php

namespace GDTA\Core\Data\Business;

use GDTA\Core\Data\Database\DatabaseObject;
use GDTA\Core\Data\Database\DatabaseHandler;
use GDTA\Core\Model\Traits\DatabaseObjectUidKeyField;
use GDTA\Core\Model\Traits\DatabaseObjectCreationUpdateFields;

/**
 * LinkObject - Classe générique d'objet métier.
 */
class LinkObject extends DatabaseObject
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
    parent::__construct('a1000_links',$oDB);
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
      $this->addNewFieldDefinition('LinkDefUid','uid_lnkd');
      $this->addNewFieldDefinition('Bid','bid_lnk');
      $this->addNewFieldDefinition('ObjectSrcUid','uid_obj_source');
      $this->addNewFieldDefinition('ObjectTrgUid','uid_obj_target');
  }//end initSpecificObjectFieldsDefintion()

  /**
   * Définie la valeur d'une meta sur le lien entre objet
   *
   * @param int $iUidLnkMetaDef   UID de la définition de meta sur lien
   * @param int $xValue           Valeur
   */
  public function setLinkMetaValue($iUidLnkMetaDef,$xValue) {

    $lLnkMetaDef = new LinkMetaDefinition($iUidLnkMetaDef);
    // TODO Générer le cas pas de retour !
    $laValueTmp = [
      "value"     => $xValue,
      "dataType"  => $lLnkMetaDef->getFieldValue('LinkMetaType'),
      "dataPattern"  => $lLnkMetaDef->getFieldValue('LinkMetaPattern')
    ];

    $liLnkUid = $this->getUid();
    $loBusinessMetaLnk = BusinessDataFactory::getBusinessMetaLinkFromLinkAndMetaDef(
      $liLnkUid,
      $lLnkMetaDef->getUid()
    );
    // TODO Générer le cas pas de retour !
    $loBusinessMetaLnk->setFieldValue('Value',json_encode($laValueTmp));
    $loBusinessMetaLnk->recordObject();

  }//end setLinkMetaValue()

}//end class
?>
