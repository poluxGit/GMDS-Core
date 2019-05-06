<?php
namespace GMDS\Core\Data\Database;

use GMDS\Core\DatabaseManager;
use GMDS\Core\Data\Database\SQL\SQLQueryGenerator;

use GMDS\Core\Exception\CoreException;
use GMDS\Core\Exception\DatabaseObjectException;

/**
 * DatabaseObjectFields
 *
 * Manage a collection of fields linked to a specific DatabaseObject
 */
class DatabaseObjectFields
{
  //////////////////////////////////////////////////////////////////////////////
  // Private properties
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Array of DatabaseObjectField Objects
   *
   * @example [[ id => uid, mand => true, key => true, order => 1, field => {DatabaseObjectField Object}], ...]
   *
   * @var array(DatabaseObjectField)
   */
  private $_aFields           = [];
  /**
   * DatabaseObject reference
   *
   * @var DatabaseObject
   */
  private $_oDatabaseObject   = null;

  /**
   * Internal name of index about id of a field definition
   * @var string
   */
  const INTERNAL_FIELD_ID_IDX = 'id';
  /**
   * Internal name of index about order of a field definition
   * @var string
   */
  const INTERNAL_FIELD_ORDER_IDX = 'order';
  /**
   * Internal name of index about mandatory of a field definition
   * @var string
   */
  const INTERNAL_FIELD_ISMAND_IDX = 'mand';
  /**
   * Internal name of index about key of a field definition
   * @var string
   */
  const INTERNAL_FIELD_ISKEY_IDX = 'key';
  /**
   * Internal name of index about object of a field definition
   * @var string
   */
  const INTERNAL_FIELD_OBJ_IDX = 'field';

  // ***************************************************************************
  // Default constructor
  // ***************************************************************************
  /**
   * Default constructor
   *
   * @param DatabaseObject $oDBObj    DatabaseObject concerned
   */
  function __construct(DatabaseObject $oDBObj) {
    $this->_oDatabaseObject = $oDBObj;
  }//end __construct()

  // ***************************************************************************
  // Fields Management
  // ***************************************************************************
  /**
   * Root method about adding a new Field
   *
   * @param [type]  $sInternalID         [description]
   * @param [type]  $sFieldName          [description]
   * @param [type]  $sSQLFieldDefinition [description]
   * @param [type]  $sSQLFieldAlias      [description]
   * @param [type]  $sFieldType          [description]
   * @param [type]  $xDefaultValue       [description]
   * @param boolean $isMandatory         [description]
   * @param boolean $isKey               [description]
   * @param integer $iOrder              [description]
   *
   * @return DatabaseObjectField    FieldObject created and added (NULL if not)
   */
  protected function _addNewField($sInternalID,$sFieldName,$sSQLFieldDefinition,$sSQLFieldAlias,$sFieldType,$xDefaultValue,$isMandatory,$isKey,$iOrder=0,$sLinkedTablename=null,$aJoinConstraints=[]) {

    $laNewFieldData = [];

    $loObjNewField = new DatabaseObjectField(
      $sInternalID,
      $sFieldName,
      $sSQLFieldDefinition,
      $sSQLFieldAlias,
      $sFieldType,$xDefaultValue,$isMandatory,$isKey,$sLinkedTablename,$aJoinConstraints
    );

    // Field ID already exists ?
    if ($this->_isFieldIDExists($sInternalID)){
      return null;
    } else {

      // Order management !
      $liOrder = $iOrder;
      if ($iOrder==0) {
        $liOrder = count($this->_aFields)+1;

      } else {
        // Redefine Order of fields behond new one!
        // $mapFunc = function($fieldData){ if($fieldData[self::INTERNAL_FIELD_ORDER_IDX]>=$iTmpOrder){ $fieldData[self::INTERNAL_FIELD_ORDER_IDX] = $fieldData[self::INTERNAL_FIELD_ORDER_IDX]+1; }};
        // \array_map($mapFunc,$this->_aFields);
      }

      $laNewFieldData[self::INTERNAL_FIELD_ID_IDX]      = $sInternalID;
      $laNewFieldData[self::INTERNAL_FIELD_ISMAND_IDX]  = $loObjNewField->isMandatory();
      $laNewFieldData[self::INTERNAL_FIELD_ISKEY_IDX]   = $loObjNewField->isKey();
      $laNewFieldData[self::INTERNAL_FIELD_ORDER_IDX]   = $liOrder;
      $laNewFieldData[self::INTERNAL_FIELD_OBJ_IDX]     = $loObjNewField;
      array_push($this->_aFields,$laNewFieldData);

      // Need to sort fields ?
      if ($iOrder!=0) {
        $sortFunc = function($a,$b){ return $a[self::INTERNAL_FIELD_ORDER_IDX]>$b[self::INTERNAL_FIELD_ORDER_IDX]; };
        usort($this->_aFields,$sortFunc);
      }
      return $loObjNewField;
    }
  } //end _addNewField()

