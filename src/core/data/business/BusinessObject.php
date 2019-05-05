<?php

namespace GDTA\Core\Data\Business;

use GDTA\Core\Exception\BusinessObjectException;
use GDTA\Core\Data\Database\DatabaseObject;
use GDTA\Core\Data\Database\DatabaseHandler;
use GDTA\Core\Model\Traits\DatabaseObjectUidKeyField;
use GDTA\Core\Model\Traits\DatabaseObjectVersionFields;
use GDTA\Core\Model\Traits\DatabaseObjectCreationUpdateFields;
use GDTA\Core\Model\Traits\DatabaseObjectCommonField;

use GDTA\Core\Model\ObjectDefinition;
use GDTA\Core\Model\ObjectMetaDefinition;

/**
 * BusinessObject - Classe générique d'objet métier.
 */
class BusinessObject extends DatabaseObject
{
  use DatabaseObjectUidKeyField, DatabaseObjectVersionFields, DatabaseObjectCreationUpdateFields, DatabaseObjectCommonField;

  //////////////////////////////////////////////////////////////////////////////
  // Constructeurs par défaut & initialisation
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Constructeur par défaut
   *
   * @param int             (Optionel) $iUid Uid interne de l'objet
   * @param string          Nom de la table de la base de données
   * @param DatabaseHandler $oDB  Database Handler
   */
  function __construct($iUid=null,$sTablename,DatabaseHandler $oDB=null)
  {
    parent::__construct($sTablename,$oDB);
    $this->setObjectKeyDBFields();
    $this->setVersionDBfields();
    $this->setCommonDBfields();
    $this->setCreationUpdateDBFields();
    $this->initSpecificObjectFieldsDefintion();

    if (!\is_null($iUid)) {
      $this->loadObjectFromCondition(["uid = $iUid"]);
    }
  }//end __construct()

  /**
   * Initialisation "interne"
   *
   */
  protected function initSpecificObjectFieldsDefintion()
  {
      // Other fields definition ...
      $this->addNewFieldDefinition('ObjectUid','uid_obj');

  }//end initSpecificObjectFieldsDefintion()

  //////////////////////////////////////////////////////////////////////////////
  // Accesseurs
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Retourne l'objet InternalObject attaché à l'objet courant
   *
   * @return InternalObject   Instance d'objet InternalObject, null si non défini
   */
  public function getInternalObject()
  {
    $lObjReturned = null;
    $lsObjUid = $this->getFieldValue('ObjectUid');
    if(!\is_null($lsObjUid)) {
      $lObjReturned = new InternalObject($lsObjUid);
    }
    return $lObjReturned;
  }//end getInternalObject()

  //////////////////////////////////////////////////////////////////////////////
  // Enregistrement de l'objet
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Surcharge de la méthode DatabaseObject::recordObject()
   *
   * @return int  Nb lignes impactées
   */
  public function recordObject()
  {
    $liNbRows = null;
    try {
      if ($this->needAnInsert()) {
        // Création de l'objet interne!
        $liObjDefUid = $this->createInternalObjectForCurrentObject();

        // Création de l'objet courant!
        $this->setFieldValue('ObjectUid',$liObjDefUid);
        $liObjUid = $this->createObjectInDatabase();

        // DEBUG echo "Identifiant en Base de l'objet Business".strval($liObjUid);

        // Mise à jour de l'objet interne!
        $loObj = new InternalObject($liObjDefUid);
        $loObj->setFieldValue('ObjectUid',$liObjUid);
        $loObj->recordObject();
        $liNbRows = $loObj->getUid();
      } else {
        $liNbRows = parent::recordObject();
      }
      // BusinessMetatdata creation for current object !
      $this->_instanciateObjectMetaForCurrentObject();
    } catch (\Exception $e) {
      // REMOVE InternalObject TODO
      throw new BusinessObjectException(sprintf(
        "Erreur durant la creation de l'objet Business '%s' (ObjInterneUID:%s) : %s.",
        $this->_sTablename,
        $liObjDefUid,
        $e->getMessage()
      ));
    } finally {
      return $liNbRows;
    }
  }//end recordObject()

