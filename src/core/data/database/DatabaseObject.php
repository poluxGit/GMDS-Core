<?php
namespace GMDS\Core\Data\Database;

use GMDS\Core\DatabaseManager;
use GMDS\Core\Exception\CoreException;
use GMDS\Core\Exception\DatabaseObjectException;
use GMDS\Core\Data\Database\SQL\SQLQueryGenerator;

/**
 * DatabaseObject
 *
 * Data persistance applicative layer
 */
class DatabaseObject
{
  // ***************************************************************************
  // Private properties
  // ***************************************************************************
  private $_oDbConnection         = null;
  private $_sTablename            = null;
  private $_aLinkedTables         = null;
  // private $_aDbKeys               = [];
  // private $_aFieldsDefinition     = [];
  // private $_aFieldsInitialValues  = [];
  // private $_aFieldsCurrentValues  = [];

  /**
   * Fields definition
   *
   * @var DatabaseObjectFields
   */
  public $_oFields = null;

  // ***************************************************************************
  // Default constructor
  // ***************************************************************************
  /**
   * Default constructor
   *
   * @param string          $sTablename     Table name about object wished
   * @param DatabaseHandler $oDB            Optionnal : Database handler
   */
  function __construct($sTablename, DatabaseHandler $oDB=null)
  {
    $this->_sTablename = $sTablename;
    if (\is_null($oDB)) {
      $this->_oDbConnection = DatabaseManager::getDefaultConnection();
    }
    else {
      $this->_oDbConnection = $oDB;
    }
    $this->_oFields = new DatabaseObjectFields($this);
  }//end __construct()

  // ***************************************************************************
  // Fields definition Management
  // ***************************************************************************
  /**
   * Define a field for current object
   *
   * @param  [type]  $sFieldName          [description]
   * @param  [type]  $sSQLFieldDefinition [description]
   * @param  [type]  $sFieldType          [description]
   * @param  [type]  $xDefaultValue       [description]
   * @param  boolean $isMandatory         [description]
   * @param  boolean $isKey               [description]
   * @param  integer $iOrder              [description]
   * @return [type]                       [description]
   */
  public function defineCommonField(
    $sFieldName,
    $sSQLFieldDefinition,
    $sFieldType,
    $xDefaultValue=null,
    $isMandatory=false,
    $isKey=false,
    $iOrder=0
  ){
    $loObj = $this->_oFields->addField(
      $sFieldName,
      $sSQLFieldDefinition,
      $sFieldType,
      $xDefaultValue,
      $isMandatory,
      $isKey,
      $iOrder
    );

    if (\is_null($loObj)) {
      throw new CoreException(
        "defineCommonField",
        "Le champ '%s' est deja declare.",
        [$sFieldName]
      );
    }
  }//end defineCommonField()


  public function defineLinkedField($sLinkedTablename,$sFieldName,$sSQLFieldDefinition,$sSQLTargetTableFieldName,$sSQLSourceTableFieldName,$iOrder=0)
  {
    $loObj = $this->_oFields->addLinkedTableField($sLinkedTablename,$sFieldName,$sSQLFieldDefinition,$sSQLTargetTableFieldName,$sSQLSourceTableFieldName,$iOrder);

    if (\is_null($loObj)) {
      throw new defineLinkedField(
        "defineCommonField",
        "Le champ '%s' est deja declare.",
        [$sFieldName]
      );
    }
  }//end defineLinkedField()

  // ***************************************************************************
  // TODEF
  // ***************************************************************************


  public function getFieldsDefinition()
  {
    return $this->_aFieldsDefinition;
  }



  // ***************************************************************************
  // Value management methods
  // ***************************************************************************
  /**
   * Set the initial value of a field
   *
   * @param string  $sFieldName    Field name to update
   * @param mixed   $xValue        Field value to set
   */
  protected function setFieldInitialValue($sFieldName,$xValue)
  {
    $this->_oFields->setFieldInitialValue($sFieldName,$xValue);
  }//end setFieldInitialValue()

  /**
   * Set the initial value of a field
   *
   * @param string  $sFieldName    Field name to update
   * @param mixed   $xValue        Field value to set
   */
  public function setFieldValue($sFieldName,$xValue)
  {
    $this->_oFields->setFieldValue($sFieldName,$xValue);
  }//end setFieldValue()

  /**
   * Return a Field value
   *
   * @throws DatabaseObjectException(Field not defined)
   * @param  string $sFieldName   Field unique name.
   * @return mixed  Field value (null).
   */
  public function getFieldValue($sFieldName)
  {
    return $this->_oFields->getFieldValue($sFieldName);
  }//end getFieldValue()

  /**
   * Return main table name
   *
   * @return string   Nom de la table en base de données.
   */
  public function getTablename()
  {
    return $this->_sTablename;
  }//end getTablename()

