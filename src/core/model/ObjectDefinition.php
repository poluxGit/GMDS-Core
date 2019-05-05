<?php

namespace GDTA\Core\Model;

use GDTA\Core\Data\Database\DatabaseObject;

/**
 * ObjectDefinition class
 *
 */
class ObjectDefinition extends DatabaseObject
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
    parent::__construct('a0000_def_objects');
    $this->setObjectKeyDBFields();
    $this->setCommonDBfields();
    $this->setCreationUpdateDBFields();
    $this->initSpecificObjectFieldsDefintion();

    if (!\is_null($iUid)) {
      $this->loadObjectFromCondition(["uid = $iUid"]);
    }
  }//end __construct()

  protected function initSpecificObjectFieldsDefintion()
  {
      // Other fields definition ...
      $this->addNewKeyFieldDefinition('ModelUid','uid_mdl');
      $this->addNewFieldDefinition('ObjectBIDPattern','bid_obj_pattern');
      $this->addNewFieldDefinition('TableName','table_name');
      $this->addNewFieldDefinition('ViewName','view_name');
      $this->addNewFieldDefinition('ViewSQLDefinition','view_selectSQL');
      $this->addNewFieldDefinition('isVersionable','isVersionable');
      $this->addNewFieldDefinition('isSystem','isSystem');
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
      $this->getFieldValue('ViewName'),
      $this->getFieldValue('ViewSQLDefinition')
    );
  }//end getSQLViewCreationOrder()

  /**
   * Retourne la defintion d'objet depuis son BID et son model UID (Optionnel)
   *
   * @param  [type] $sObjectDefinitionBid [description]
   * @param  [type] $sModelUid            [description]
   * @return [type]                       [description]
   */
  public static function getObjectDefinitionFromBid($sObjectDefinitionBid,$sModelUid=null)
  {
    $loObjectDef = new ObjectDefinition();
    $lsSQLCondition = "bid = '$sObjectDefinitionBid'";
    if (!\is_null($sModelUid)) {
      $lsSQLCondition .= " AND uid_mdl = $sModelUid";
    }
    $laDataResult = $loObjectDef->searchObjects([$lsSQLCondition]);

    if(count($laDataResult) == 1 && \array_key_exists('Uid',$laDataResult[0])) {
      $loObjectDef = new ObjectDefinition($laDataResult[0]['Uid']);
    } else {
      $loObjectDef = null;
    }
    return $loObjectDef;
  }//end getObjectDefinitionFromBid()

  /**
   * Retourne la defintion d'objet depuis son nom de table
   *
   * @param  string $sTablename [description]
   * @param  string $sModelUid  [description]
   * @return ObjectDefinition   [description]
   */
  public static function getObjectDefinitionFromTablename($sTablename,$sModelUid=null)
  {
    $loObjectDef = new ObjectDefinition();
    $lsSQLCondition = " table_name = '$sTablename' ";
    if (!\is_null($sModelUid)) {
      $lsSQLCondition .= " AND uid_mdl = $sModelUid";
    }
    $laDataResult = $loObjectDef->searchObjects($lsSQLCondition);

    if(count($laDataResult) == 1 && \array_key_exists('Uid',$laDataResult[0])) {
      $loObjectDef = new ObjectDefinition($laDataResult[0]['Uid']);
    } else {
      $loObjectDef = null;
    }
    return $loObjectDef;
  }//end getObjectDefinitionFromTablename()

  /**
   * Retourne un tableau d'objet de définition de meta sur la defintion d'objet courant
   *
   * @return array(ObjectMetaDefintion) Tableau de défintion de meta d'objets
   */
  public function getObjectMetaDefArray() {
    $laObjMetaDef = [];

    $laTmp = ObjectMetaDefinition::getObjectMetaDefinitionByObjectDefinition(
      $this->getUid()
    );

    foreach ($laTmp as $value) {
      array_push($laObjMetaDef,new ObjectMetaDefinition($value));
    }

    return $laObjMetaDef;
  }//end getObjectMetaDefArray()

}//end class

?>
