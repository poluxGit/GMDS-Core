<?php

namespace GDTA\Core\Model;

use GDTA\Core\Data\Database\DatabaseObject;

/**
 * LinkDefinition class
 *
 */
class LinkDefinition extends DatabaseObject
{
  use \GDTA\Core\Model\Traits\DatabaseObjectUidKeyField;
  use \GDTA\Core\Model\Traits\DatabaseObjectCommonField;
  use \GDTA\Core\Model\Traits\DatabaseObjectCreationUpdateFields;

  /**
   * Constructeur
   *
   * @param int $iUid    Optional : Uid to load
   */
  function __construct($iUid=null)
  {
    parent::__construct('a0000_def_links');
    $this->setObjectKeyDBFields();
    $this->setCommonDBfields();
    $this->setCreationUpdateDBFields();
    $this->initSpecificObjectFieldsDefintion();

    if (!\is_null($iUid)) {
      $this->loadObjectFromCondition(["uid = $iUid"]);
    }
  }//end __construct()

  /**
   * Initialisation des champs spécifiques à l'objet
   *
   * @return [type] [description]
   */
  protected function initSpecificObjectFieldsDefintion()
  {
      // Other fields definition ...
      $this->addNewFieldDefinition('ObjDefUidSource','uid_objd_source');
      $this->addNewFieldDefinition('ModelObjDefUidSource','uid_mdl_source');
      $this->addNewFieldDefinition('ObjDefUidTarget','uid_objd_target');
      $this->addNewFieldDefinition('ModelObjDefUidTarget','uid_mdl_target');
      $this->addNewFieldDefinition('LinkType','link_type');
      $this->addNewFieldDefinition('LinkMandatory','link_mandatory');
      $this->addNewFieldDefinition('LinkMultiple','link_multiple');
      $this->addNewFieldDefinition('LinkSQLView','view_name');
      $this->addNewFieldDefinition('LinkSQLViewDefintion','view_select');
  }//end initSpecificObjectFieldsDefintion()

  /**
   * Retourne l'ordre SQL de définition des vues de la définition d'objet.
   *
   * @return string     Ordre SQL de création de la vue
   */
  public function getSQLViewCreationOrder(){

    $lsSQLOrder = "CREATE OR REPLACE VIEW %s AS %s;";

    return sprintf(
      $lsSQLOrder,
      $this->getFieldValue('LinkSQLView'),
      $this->getFieldValue('LinkSQLViewDefintion')
    );
  }//end getSQLViewCreationOrder()

  /**
   * Retourne un tableau des doonées des meta sur la définition de liens courante
   *
   * @return array  Tableau des UI de MLND
   */
  public function getAllLinkMetaDefinitionAsArray()
  {
    $loSearch = new LinkMetaDefinition();
    $laResult = $loSearch->searchObjects(
      sprintf(
        " uid_lnkd = %s ",
        $this->getUid()
      )
    );

    $laFinalResult = [];

    foreach ($laResult as $value) {
      \array_push($laFinalResult,$value['Uid']);
    }

    return $laFinalResult;
  }//end getAllLinkMetaDefinitionAsArray()

  /**
   * Retourne un tableau des objets meta sur la définition de liens courante
   *
   * @return array  Tableau des Objets MLND
   */
  public function getAllLinkMetaDefinitionAsObject()
  {
    $laMlnd = [];
    $laTmp = $this->getAllLinkMetaDefinitionAsArray();
    foreach ($laTmp as $value) {
      \array_push($laMlnd,new LinkMetaDefinition($value));
    }
    return $laMlnd;
  }//end getAllLinkMetaDefinitionAsObject()

}//end class

?>