  // ***************************************************************************
  // LOAD DATA MANAGEMENT
  // ***************************************************************************
  /**
   * Load object from conditions (WHERE)
   *
   * @param  array $aCondition  Array of conditions [[uid,1],[mdl,3]]
   * @return [type]             [description]
   */
  public function loadObject($aCondition)
  {
    try {
      // SQL Select query generation!
      $lsSQLQuery = SQLQueryGenerator::generateSelectSQLQueryFromDatabaseObject($this,$aCondition);

      // DEBUG print_r($aCondition);

      // DatabaseHandler execution !
      $laResult = $this->_oDbConnection->queryAndFetch($lsSQLQuery);

      if (count($laResult)>1) {
        throw new DatabaseObjectException(
          "Impossible de charger l'objet - Plusieurs donnees sources trouvees. Nb: %s | Conditions: %s ",
          [\strval(count($laResult)),
          var_export($aCondition,true)]
        );
      }elseif (count($laResult)==0) {
        throw new DatabaseObjectException(
          "Impossible de charger l'objet - Aucune donnee trouvee - (Conditions: %s).",
          [var_export($aCondition,true)]
        );
      }

      // Loading first row from dataresult!
      $laRow = $laResult[0];
      $this->_loadInitialFieldValueFromArray($laRow);

    } catch (\Exception $e) {
      throw new DatabaseObjectException(
        "Erreur durant le chargement de l'objet : %s",
        $e->getMessage()
      );
    }
  }//end loadObject()

  /**
   * Set Current Object fields initial values from associative array
   *
   * @param  array $aFieldValueArray  Array of values
   */
  private function _loadInitialFieldValueFromArray($aFieldValueArray)
  {
    $this->_oFields->resetAllInitialFieldValue();
    foreach ($aFieldValueArray as $lsColumnName => $lsColumnValue) {
      if(!empty($lsColumnName)) {
        $this->setFieldInitialValue($lsColumnName,$lsColumnValue);
      }
    }
  }//end _loadInitialFieldValueFromArray()

  /**
   * Set Current Object fields values from associative array
   *
   * @param  array $aFieldValueArray  Array of values
   */
  private function _loadFieldValueFromArray($aFieldValueArray)
  {
    $this->_oFields->resetAllInitialFieldValue();
    foreach ($aFieldValueArray as $lsColumnName => $lsColumnValue) {
      if(!empty($lsColumnName)) {
        $this->setFieldValue($lsColumnName,$lsColumnValue);
      }
    }
  }//end _loadFieldValueFromArray()

  // ***************************************************************************
  // RECORD DATA - INSERT / UPDATE
  // ***************************************************************************
  /**
   * Enregistre l'objet en base de données.
   *
   * @return int Nombre d'enregistrements enregistrés. NULL si aucune action identifié (ni création, ni maj)
   */
  public function recordObject(){
    $liNbRows = null;
    if ($this->needAnInsert()) {
      $liNbRows = $this->createObjectInDatabase();
    } else {
      $liNbRows = $this->updateObjectInDatabase();
    }
    return $liNbRows;
  }//end recordObject()

  /**
   * Mise à jour de l'objet en base de données.
   *
   * @return [type] [description]
   */
  public function updateObjectInDatabase()
  {
    // PREPARATION OF SQL QUERY
    // Build Fields to update!
    $laSetPart = [];
    foreach ($this->_aFieldsCurrentValues as $key => $value) {
      if (!\is_null($value) && in_array($key, $this->_aFieldsDefinition)) {
        $laSetPart[$key] = $value;
      }
    }
    // Object related SQL Where conditions generation!
    $lsConditionWhere = $this->generateKeysSQLCondition();

    // SQL Update Query generation!
    $lsSQLQuery = SQLQueryGenerator::generatUpdateSQLQuery(
      $laSetPart,
      $this->_sTablename,
      [$lsConditionWhere]
    );

    // print_r($lsSQLQuery);

    try {
      // DatabaseHandler execution !
      $liNbRows = $this->_oDbConnection->execQuery($lsSQLQuery);
      $this->loadObjectFromCondition([$lsConditionWhere]);
    } catch (\Exception $e) {
      throw new ApplicationException(
        "updateObjectInDatabase",
        "Erreur durant la mise a jour de l'objet : %s",
        $e->getMessage()
      );
    }
    return $liNbRows;
  }//end updateObjectInDatabase()

  /**
   * Création de l'objet en base de données.
   *
   * @return int uid de l'objet
   */
  public function createObjectInDatabase()
  {
    $liUid = null;

    // PREPARATION OF SQL QUERY
    // Build Fields to insert!
    $laInsertFieldsPart = [];
    foreach ($this->_aFieldsCurrentValues as $key => $value) {
      if (!\is_null($value)) {
        $laInsertFieldsPart[$key] = $value;
      }
    }

    // SQL Update Query generation!
    $lsSQLQuery = SQLQueryGenerator::generatInsertSQLQuery(
      $laInsertFieldsPart,
      $this->_sTablename
    );

     // DatabaseHandler execution !
     $liNbRows = $this->_oDbConnection->execQuery($lsSQLQuery);
     //echo " $lsSQLQuery toto $liNbRows - ".$this->_oDbConnection->getPDOObject()->errorCode();

     if ($liNbRows == 0 || $this->_oDbConnection->getPDOObject()->errorCode() !== '00000') {
       throw new DatabaseObjectException(
         sprintf(
           "Error occured during Database Query execution (sql:%s) | Error : %s",
           $lsSQLQuery,
           $this->_oDbConnection->getPDOObject()->errorInfo()
         )
       );
     } else {
       // DEBUG echo "Uid after Create : $liNbRows \n";
       $this->loadObjectFromCondition(["uid = $liNbRows"]);
     }

    return intval($this->getUid());
  }//end createObjectInDatabase()