  /**
   * Add a new field in current SQL Field collection
   *
   * @param string  $sFieldName          Unique Field name
   * @param string  $sSQLFieldDefinition SQL definition
   * @param string  $sFieldType          Field type
   * @param mixed   $xDefaultValue       Default Value (Optional)
   * @param boolean $isMandatory         Field is mandatory ?
   * @param boolean $isKey               Field is key ?
   * @param integer $iOrder              Field Order
   *
   * @return DatabaseObjectField        Field Object created (NULL if not)
   */
  public function addField($sFieldName,$sSQLFieldDefinition,$sFieldType,$xDefaultValue=null,$isMandatory=false,$isKey=false,$iOrder=0) {
    return $this->_addNewField(strtolower($sFieldName),$sFieldName,$sSQLFieldDefinition,$sFieldName,$sFieldType,$xDefaultValue,$isMandatory,$isKey,$iOrder);
  }//end addField()

  /**
   * Add a new key field
   *
   * @param string  $sFieldName          Unique Field name
   * @param string  $sSQLFieldDefinition SQL definition
   * @param string  $sFieldType          Field type
   * @param integer $iOrder              (Optional) Field Order
   */
  public function addKeyField($sFieldName,$sSQLFieldDefinition,$sFieldType,$iOrder=0) {
    return $this->_addNewField(strtolower($sFieldName),$sFieldName,$sSQLFieldDefinition,$sFieldName,$sFieldType,$xDefaultValue,true,true,$iOrder);
  }//end addKeyField()

  /**
   * Add a new mandatory field
   *
   * @param string  $sFieldName          Unique Field name
   * @param string  $sSQLFieldDefinition SQL definition
   * @param string  $sFieldType          Field type
   * @param mixed   $xDefaultValue       (Optional) Default Value
   * @param integer $iOrder              (Optional) Field Order
   */
  public function addMandatoryField($sFieldName,$sSQLFieldDefinition,$sFieldType,$xDefaultValue=null,$iOrder=0) {
    return $this->_addNewField(strtolower($sFieldName),$sFieldName,$sSQLFieldDefinition,$sFieldName,$sFieldType,$xDefaultValue,true,false,$iOrder);
  }//end addMandatoryField()

  /**
   * Add a new Linked field (another table)
   *
   * @param string  $sLinkedTablename         Linked table name
   * @param string  $sFieldName               Unique field name
   * @param string  $sSQLFieldDefinition      SQL field definition
   * @param string  $sSQLTargetTableFieldName SQL target table field linked to source object' table
   * @param string  $sSQLSourceTableFieldName SQL source table field
   * @param integer $iOrder                   Field Order
   */
  public function addLinkedTableField($sLinkedTablename,$sFieldName,$sSQLFieldDefinition,$sSQLTargetTableFieldName,$sSQLSourceTableFieldName,$iOrder=0) {
    $laJoinConstraints = ['LEFT JOIN',$sSQLTargetTableFieldName,$sSQLSourceTableFieldName];
    return $this->_addNewField(strtolower($sFieldName),$sFieldName,$sSQLFieldDefinition,$sFieldName,'string',null,false,false,$iOrder,$sLinkedTablename,$laJoinConstraints);
  }//end addLinkedTableField()