  /**
   * Définie la valeur d'une meta sur l'objet
   *
   * @param int $iUidObjMetaDef   UID de la définition de meta
   * @param int $xValue           Valeur
   */
  public function setBusinessMetaValue($iUidObjMetaDef,$xValue) {
    $lObjMetaDef = new ObjectMetaDefinition($iUidObjMetaDef);
    // TODO Générer le cas pas de retour !
    $laValueTmp = [
      "value"     => ($lObjMetaDef->getFieldValue('MetaType')=="Integer"&&\is_int($xValue)?intval($xValue):$xValue),
      "dataType"  => $lObjMetaDef->getFieldValue('MetaType'),
      "dataPattern"  => $lObjMetaDef->getFieldValue('MetaPattern')
    ];

    //print_r(this);

    $liUid = $this->getInternalObject()->getFieldValue('Uid');
    $loBusinessMetaObj = BusinessDataFactory::getBusinessMetaObjectFromObjectAndMetaDef(
      $liUid,
      $lObjMetaDef->getUid()
    );
    // TODO Générer le cas pas de retour !
    $loBusinessMetaObj->setFieldValue('Value',json_encode($laValueTmp));
    $loBusinessMetaObj->recordObject();



  }//end setBusinessMetaValue()

  //////////////////////////////////////////////////////////////////////////////
  // Méthodes privées
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Création de l'objet interne de l'objet courant
   *
   * @return int  Objet Interne crée
   */
  private function createInternalObjectForCurrentObject()
  {
    $loInternalObject = new InternalObject();

    $loObjDef = ObjectDefinition::getObjectDefinitionFromTablename(
      $this->getTablename()
    );
    //print_r($loObjDef);

    if (\is_null($loObjDef)) {
      throw new BusinessObjectException(
        sprintf(
          "Erreur durant la creation de l'InternalObject pour le BusinessObject : '%s'",
          $this->getFieldValue('Bid')
        )
      );
    }

    $loInternalObject->setFieldValue('ObjectDefinitionUid',$loObjDef->getFieldValue('Uid'));
    $loInternalObject->setFieldValue('ModelUid',$loObjDef->getFieldValue('ModelUid'));
    $loInternalObject->setFieldValue('Bid',$this->getFieldValue('Bid'));

    // Objet Not Versionable => default values
    if( $loObjDef->getFieldValue('isVersionable') == '1' ) {
      $loInternalObject->setVersion($this->getVersion());
      $loInternalObject->setRevision($this->getRevision());
    }
    else {
      $loInternalObject->setVersion('UNIQ');
      $loInternalObject->setRevision(0);
    }

    $loInternalObject->recordObject();

    return intval($loInternalObject->getUid());
  }//end createInternalObjectForCurrentObject()

  /**
   * Instanciation des metadonnées pour l'objet courant
   *
   * @return bool   True si OK!
   */
  protected function _instanciateObjectMetaForCurrentObject()
  {
    $loObjDef = $this->getObjectDefinition();
    $loObjInt  = $this->getInternalObject();

    $liObjIntUID = $loObjInt->getUid();

    if(!\is_null($loObjDef) && !\is_null($loObjInt)) {
      $laMetaObjDef = $loObjDef->getObjectMetaDefArray();
      // Meta to def ?
      if (count($laMetaObjDef)>0) {
        foreach ($laMetaObjDef as $loObjectTmp) {
          if ($loObjectTmp instanceof ObjectMetaDefinition) {
            // FIXME dup des meta données - En attendant on met toujours à zéro
            $loNewObjTmp = new BusinessObjectMeta();
            $loNewObjTmp->setFieldValue('ObjectMetaDefinitionUid',$loObjectTmp->getUid());
            $loNewObjTmp->setFieldValue('ObjectUid',$liObjIntUID);

            // FIXME Gestion Utilisateurs - Redéfinir
            $loNewObjTmp->setCreatorUid(1);
            $loNewObjTmp->recordObject();
          }
        }
      }
    }

    return TRUE;
  }//end _instanciateObjectMetaForCurrentObject()

  /**
   * Retourne la définition d'objet de l'objet courant
   *
   * @return ObjectDefinition   Définition de l'objet courant. (Null si non trouvée)
   */
  public function getObjectDefinition():ObjectDefinition
  {
    $lObjInterne = $this->getInternalObject();
    $loObj = null;
    if (!\is_null($lObjInterne)) {
      $loObj = new ObjectDefinition(
        $lObjInterne->getFieldValue('ObjectDefinitionUid')
      );
    }
    return $loObj;
  }//end getObjectDefinition()

}//end class

?>