  /**
   * Retourne Vrai si au moins un champs est à mettre à jour.
   *
   * @return bool   Vrai si au moins un champs est à mettre à jour
   */
  public function needAnUpdate()
  {
    $lbResult = false;
    // Reset to null all fields current values to null!
    foreach ($this->_aFieldsCurrentValues as $key => $value) {
      if(!\is_null($value) && !$lbResult)
      {
        $lbResult = true;
      }
    }
    return $lbResult;
  }//end needAnUpdate()

  /**
   * Retourne Vrai si l'objet doit être inséré
   *
   * @internal Vérification des valeurs des clés, si toutes sont nulles alors VRAI
   *
   * @return bool   Vrai si toutes les champs clés de l'objet sont null
   */
  public function needAnInsert()
  {
    $lbResult = false;
    $lbResult = (\is_null($this->getFieldValue('Uid')));

    // foreach ($this->_aDbKeys as $key => $value) {
    //   if(!\is_null($this->getFieldValue($key)))
    //   {
    //     $lbResult = false;
    //     echo "Clé Not Insert trouvée : $key => $value \n";
    //   }
    // }
    return $lbResult;
  }//end needAnInsert()

  //////////////////////////////////////////////////////////////////////////////
  // Méthodes outils internes - Protected
  //////////////////////////////////////////////////////////////////////////////

  /**
   * Retourne la condition SQL (WHERE) afin de cibler l'objet
   *
   * @return string   Condition SQL
   */
  protected function generateKeysSQLCondition()
  {
    $lsSQLCondition = null;
    foreach ($this->_aDbKeys as $key => $value) {

      $lxFieldValue = $this->getFieldValue($key);

      if(\is_null($lsSQLCondition))
      {
        $lsSQLCondition = "";
      } else {
        $lsSQLCondition .= " AND ";
      }
      $lsSQLCondition .= $value." = ".((\is_string($lxFieldValue)?"'$lxFieldValue'":"$lxFieldValue"));
    }
    return $lsSQLCondition;
  }//end generateKeysSQLCondition()

  /**
   * Renvoi un tableau de données associatives correspondant aux objet
   * de la classe courante dont la condition a été appliqué
   *
   * @param  string $sWhereCondition  Condition SQL Where
   * @return array                    Données trouvées
   */
  public function searchObjects($sWhereCondition)
  {
    try {
      // SQL Select query generation!
      $lsSQLQuery = SQLQueryGenerator::generateSelectSQLQuery(
        $this->_aFieldsDefinition,
        $this->_sTablename,
        [$sWhereCondition],
        null
      );

      // DatabaseHandler execution !
      $laResult = $this->_oDbConnection->queryAndFetch($lsSQLQuery);

    } catch (\Exception $e) {
      throw new ApplicationException(
        "loadObjectFromCondition",
        "Erreur durant le chargement de l'objet : %s",
        $e->getMessage()
      );
    }
    return $laResult;
  }//end searchObjects()

  /**
   * Renvoi un tableau de données associatives correspondant aux objet
   * de la classe courante dont la condition a été appliqué
   *
   * @param  string $sWhereCondition  Condition SQL Where
   * @return array                    Données trouvées
   */
  protected function searchObjectsWithoutSelectFields($sWhereCondition)
  {
    try {
      // SQL Select query generation!
      $lsSQLQuery = SQLQueryGenerator::generateSelectSQLQuery(
        null,
        $this->_sTablename,
        [$sWhereCondition],
        null
      );

      // DatabaseHandler execution !
      $laResult = $this->_oDbConnection->queryAndFetch($lsSQLQuery);

    } catch (\Exception $e) {
      throw new ApplicationException(
        "loadObjectFromCondition",
        "Erreur durant le chargement de l'objet : %s",
        $e->getMessage()
      );
    }
    return $laResult;
  }//end searchObjectsWithoutSelectFields()

  /**
   * Retourne les champs et valeurs de l'objet courant
   *
   * @return array  Données de l'objet sous forme de tableau associatif
   */
  public function toAssocArray(){
    $laResult = [];

    foreach ($this->_aFieldsDefinition as $key => $value) {
      $laResult[$key] = $this->getFieldValue($key);
    }

    return $laResult;
  }//end toAssocArray()
}//end class

?>