  // ***************************************************************************
  // Value management
  // ***************************************************************************
  /**
   * Return a field value
   *
   * @param string $sFieldName  Field name to Search
   * @return mixed Value of field
   */
  public function getFieldValue($sFieldName){
    $lResult = null;
    if ($this->_isFieldIDExists($sFieldName)) {
      $loFieldObj = $this->getFieldObjectById($sFieldName);
      if ($loFieldObj instanceof DatabaseObjectField) {
        if (!\is_null($loFieldObj->getValue())) {
          $lResult = $loFieldObj->getValue();
        } else if (!\is_null($loFieldObj->getInitialValue())) {
          $lResult = $loFieldObj->getInitialValue();
        }
      }
    } else {
      throw new DatabaseObjectException(
        "Le champ '%s' n'est pas declare. Impossible de trouver sa valeur.",
        [$sFieldName]
      );
    }
    return $lResult;
  }//end getFieldValue()

  /**
   * Set a field value
   *
   * @param string  $sFieldname Field name
   * @param mixed   $xValue     Value to set
   */
  public function setFieldValue($sFieldname,$xValue)
  {
    $lResult = null;
    if ($this->_isFieldIDExists($sFieldName)) {
      $loFieldObj = $this->getFieldObjectById($sFieldName);
      $loFieldObj->setValue($xValue);
    } else {
      throw new DatabaseObjectException(
        "Le champ '%s' n'est pas déclaré. Impossible de définir sa valeur.",
        [$sFieldName]
      );
    }
  }//end setFieldValue()

  /**
   * Set a initial field value
   *
   * @param string  $sFieldname Field name
   * @param mixed   $xValue     Value to set
   */
  public function setFieldInitialValue($sFieldname,$xValue)
  {
    $lResult = null;
    if ($this->_isFieldIDExists($sFieldName)) {
      $loFieldObj = $this->getFieldObjectById($sFieldName);
      $loFieldObj->setInitialValue($xValue);
    } else {
      throw new DatabaseObjectException(
        "Le champ '%s' n'est pas déclaré. Impossible de définir sa valeur.",
        [$sFieldName]
      );
    }
  }//end setFieldInitialValue()

  /**
   * Return TRUE if a field with ID already exists
   *
   * @param  string  $sInternalFieldID Internal Field ID
   * @return boolean                   TRUE if a field with this ID exists
   */
  protected function _isFieldIDExists($sInternalFieldID) {
    foreach($this->_aFields as $fieldData){
      if(\strtolower($fieldData[self::INTERNAL_FIELD_ID_IDX])===\strtolower($sInternalFieldID))
      {
        return true;
      }
    }
    return false;
  }//end isFieldIDExists()

  /**
   * Return Field Object from his ID
   *
   * @param  string               $sFieldName Field name
   * @return DatabaseObjectField              Field object.
   */
  public function getFieldObjectById($sFieldName):DatabaseObjectField{
    $lFieldObj = null;
    foreach($this->_aFields as $fieldData){
      if(\strtolower($fieldData[self::INTERNAL_FIELD_ID_IDX])===\strtolower($sFieldName))
      {
        $lFieldObj = $fieldData[self::INTERNAL_FIELD_OBJ_IDX];
      }
    }
    return \is_null($lFieldObj)?null:$lFieldObj;
  }//end getFieldObjectById()

  /**
   * Return an array having for values all field names in order
   *
   * @return array    Array of field names in order.
   */
  public function getAllFieldId():array {
    $laResult = [];
    foreach ($this->_aFields as $fieldData) {
      \array_push($laResult,$fieldData[self::INTERNAL_FIELD_ID_IDX]);
    }
    return $laResult;
  }//end getAllFieldId()

  public function resetAllFieldValue()
  {
    foreach ($this->getAllFieldId() as $value) {
      $this->getFieldObjectById($value)->setValue(null);
    }
  }

  public function resetAllInitialFieldValue()
  {
    foreach ($this->getAllFieldId() as $value) {
      $this->getFieldObjectById($value)->setInitialValue(null);
    }
  }

}//end class

?>
