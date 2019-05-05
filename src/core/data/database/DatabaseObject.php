<?php
namespace GMDS\Core\Data\Database;

use GMDS\Core\DatabaseManager;
use GMDS\Core\Exception\CoreException;
use GMDS\Core\Exception\DatabaseObjectException;
use GMDS\Core\Data\Database\SQL\SQLQueryGenerator;

/**
 * DatabaseObject
 *
 * Classe abstraite d'objet persistant en base de données.
 */
class DatabaseObject
{
  friend DatabaseObjectField;

  // ***************************************************************************
  // Private properties
  // ***************************************************************************
  private $_oDbConnection         = null;
  private $_sTablename            = null;
  private $_aLinkedTables         = null;
  private $_aDbKeys               = [];
  private $_aFieldsDefinition     = [];
  private $_aFieldsInitialValues  = [];
  private $_aFieldsCurrentValues  = [];

  /**
   * Fields definition
   *
   * @var DatabaseObjectFields
   */
  private DatabaseObjectFields $_oFields = null;

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
  }//end __construct()

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
      $xDefaultValue=null,
      $isMandatory=false,
      $isKey=false,
      $iOrder=0
    );

    if (\is_null($loObj)) {
      throw new CoreException(
        "defineCommonField",
        "Le champ '%s' est deja declare.",
        [$sSettingsFilepath]
      );
    }
  }//end defineCommonField()


  public function defineLinkedField(
    // $sFieldName,
    // $sSQLFieldDefinition,
    // $sFieldType,
    // $xDefaultValue=null,
    // $isMandatory=false,
    // $isKey=false,
    // $iOrder=0
  ) {
    // TODO To implement
  }//end defineLinkedField()


  //////////////////////////////////////////////////////////////////////////////
  // Méhtodes de gestion des la définition des champs
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Add a new field definition on current object
   *
   * @param string $sName           Nom 'public' interne du champs.
   * @param string $sSQLFieldName   Champ SQL associé.
   */
  public function addNewFieldDefinition($sName, $sSQLFieldName)
  {
    if(!\array_key_exists($sName,$this->_aFieldsDefinition))
    {
      $this->_aFieldsDefinition[$sName] = $sSQLFieldName;
    }
    else {
      throw new ApplicationException(
        "addNewFieldDefinition",
        "Le champ '%s' est deja declare.",
        [$sSettingsFilepath]
      );
    }
  }//end addNewFieldDefinition()

  /**
   * addNewKeyFieldDefinition
   *
   * Ajout d'une nouvelle clé.
   *
   * @param string $sName           Nom 'public' interne du champs.
   * @param string $sSQLFieldName   Champ SQL associé.
   */
  public function addNewKeyFieldDefinition($sName, $sSQLFieldName)
  {
    if(!\array_key_exists($sName,$this->_aDbKeys))
    {
      $this->_aDbKeys[$sName] = $sSQLFieldName;
      // Ajout au référentiel de champs commun!
      $this->addNewFieldDefinition($sName, $sSQLFieldName);
    }
    else {
      throw new ApplicationException(
        "addNewKeyFieldDefinition",
        "Le champ '%s' (sql:%s) est deja defini comme cle de l'objet.",
        [$sName,$sSQLFieldName]
      );
    }
  }//end addNewKeyFieldDefinition()

  public function getFieldsDefinition()
  {
    return $this->_aFieldsDefinition;
  }

  /**
   * Return true if field is defined
   *
   * @param  string   $sFieldName   Field unique name.
   * @return boolean  true if defined
   */
  public function isFieldNameDefined($sFieldName)
  {
    return \array_key_exists($sFieldName,$this->_aFieldsDefinition);
  }//end isFieldNameDefined()

  //////////////////////////////////////////////////////////////////////////////
  // Méhtodes de gestion des valeurs de champs
  //////////////////////////////////////////////////////////////////////////////

  /**
   * Set a Field value
   *
   * @param string  $sFieldName   Nom interne du champs
   * @param mixed   $xValue       Valeur à définir.
   */
  public function setFieldValue($sFieldName,$xValue)
  {
    $sSQLFieldName = $this->getSQLFieldNameFromFieldName($sFieldName);
    $this->_aFieldsCurrentValues[$sSQLFieldName] = $xValue;
  }//end setFieldValue()

  /**
   * Set the initial value of a field
   *
   * @param string  $sFieldName   Nom interne du champs
   * @param mixed   $xValue       Valeur à définir.
   */
  protected function setFieldInitialValue($sFieldName,$xValue)
  {
    // DEBUG echo sprintf("Affectation %s => %s \n",$sFieldName,$xValue);
    $sSQLFieldName = $this->getSQLFieldNameFromFieldName($sFieldName);
    $this->_aFieldsInitialValues[$sSQLFieldName] = $xValue;
  }//end setFieldInitialValue()

  /**
   * Set the initial value of a field (SQLName)
   *
   * @param string  $sSQLFieldName    Nom SQL du champs
   * @param mixed   $xValue           Valeur à définir.
   */
  protected function setSQLFieldInitialValue($sSQLFieldName,$xValue)
  {
    $this->_aFieldsInitialValues[$sSQLFieldName] = $xValue;
  }//end setSQLFieldInitialValue()

  /**
   * Return Field value
   *
   * @param  string $sFieldName   Field unique name.
   * @return mixed  Field value.
   */
  public function getFieldValue($sFieldName)
  {
    $result = null;
    if ($this->isFieldNameDefined($sFieldName)) {
      $sSQLFieldName = $this->getSQLFieldNameFromFieldName($sFieldName);

      if(\array_key_exists($sSQLFieldName,$this->_aFieldsCurrentValues) && !\is_null($this->_aFieldsCurrentValues[$sSQLFieldName]) )
      {
        $result =  $this->_aFieldsCurrentValues[$sSQLFieldName];
      }
      elseif (\array_key_exists($sSQLFieldName,$this->_aFieldsInitialValues)) {
        $result =  $this->_aFieldsInitialValues[$sSQLFieldName];
      }

    } else {
      throw new ApplicationException(
        "getFieldValue",
        "Le champ '%s' n'est pas declare. Impossible de trouver sa valeur.",
        [$sFieldName]
      );
    }
    return $result;
  }//end getFieldValue()

  /**
   * Retourne le nom SQL du champs depuis son nom interne
   *
   * @param  string   $sName  Nom interne du champs à chercher.
   * @return string   NULL si non trouvée.
   */
  public function getSQLFieldNameFromFieldName($sName)
  {
    $result = null;
    if(\array_key_exists($sName,$this->_aFieldsDefinition))
    {
      $result =  $this->_aFieldsDefinition[$sName];
    }
    return $result;
  }//end getSQLFieldNameFromFieldName()

  /**
   * Retourne le nom de la table en base
   *
   * @return string   Nom de la table en base de données.
   */
  protected function getTablename()
  {
    return $this->_sTablename;
  }//end getTablename()

  //////////////////////////////////////////////////////////////////////////////
  // Chargement/Création/Mise à jour de l'objet en DB
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Load object from conditions (WHERE)
   *
   * @param  array $aCondition  Array of conditions.
   * @return [type]             [description]
   */
  public function loadObjectFromCondition($aCondition)
  {
    try {
      // SQL Select query generation!
      $lsSQLQuery = SQLQueryGenerator::generateSelectSQLQuery(
        $this->_aFieldsDefinition,
        $this->_sTablename,
        $aCondition,
        null
      );

      //print_r($aCondition);

      // DatabaseHandler execution !
      $laResult = $this->_oDbConnection->queryAndFetch($lsSQLQuery);

      if (count($laResult)>1) {
        throw new ApplicationException(
          "loadObjectFromCondition",
          "Impossible de charger l'objet - Plusieurs donnees sources trouvees. Nb: %s",
          \strval(count($laResult))
        );
      }elseif (count($laResult)==0) {
        throw new ApplicationException(
          "loadObjectFromCondition",
          "Impossible de charger l'objet - Aucune donnee trouvee.",
          ''
        );
      }
      // Loading first row from dataresult!
      $laRow = $laResult[0];
      foreach ($laRow as $lsColumnName => $lsColumnValue) {
        if(!empty($lsColumnName)) {
          $this->setFieldInitialValue($lsColumnName,$lsColumnValue);
        }
      }
      $this->resetCurrentValues();
    } catch (\Exception $e) {
      throw new ApplicationException(
        "loadObjectFromCondition",
        "Erreur durant le chargement de l'objet : %s",
        $e->getMessage()
      );
    }
  }//end loadObject()

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
   * RAZ des valeurs en cours
   *
   * @internal Défini à null le tableau des valeurs depuis celui des def de champs.
   */
  protected function resetCurrentValues()
  {
    // Reset to null all fields current values to null!
    foreach ($this->_aFieldsDefinition as $key => $value) {
      $this->_aFieldsCurrentValues[$value] = null;
    }
  }//end resetCurrentValues()

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
