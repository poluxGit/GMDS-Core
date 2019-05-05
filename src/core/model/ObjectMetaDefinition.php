<?php

namespace GDTA\Core\Model;

use GDTA\Core\Data\Database\DatabaseObject;

/**
 * ObjectMetaDefinition class
 *
 */
class ObjectMetaDefinition extends DatabaseObject
{
  use \GDTA\Core\Model\Traits\DatabaseObjectUidKeyField;
  use \GDTA\Core\Model\Traits\DatabaseObjectCommonField;
  use \GDTA\Core\Model\Traits\DatabaseObjectCreationUpdateFields;
  // use GDTA\Core\Model\Trait\DatabaseObjectVersionFields;
  // use GDTA\Core\Model\Trait\DatabaseObjectCreationUpdateFields;

  /**
   * Constructeur
   *
   * @param int $iUid    Optional : Uid to load
   */
  function __construct($iUid=null)
  {
    parent::__construct('a0000_def_objects_meta');
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
      $this->addNewFieldDefinition('ObjectDefinitionUid','uid_objd');
      $this->addNewFieldDefinition('ModelUid','uid_mdl');
      $this->addNewFieldDefinition('MetaTitle','data_title');
      $this->addNewFieldDefinition('MetaType','data_type');
      $this->addNewFieldDefinition('MetaPattern','data_pattern');
      $this->addNewFieldDefinition('MetaOptions','data_options');
      $this->addNewFieldDefinition('MetaSQLName','data_sqlname');
      $this->addNewFieldDefinition('MetaSQLOrder','data_sqlorder');

  }//end initSpecificObjectFieldsDefintion()

  /**
   * Retourne un tableau d'identifiant de défintion de mata d'objet depuis une
   * définition d'objet
   *
   * @param  int  $iObjectDefinition  UID de la définition d'objet
   * @return array                    Tableau d'UID des meta définition trouvée.
   */
  public static function getObjectMetaDefinitionByObjectDefinition($iObjectDefinition):array
  {
    $laResult         = [];
    $laResultFinal    = [];
    $loObjMetaDefTmp  = new ObjectMetaDefinition();
    $loObjDef         = new ObjectDefinition($iObjectDefinition);

    $lsCondition = sprintf(
      " uid_objd = %s AND uid_mdl = %s ",
      $loObjDef->getUid(),
      $loObjDef->getFieldValue('ModelUid')
    );

    $laResult = $loObjMetaDefTmp->searchObjects($lsCondition);

     foreach ($laResult as $key => $value) {
       $laResultFinal[] = $value['Uid'];
     }
    return $laResultFinal;
  }//end getObjectMetaDefinitionForObjectDefintion()

  /**
   * Retourne un tableau d'identifiant de défintion de mata d'objet depuis une
   * définition d'objet
   *
   * @param  int  $iObjectDefinition  UID de la définition d'objet
   * @return array                    Tableau d'UID des meta définition trouvée.
   */
  public static function getObjectMetaDefinitionForObjectDefinition($iObjectDefinition):array
  {
    $laResult         = [];
    $laResultFinal    = [];
    $loObjMetaDefTmp  = new ObjectMetaDefinition();
    $loObjDef         = new ObjectDefinition($iObjectDefinition);

    $lsCondition = sprintf(
      " uid_objd = %s AND uid_mdl = %s ",
      $loObjDef->getUid(),
      $loObjDef->getFieldValue('ModelUid')
    );

    $laResult = $loObjMetaDefTmp->searchObjects($lsCondition);


    return $laResult;
  }//end getObjectMetaDefinitionForObjectDefintion()



}//end class

?>
