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

  //////////////////////////////////////////////////////////////////////////////
  // Default constructor
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Default constructor
   *
   * @param DatabaseObject $oDBObj    DatabaseObject concerned
   */
  function __construct(DatabaseObject $oDBObj) {
    $this->_oDatabaseObject = $oDBObj;
  }//end __construct()

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
  protected function _addNewField($sInternalID,$sFieldName,$sSQLFieldDefinition,$sSQLFieldAlias,$sFieldType,$xDefaultValue,$isMandatory,$isKey,$iOrder=0) {

    $laNewFieldData = [];

    $loObjNewField = new DatabaseObjectField(
      $sInternalID,
      $sFieldName,
      $sSQLFieldDefinition,
      $sSQLFieldAlias,
      $sFieldType,$xDefaultValue,$isMandatory,$isKey
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
        $mapFunc = function($fieldData){ if($fieldData[self::INTERNAL_FIELD_ORDER_IDX]>=$iOrder){ $fieldData[self::INTERNAL_FIELD_ORDER_IDX] = $fieldData[self::INTERNAL_FIELD_ORDER_IDX]+1; } });
        \array_map($mapFunc,$this->_aFields);
      }

      $laNewFieldData[self::INTERNAL_FIELD_ID_IDX]      = $sInternalID;
      $laNewFieldData[self::INTERNAL_FIELD_ISMAND_IDX]  = $loObjNewField->isMandatory();
      $laNewFieldData[self::INTERNAL_FIELD_ISKEY_IDX]   = $loObjNewField->isKey();
      $laNewFieldData[self::INTERNAL_FIELD_ORDER_IDX]   = $liOrder;
      $laNewFieldData[self::INTERNAL_FIELD_OBJ_IDX]     = $loObjNewField;
      array_push($this->_aFields,$laNewFieldData);

      // Need to sort fields ?
      if ($iOrder!=0) {
        $sortFunc = function($a,$b){ return $a[self::INTERNAL_FIELD_ORDER_IDX]<$b[self::INTERNAL_FIELD_ORDER_IDX]; });
        usort($this->_aFields,$sortFunc);
      }

      return $loObjNewField;
    }
  } //end _addNewField()

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
   * Add a new field onto current SQL Field collection
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

  public function addKeyField($sFieldName,$sSQLFieldDefinition,$sFieldType,$iOrder=0) {
    return $this->_addNewField(strtolower($sFieldName),$sFieldName,$sSQLFieldDefinition,$sFieldName,$sFieldType,$xDefaultValue,true,true,$iOrder);
  }

  public function addMandatoryField($sFieldName,$sSQLFieldDefinition,$sFieldType,$xDefaultValue=null,$iOrder=0) {
    return $this->_addNewField(strtolower($sFieldName),$sFieldName,$sSQLFieldDefinition,$sFieldName,$sFieldType,$xDefaultValue,true,false,$iOrder);
  }

}//end class

?>
