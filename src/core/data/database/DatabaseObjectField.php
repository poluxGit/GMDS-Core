<?php
namespace GMDS\Core\Data\Database;

use GMDS\Core\DatabaseManager;
use GMDS\Core\Data\Database\SQL\SQLQueryGenerator;

use GMDS\Core\Exception\CoreException;
use GMDS\Core\Exception\DatabaseObjectException;

/**
 * DatabaseObjectField
 *
 * A database object field
 */
class DatabaseObjectField
{
  // ***************************************************************************
  // Private properties
  // ***************************************************************************
  /**
   * Internal Field name
   * @var string
   */
  protected $_sInternalFieldName  = null;
  /**
   * Public Field name
   * @var string
   */
  protected $_sFieldName          = null;
  /**
   * SQL Field definition
   * @var string
   */
  protected $_sSQLFieldDef        = null;
  /**
   * SQL Field alias
   * @var string
   */
  protected $_sSQLFieldAlias      = null;
  /**
   * Field type
   * @var string
   */
  protected $_sFieldType          = null;
  /**
   * Initial value of field (after loading)
   * @var mixed
   */
  protected $_xInitValue          = null;
  /**
   * Default value of field (if not specified)
   * @var mixed
   */
  protected $_xDefaultValue       = null;
  /**
   * Value of field
   * @var mixed
   */
  protected $_xValue              = null;
  /**
   * Is a mandatory field
   * @var bool
   */
  protected $_isMandatory         = false;
  /**
   * Field mandatory
   * @var bool
   */
  protected $_isKey               = false;
  /**
   * Linked tablename
   *
   * @var string
   */
  protected $_sLinkedTableName    = null;
  /**
   * Linked Field constraints
   *
   * @example [['fieldCurrentObject','fieldDestination']]
   * @var array
   */
  protected $_aLinkedTableJoin    = [];

  // ***************************************************************************
  // Default constructor
  // ***************************************************************************
  /**
   * Default constructor
   *
   * @param string  $sInternalFieldName Internal field name (uid,label ...)
   * @param string  $sFieldName         Field name (Uid,Label,Name ...)
   * @param string  $sSQLFieldDef       SQL field definition about field (uid, CONCAT(bid,version,revision), ...)
   * @param string  $sSQLFieldAlias     SQL field alias (ID, Name....)
   * @param string  $sFieldType         Field type (int,string,bool,obj ...) TODO A définir précisément
   * @param mixed   $xDefaultValue      Field' default value
   * @param boolean $isMandatory        Field is Mandatory ?
   * @param boolean $isKey              Field is Key of DatabaseObject
   */
  function __construct($sInternalFieldName,$sFieldName,$sSQLFieldDef,$sSQLFieldAlias,$sFieldType,$xDefaultValue=null,$isMandatory=false,$isKey=false,$sLinkTablename=null,$aLinkTable=[])
  {
    $this->_sInternalFieldName  = $sInternalFieldName;
    $this->_sFieldName          = $sFieldName;
    $this->_sSQLFieldDef        = $sSQLFieldDef;
    $this->_sSQLFieldAlias      = $sSQLFieldAlias;
    $this->_sFieldType          = $sFieldType;
    $this->_xDefaultValue       = $xDefaultValue;
    $this->_isMandatory         = $isMandatory;
    $this->_isKey               = $isKey;
    $this->_sLinkedTableName    = $sLinkTablename;
    $this->_aLinkedTableJoin    = $aLinkTable;
  }//end __construct()

  // ***************************************************************************
  // Getters
  // ***************************************************************************
  public function getDefaultValue()   { return $this->_xDefaultValue; }
  public function getName()           { return $this->_sFieldName; }
  public function getSQLDefinition()  { return $this->_sSQLFieldDef; }
  public function getSQLAlias()       { return $this->_sSQLFieldAlias; }
  public function getFieldType()      { return $this->_sFieldType; }
  public function getInitialValue()   { return $this->$_xInitValue; }
  public function getValue()          { return $this->_xValue; }
  public function isMandatory()       { return $this->_isMandatory; }
  public function isKey()             { return $this->_isKey; }
  public function getLinkedTableName(){ return $this->_sLinkedTableName; }
  public function getLinkedTableJoinConstraintsArray()  { return $this->_aLinkedTableJoin; }

  // ***************************************************************************
  // Setters
  // ***************************************************************************
  /**
   * Set value of field
   * @param mixed $xValue   Value to set
   */
  public function setValue($xValue) {
    $this->_xValue = $xValue;
  }//end setValue()

  /**
   * Set initial value of field
   * @param mixed $xValue   Value to set
   */
  public function setInitialValue($xValue) {
    $this->_xValue = $xValue;
  }//end setValue()

}//end class
?>
