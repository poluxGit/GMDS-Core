<?php

namespace GMDS\Core\Model;

use GMDS\Core\Data\Database\DatabaseObject;

/**
 * Model class
 *
 */
class Model extends DatabaseObject
{
  use \GMDS\Core\Model\Traits\DatabaseObjectUidKeyField;
  use \GMDS\Core\Model\Traits\DatabaseObjectCreationUpdateFields;

  /**
   * Constructeur
   *
   * @param int $iUid    Optional : Uid to load
   */
  function __construct($iUid=null)
  {
    parent::__construct('a0000_def_models');

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
      $this->addNewFieldDefinition('Bid','bid');
      $this->addNewFieldDefinition('ModelVersion','version');
      $this->addNewFieldDefinition('ModelLabel','label');
      $this->addNewFieldDefinition('ModelName','name');
      $this->addNewFieldDefinition('ModelObjectDefinitionPrefix','bid_objd_prefix');
      $this->addNewFieldDefinition('ModelTablePrefix','bid_table_prefix');
      $this->addNewFieldDefinition('Comment','comment');
      $this->addNewFieldDefinition('JSONObjectData','jsondata');
  }//end initSpecificObjectFieldsDefintion()

  /**
   * Retourne l'objet Model depuis son BID et sa version
   *
   * @param  string $sBID       BID du modèle
   * @param  string $sVersion   Version du modèle
   * @return Model              Model trouvé, NULL si NOK
   */
  public static function getModelFromBIDVersion($sBID,$sVersion)
  {
    $loObj = new Model();
    $laResult = $loObj->searchObjects(
      sprintf(
        " UPPER(bid)=UPPER('%s') AND UPPER(version)=UPPER('%s')",
        $sBID,
        $sVersion
      )
    );

    $loObj = new Model($laResult[0]['Uid']);

    return $loObj;
  }//end getModelFromBIDVersion()

  /**
   * Retourne tous les models en base de données
   *
   * @return array    Tableau des résultats trouvés
   */
  public static function getAllModels()
  {
    $loObj = new Model();
    $laResult = $loObj->searchObjects('1=1');

    return $laResult;
  }//end getAllModels()

  /**
   * Retourne tous les définitions d'objet du model
   *
   * @return array    Tableau des résultats trouvés
   */
  public function getAllObjectDefinitions()
  {
    $loObj = new ObjectDefinition();
    $laResult = $loObj->searchObjects(
      sprintf(
        "uid_mdl = %s",
        strval($this->getUid())
      )
    );

    return $laResult;
  }//end getAllObjectDefinitions()

  /**
   * Retourne tous les définitions d'objet du model
   *
   * @return array    Tableau des résultats trouvés
   */
  public function getAllLinkDefinitions()
  {
    $loObj = new LinkDefinition();
    $laResult = $loObj->searchObjects("1=1");
    return $laResult;
  }//end getAllLinkDefinitions()

  /**
   * Mise à jour des vues SQL représentant les objets en base de données
   *
   * @return [type] [description]
   */
  public function updateSQLViewsIntoDatabase() {
    // Current object loaded ?
    if(is_null($this->getFieldValue('Uid')))
    {
      return null;
    }

  }//end updateSQLViewsIntoDatabase()


  public static function updateSQLViewsIntoDatabaseForModel($siModelUID)
  {
    $lObj = new Model($siModelUID);
    return $lObj->updateObjectInDatabase();
  }//end updateSQLViewsIntoDatabaseForModel()



}//end class

?>
