<?php

namespace GDTA\Core\Tools;


use GDTA\Core\ApplicationManager;
use GDTA\Core\ModelManager;
use GDTA\Core\Tools\Logger;
use GDTA\Core\Exception\ModelImportException;
use GDTA\Core\Model\Model;
use GDTA\Core\Model\ObjectDefinition;
use GDTA\Core\Model\ObjectMetaDefinition;
use GDTA\Core\Model\LinkDefinition;
use GDTA\Core\Model\LinkMetaDefinition;
use GDTA\Core\Data\Database\DatabaseObject;
/**
 * ModelImporter - Processus d'import d'une version d'un modèle
 *
 */
class ModelImporter
{
  /**
   * Fichier source du model
   *
   * @var string
   */
  private $_modelFilepath = null;

  /**
   * Logger d'import
   *
   * @var Logger
   */
  private $_oLogger = null;

  /**
   * Contenu du Model
   *
   * @var array
   */
  private $_aModelContent = [];

  /**
   * Construteur par défaut
   */
  public function __construct($sCompleteFilepath)
  {
    $this->_modelFilepath = $sCompleteFilepath;
  }//end __construct()



  /**
   * Retourne vrai si le fichier du model existe
   *
   * @return boolean Vrai si le fichier existe, faux sinon
   */
  protected function modelFileExists()
  {
    $lbResult = true;
    if (\is_null($this->_modelFilepath)) {
      $lbResult = false;
    } elseif (!\file_exists($this->_modelFilepath)) {
      $lbResult = false;
    }
    return $lbResult;
  }//end modelFileExists()

  private function _checkMandatoryFieldsIntoContent($aContentPathToCheck,$aFieldsToCheck)
  {
    $lbValidity = true;
    $laCurrentArray = $this->_aModelContent;
    $laContentPathProgress = [];
    $laFieldsInError = [];

    // -------------------------------------------------------------------------
    // Existance du chemin où valider les champs obligatoires ?
    // -------------------------------------------------------------------------

    // On se positionne au bon niveau de l'arborescence!
    foreach ($aContentPathToCheck as $value) {
      // Index de parcours existant ?
      if (!\array_key_exists($value,$laCurrentArray)) {
        // Si le chemin ne peux pas être atteint alors Exception!
        throw new ModelImportException(
          'CheckModel',
          sprintf(
            "Structure '%s' non trouvee.",
            implode('/',$laContentPathProgress).'/'.$value
          ),
          $this->_modelFilepath
        );
      } else {
        \array_push($laContentPathProgress,$value);
      }
      $laCurrentArray = $laCurrentArray[$value];
    }//end foreach

    // Log Parcours OK!
    $this->addLogModelImportMessage("CheckModel",
      sprintf(
        "Niv. %02d - %s | Validation de l'existance des champs obligatoires.",
        count($laContentPathProgress),
        implode('/',$laContentPathProgress)
      ),
      true
    );

    $liNiveau = count($laContentPathProgress)+1;
    $lsPath = implode('/',$laContentPathProgress);

    // -------------------------------------------------------------------------
    // Validation de l'existance des champs obligatoires!
    // -------------------------------------------------------------------------
    foreach ($aFieldsToCheck as $value) {
      // Champ obligatoire existant ?
      if (!\array_key_exists($value,$laCurrentArray)) {
        $this->addLogModelValidationFieldExists(
          $liNiveau,
          $lsPath,
          $value,
          true,
          false);
        $lbValidity = false;
        \array_push($laFieldsInError,$value);
      } else {
        $this->addLogModelValidationFieldExists(
          $liNiveau,
          $lsPath,
          $value,
          true,
          true);
      }
    }//end foreach

    // Log selon existances des champs!
    if ($lbValidity) {

      $this->addLogModelImportMessage(
        "CheckModel",
        sprintf(
          "Niv. %02d - %s | Tous les champs obligatoires sont definis.",
          count($laContentPathProgress),
          implode('/',$laContentPathProgress)
        ),
        true
      );

    } else {

      $this->addLogModelImportMessage(
        "CheckModel",
        sprintf(
          "Niv. %02d - %s | Les champs obligatoires suivant sont manquants : '%s'.",
          count($laContentPathProgress),
          implode('/',$laContentPathProgress),
          implode(', ',$laFieldsInError)
        ),
        false
      );
    }

    return $lbValidity;
  }//end _checkMandatoryFieldsIntoContent()


  private function _checkFieldsIntoContent($aContentPathToCheck,$aFieldsToCheck)
  {
    $lbValidity = true;
    $laCurrentArray = $this->_aModelContent;
    $laContentPathProgress = [];
    $laFieldsInWarning = [];

    // -------------------------------------------------------------------------
    // Existance du chemin où valider les champs obligatoires ?
    // -------------------------------------------------------------------------

    // On se positionne au bon niveau de l'arborescence!
    foreach ($aContentPathToCheck as $value) {
      // Index de parcours existant ?
      if (!\array_key_exists($value,$laCurrentArray)) {
        // Si le chemin ne peux pas être atteint alors Exception!
        throw new ModelImportException(
          'CheckModel',
          sprintf(
            "Structure '%s' non trouvee.",
            implode('/',$laContentPathProgress).'/'.$value
          ),
          $this->_modelFilepath
        );
      } else {
        \array_push($laContentPathProgress,$value);
      }
      $laCurrentArray = $laCurrentArray[$value];
    }//end foreach

    // Log Parcours OK!
    $this->addLogModelImportMessage("CheckModel",
      sprintf(
        "Niv. %02d - %s | Validation de l'existance des champs optionnels.",
        count($laContentPathProgress),
        implode('/',$laContentPathProgress)
      ),
      true
    );

    $liNiveau = count($laContentPathProgress)+1;
    $lsPath = implode('/',$laContentPathProgress);


    // -------------------------------------------------------------------------
    // Validation de l'existance des champs !
    // -------------------------------------------------------------------------
    foreach ($aFieldsToCheck as $value) {
      // Champ obligatoire existant ?
      if (!\array_key_exists($value,$laCurrentArray)) {
        $this->addLogModelValidationFieldExists(
          $liNiveau,
          $lsPath,
          $value,
          false,
          false);
        $lbValidity = false;
        \array_push($laFieldsInWarning,$value);
      } else {
        $this->addLogModelValidationFieldExists(
          $liNiveau,
          $lsPath,
          $value,
          false,
          true);
      }
    }//end foreach

    // Log selon existances des champs!
    if ($lbValidity) {
      $this->addLogModelImportMessage(
        "CheckModel",
        sprintf(
          "Niv. %02d - %s | Tous les champs optionnels sont definis.",
          count($laContentPathProgress),
          implode('/',$laContentPathProgress)
        ),
        true
      );
    } else {

      $this->addLogModelImportMessage(
        "CheckModel",
        sprintf(
          "Niv. %02d - %s | Les champs optionnels suivant sont manquants : '%s'.",
          count($laContentPathProgress),
          implode('/',$laContentPathProgress),
          implode(', ',$laFieldsInWarning)
        ),
        false
      );
    }

    return $lbValidity;
  }//end _checkFieldsIntoContent()

  /**
   * Evaluation de la validité de la définition du modèle
   */
  public function checkModelContentValidity()
  {
    // Internal flags!
    $lbWithoutValidity = true;
    $lbwithoutWarning  = true;

    // Niveau 1 - Model
    $this->_oLogger->addLogSep();
    $lbWithoutValidity = $this->_checkMandatoryFieldsIntoContent(
      ['model'],
      ['code','version','label','name','definitions','table_prefix','bid_objd_prefix']
    );
    $this->_oLogger->addLogSep();
    $lbwithoutWarning  = $this->_checkFieldsIntoContent(
      ['model'],
      ['comment']
    );
    $this->_oLogger->addLogSep();

    // Niveau 2 - Model/Definitions
    if ($lbWithoutValidity) {
      $lbWithoutValidity = $this->_checkMandatoryFieldsIntoContent(
        ['model','definitions'],
        ['objects','links']
      );
      $this->_oLogger->addLogSep();
      $lbwithoutWarning  = $this->_checkFieldsIntoContent(
        ['model','definitions'],
        ['rules','rights']
      );
      $this->_oLogger->addLogSep();
    }

    return $lbWithoutValidity;
  }//end checkModelContentValidity()

  /**
   * Import du model en base de données
   *
   */
  public function importModelIntoDatabase()
  {
    $lsStepImport = "Init";

    // Initialisation du Logger!
    $lsLogFilePath = ApplicationManager::generateLoggerFileNameWithDate(
      date('His').'-logImportModel'
    );
    $this->_oLogger = new Logger($lsLogFilePath,'NOTOK');

    // Log
    $this->addLogModelImportMessage(
      $lsStepImport,
      sprintf(
        "Demarrage de l'import du fichier %s",
        $this->_modelFilepath
      )
    );

    // --------------------------------------------------------------------
    // Existance du fichier source ?
    // --------------------------------------------------------------------
    if (!$this->modelFileExists()) {
      throw new ModelImportException(
        $lsStepImport,
        "Fichier source inexistant.",
        $this->_modelFilepath
      );
    } else {
      // Log
      $this->addLogModelImportMessage(
        $lsStepImport,
        sprintf(
          "Fichier du model '%s' trouve avec succes.",
          $this->_modelFilepath
        )
      );
    }

    // --------------------------------------------------------------------
    // Décodage JSON
    // --------------------------------------------------------------------
    $lsStepImport = 'Preparation';
    $lsJSONArray = \json_decode(\file_get_contents($this->_modelFilepath),true);

    if (\is_null($lsJSONArray)) {
      throw new ModelImportException(
        $lsStepImport,
        "Interpretation JSON to Array en erreur : ".\json_last_error_msg().".",
        $this->_modelFilepath
      );
    } elseif ($lsJSONArray == FALSE) {
      throw new ModelImportException(
        $lsStepImport,
        "Transformation JSON to Array en erreur : ".\json_last_error_msg().".",
        $this->_modelFilepath
      );
    } else {

      $this->addLogModelImportMessage(
        $lsStepImport,
        "Transformation JSON en Array OK.",false
      );
      $this->_aModelContent = $lsJSONArray;
    }

    // --------------------------------------------------------------------
    // Vérification du modèle
    // --------------------------------------------------------------------
    $lsStepImport = 'Verification';
    $lbCheckModel = $this->checkModelContentValidity();

    if ($lbCheckModel) {
      // Log
      $this->addLogModelImportMessage($lsStepImport,'Contenu du model validé.');
    } else {
      throw new ModelImportException(
        $lsStepImport,
        "Le contenu du model est invalide.",
        $this->_modelFilepath
      );
    }

    $this->loadModelIntoDatabase();



  }//end importModelIntoDatabase()

  /**
   * [addLogModelValidationFieldExists description]
   * @param [type]  $iNiveau      [description]
   * @param [type]  $sPathToCheck [description]
   * @param [type]  $sFieldName   [description]
   * @param boolean $bIsMandatory [description]
   * @param boolean $bResultOk    [description]
   */
  protected function addLogModelValidationFieldExists($iNiveau,$sPathToCheck,$sFieldName,$bIsMandatory=false,$bResultOk=true)
  {
    $lsMsg = null;
    if ($bIsMandatory && $bResultOk ) {
      $lsMsg = "Niv. %02d - %s | Champ obligatoire '%s' existe.";
    } elseif ($bIsMandatory && !$bResultOk) {
      $lsMsg = "Niv. %02d - %s | Champ obligatoire '%s' n'existe pas.";
    } elseif (!$bIsMandatory && $bResultOk) {
      $lsMsg = "Niv. %02d - %s | Champ '%s' existe.";
    } elseif (!$bIsMandatory && !$bResultOk) {
      $lsMsg = "Niv. %02d - %s | Champ  '%s' n'existe pas.";
    }

    $this->addLogModelImportMessage(
      "CheckModel",
      sprintf($lsMsg,$iNiveau,$sPathToCheck,$sFieldName),
      $bResultOk
    );
  }//end addLogModelValidationFieldExists()

  /**
   * [addLogModelValidationFieldExists description]
   * @param [type]  $iNiveau      [description]
   * @param [type]  $sPathToCheck [description]
   * @param [type]  $sFieldName   [description]
   * @param boolean $bIsMandatory [description]
   * @param boolean $bResultOk    [description]
   */
  protected function addLogModelImportMessage($sActivity,$sMessage,$Result=true)
  {
    $liLenMax = 16;
    $liActLen = \strlen($sActivity);
    $liTemp  =  $liLenMax - $liActLen;

    if (($liTemp % 2) == 0) {
      $liAvant = ($liTemp/2)+$liActLen;
      $liApres = ($liTemp/2);
    } else {
      $liAvant = ($liTemp/2)+$liActLen-2;
      $liApres = ($liTemp/2)+3;
    }

    if($Result) {
      $liAvant2 = 4;
      $liApres2 = 1;
    } else {
      $liAvant2 = 5;
      $liApres2 = 0;
    }

    $lsMsg = "ModelImport [%".strval($liAvant)."s %".strval($liApres)."s][%".strval($liAvant2)."s %".strval($liApres2)."s] - %s";

    $this->_oLogger->addLog(
      $lsMsg,
      [$sActivity,'',($Result?"OK":"NOK"),'',$sMessage]
    );
  }//end addLogModelValidationFieldExists()

  /**
   * Chargement du modèle en base de données
   *
   * @return [type] [description]
   */
  protected function loadModelIntoDatabase()
  {
    // Log
    $this->addLogModelImportMessage(
        'ImpModel',
        "Démarrage de l'import du model."
    );

    // *************************************************************************
    // Gestion du Model
    // *************************************************************************

    // Model existe déjà ?
    $lsWhere = sprintf(
      "bid = UPPER('%s') AND version = UPPER('%s')",
      $this->_aModelContent['model']['code'],
      $this->_aModelContent['model']['version']
    );

    $loObjModel = new Model();
    $laResult   = $loObjModel->searchObjects($lsWhere);

    if (count($laResult)>0) {
      throw new ModelImportException(
        'ImpModel',
        sprintf(
          "Le modele (BID:'%s') en version '%s' existe deja.",
          $this->_aModelContent['model']['code'],
          $this->_aModelContent['model']['version']
        ),
        $this->_modelFilepath
      );
    }

    // Création du model en base!
    // -------------------------------------------------------------------------
    $laMappingModelfieldsToObjectField = [
      "code" => "Bid",
      "version" => "ModelVersion",
      "label" => "ModelLabel",
      "name" => "ModelName",
      "comment" => "Comment",
      "table_prefix" => "ModelTablePrefix",
      "bid_objd_prefix" => "ModelObjectDefinitionPrefix"
    ];

    $this->loadModelAttributesToObject(
      $this->_aModelContent['model'],
      $laMappingModelfieldsToObjectField,
      $loObjModel
    );

    // Insert as admin
    $loObjModel->setCreatorUid(1);
    $liMdlUid = $loObjModel->recordObject();

    // echo "Uid Mdl created => $liMdlUid";

    // log Mdl created!
    $this->addLogModelImportMessage(
        'ImpModel',
        sprintf(
          "Création du model '%s/%s' avec uid : %s.",
          $loObjModel->getFieldValue('Bid'),
          $loObjModel->getFieldValue('ModelVersion'),
          $loObjModel->getUid()
          )
    );

    // *************************************************************************
    // Gestion de la définition des Objets - Import OBD
    // *************************************************************************

    // Création des objets  en base!
    // -------------------------------------------------------------------------
    // Pour chacun des objets
    $laMappingModelfieldsToObjectField_ObjectDefinition = [
      "label" => "Label",
      "name" => "Name",
      "comment" => "Comment"
    ];

    // print_r($this->_aModelContent['model']['definitions']);

    foreach ($this->_aModelContent['model']['definitions']['objects'] as $key => $value) {
      $loObjDef = new ObjectDefinition();

      // TODO Gestion doublons

      $this->loadModelAttributesToObject(
        $value,
        $laMappingModelfieldsToObjectField_ObjectDefinition,
        $loObjDef
      );

      // Règles d'import spécifique!
      // -----------------------------------------------------------------------
      // Model UID!
      $loObjDef->setFieldValue('ModelUid',$loObjModel->getUid());

      // ObjectDef BID !
      $lsBID = sprintf(
        "%s.%s-OBJD_%s",
        $loObjModel->getFieldValue('ModelObjectDefinitionPrefix'),
        $loObjModel->getFieldValue('ModelVersion'),
        $value['code']
      );
      $loObjDef->setBid($lsBID);

      // ObjectDef isVersionable field!
      $liVersionable = ($value['isVersionable']?1:0);
      $loObjDef->setFieldValue('isVersionable',($value['isVersionable']?1:0));

      // ObjectDef internal fields definition!
      $lsBID_OBDPattern = sprintf(
        "%s-",
        $value['code']
      );
      $loObjDef->setFieldValue('ObjectBIDPattern',$lsBID_OBDPattern);

      $loObjDef->setFieldValue(
        'TableName',
        sprintf(
          "%s_%s",
          $loObjModel->getFieldValue('ModelTablePrefix'),
          \strtolower($loObjDef->getFieldValue('Label'))
        )
      );
      // Définition du nom de la vue SQL
      $loObjDef->setFieldValue(
        'ViewName',
        sprintf(
          "%svw_%s",
          $loObjModel->getFieldValue('ModelTablePrefix'),
          \strtolower($loObjDef->getFieldValue('Label'))
        )
      );

      $loObjDef->setCreatorUid(1);
      // Record
      $loObjDef->recordObject();

      // log Mdl created!
      $this->addLogModelImportMessage(
          'ObjImp',
          sprintf(
            "Création de la définition d'objet (BID:'%s') avec uid : %s - Label: '%s'.",
            $loObjDef->getFieldValue('Bid'),
            $loObjDef->getUid(),
            $loObjDef->getFieldValue('Label')
          )
      );

      // TODO Generation SQL
      // $loObjDef->setFieldValue('ViewSQLDefinition',$lsBID_OBDPattern);


      // *************************************************************************
      // Gestion de la définition des meta sur Objets - Import MOBD
      // *************************************************************************

      // Gestion des Attributs
      // -----------------------------------------------------------------------
      if (\array_key_exists('attributes',$value)) {

        // Pour chacun des attributs
        $laMappingModelfieldsToObjectField_ObjectMetaDefinition = [
          "label" => "Label",
          "name" => "Name",
          "comment" => "Comment",
          "title" => "MetaTitle",
          "type" => "MetaType",
          "pattern" => "MetaPattern",
          "options" => "MetaOptions",
          "sqlname" => "MetaSQLName",
          "sqlorder" => "MetaSQLOrder"
        ];

        foreach ($value['attributes'] as $key => $valueMeta) {
          $loMetaObjDef = new ObjectMetaDefinition();

          $this->loadModelAttributesToObject(
            $valueMeta,
            $laMappingModelfieldsToObjectField_ObjectMetaDefinition,
            $loMetaObjDef
          );

          // ModelUid !
          $loMetaObjDef->setFieldValue('ModelUid', $loObjModel->getUid());
          // ObjDef Uid !
          $loMetaObjDef->setFieldValue('ObjectDefinitionUid',$loObjDef->getUid());

          $lsMetaObjDefBid = sprintf(
            "%s_%s",
            $loObjDef->getBid(),
            $valueMeta['code']
          );
          $loMetaObjDef->setBid($lsMetaObjDefBid);

          // Record
          $loMetaObjDef->setCreatorUid(1);
          $loMetaObjDef->recordObject();

          // log Mdl created!
          $this->addLogModelImportMessage(
              'ObjMetaImp',
              sprintf(
                "Création de la définition de meta d'objet (BID:'%s') avec uid : %s - Label: '%s'.",
                $loMetaObjDef->getFieldValue('Bid'),
                $loMetaObjDef->getUid(),
                $loMetaObjDef->getFieldValue('Label')
              )
          );

        }//end foreach

      }//end if

    }//end foreach


    // *************************************************************************
    // Gestion de la définition des Liens entre objets - Import LNKD
    // *************************************************************************
    // Mapping between JSON model definition file and FieldName defined
    // on Link Object.
    $laMappingModelfieldsToObjectField_LinkDefinition = [
      "label" => "Label",
      "name" => "Name",
      "comment" => "Comment",
      "label" => "Label",
      "name" => "Name",
      "type" => "LinkType"
    ];

    // Iterate on LinkDefinitions !
    foreach ($this->_aModelContent['model']['definitions']['links'] as $key => $valueLnkDef)
    {
      $loLnkDef = new LinkDefinition();
      $this->loadModelAttributesToObject(
        $valueLnkDef,
        $laMappingModelfieldsToObjectField_LinkDefinition,
        $loLnkDef
      );
      // ObjectDef BID !
      $lsBID = sprintf(
        "%s.%s-LNKD_%s",
        $loObjModel->getFieldValue('ModelObjectDefinitionPrefix'),
        $loObjModel->getFieldValue('ModelVersion'),
        $valueLnkDef['code']
      );
      $loLnkDef->setBid($lsBID);
      $loLnkDef->setFieldValue(
        "LinkMultiple",
        ($valueLnkDef['isMultiple']?1:0)
      );
      $loLnkDef->setFieldValue(
        "LinkMandatory",
        ($valueLnkDef['isMandatory']?1:0)
      );
      $loLnkDef->setFieldValue(
        "LinkSQLView",
        sprintf(
          "%svwlnk_%s",
          $loObjModel->getFieldValue('ModelTablePrefix'),
          \strtolower($loLnkDef->getFieldValue('Label'))
        )
      );
      // Chargement de l'objet Source!
      $loObjDefSrc            = new ObjectDefinition();
      $laObjDefFounded        = null;
      $lsObjDefWhereCondition = sprintf(
        "bid_obj_pattern = '%s-'",
        $valueLnkDef['fatherCode']
      );

      $laObjDefFounded = $loObjDefSrc->searchObjects($lsObjDefWhereCondition);
      // OK ?
      if (count($laObjDefFounded)==1) {
        $loObjDefSrc = new ObjectDefinition($laObjDefFounded[0]['Uid']);

        // log ObjectDefintion founded created!
        $this->addLogModelImportMessage(
            'LinkImp',
            sprintf(
              "Identification de la définition d'objet source depuis son code '%s' => Résultat (BID:'%s'|UID:'%s').",
              $valueLnkDef['fatherCode'],
              $loObjDefSrc->getBid(),
              $loObjDefSrc->getUid()
            )
        );

        $loLnkDef->setFieldValue(
          "ObjDefUidSource",
          $loObjDefSrc->getUid()
        );
        $loLnkDef->setFieldValue(
          "ModelObjDefUidSource",
          $loObjModel->getUid()
        );
      }

      // Chargement de l'objet Target!
      $loObjDefTrg            = new ObjectDefinition();
      $laObjDefFounded        = null;
      $lsObjDefWhereCondition = sprintf(
        "bid_obj_pattern = '%s-'",
        $valueLnkDef['sonCode']
      );

      $laObjDefFounded = $loObjDefTrg->searchObjects($lsObjDefWhereCondition);
      // OK ?
      if (count($laObjDefFounded)==1) {
        $loObjDefTrg = new ObjectDefinition($laObjDefFounded[0]['Uid']);

        // log ObjectDefintion founded created!
        $this->addLogModelImportMessage(
            'LinkImp',
            sprintf(
              "Identification de la définition d'objet target depuis son code '%s' => Résultat (BID:'%s'|UID:'%s').",
              $valueLnkDef['sonCode'],
              $loObjDefTrg->getBid(),
              $loObjDefTrg->getUid()
            )
        );

        // Définition des Objets Sources et Target sur l'objet Liens !
        // ---------------------------------------------------------------------
        $loLnkDef->setFieldValue(
          "ObjDefUidTarget",
          $loObjDefTrg->getUid()
        );
        $loLnkDef->setFieldValue(
          "ModelObjDefUidTarget",
          $loObjModel->getUid()
        );
      }//end if

      // Record link Object !
      // ---------------------------------------------------------------------
      $loLnkDef->setCreatorUid(1);
      $loLnkDef->recordObject();

      // log Mdl created!
      $this->addLogModelImportMessage(
          'ObjMetaImp',
          sprintf(
            "Création de la définition du liens entre objet (BID:'%s') avec uid : %s - Label: '%s'.",
            $loLnkDef->getFieldValue('Bid'),
            $loLnkDef->getUid(),
            $loLnkDef->getFieldValue('Label')
          )
      );

      // *************************************************************************
      // Gestion de la définition des meta sur lien - Import MLND
      // *************************************************************************

      // Gestion des Attributs (json idx : attributes)
      // -----------------------------------------------------------------------
      if (\array_key_exists('attributes',$valueLnkDef)) {
        // Mapinng betweenmodel defintion and Object field!
        $laMappingModelfieldsToObjectField_LnkMetaDefinition = [
          "label" => "Label",
          "name" => "Name",
          "comment" => "Comment",
          "title" => "LinkMetaTitle",
          "type" => "LinkMetaType",
          "pattern" => "LinkMetaPattern",
          "options" => "LinkMetaOptions",
          "sqlname" => "LinkMetaSQLName",
          "sqlorder" => "LinkMetaSQLOrder"
        ];

        // Pour chacun des attribut|meta de liens!
        foreach ($valueLnkDef['attributes'] as $key => $valueMeta) {
          $loMetaLnkDef = new LinkMetaDefinition();

          $this->loadModelAttributesToObject(
            $valueMeta,
            $laMappingModelfieldsToObjectField_LnkMetaDefinition,
            $loMetaLnkDef
          );

          // ModelUid & LinkDef Uid !
          $loMetaLnkDef->setFieldValue('LinkDefinitionUid',$loLnkDef->getUid());

          // BID Building ... !
          $lsMetaLnkDefBid = sprintf(
            "%s_%s",
            $loLnkDef->getBid(),
            $valueMeta['code']
          );
          $loMetaLnkDef->setBid($lsMetaLnkDefBid);

          // Record into DB!
          $loMetaLnkDef->setCreatorUid(1);
          $loMetaLnkDef->recordObject();

          // log Meta on Link created!
          $this->addLogModelImportMessage(
              'LnkMetaImp',
              sprintf(
                "Création de la définition de meta sur lien (BID:'%s') avec uid : %s - Label: '%s'.",
                $loMetaLnkDef->getFieldValue('Bid'),
                $loMetaLnkDef->getUid(),
                $loMetaLnkDef->getFieldValue('Label')
              )
          );
        }//end foreach
      }//end if
    }//end foreach

    // Builds Views definitions and deploy update into database!
    ModelManager::updateModelObjectDefinitionView($loObjModel);
    ModelManager::updateModelLinksDefinitionView();
    ModelManager::deployModelToDatabase($loObjModel);

  }//end loadModelIntoDatabase()


  protected function deployModelStructureIntoDatabase(Model $oModel)
  {

  }//end deployModelStructureIntoDatabase

  /**
   * Chargement des attributs dans un objet (DatabaseObject)
   *
   * @param  array          $aContentToLoad             [description]
   * @param  array          $aMappingModelFieldObjField [description]
   * @param  DatabaseObject $oObject                    [description]
   */
  protected function loadModelAttributesToObject($aContentToLoad,$aMappingModelFieldObjField,$oObject):void
  {
    if (!($oObject instanceof DatabaseObject)) {
      return;
    }

    foreach ($aMappingModelFieldObjField as $key => $value) {
      if (\array_key_exists($key,$aContentToLoad)) {
        $oObject->setFieldValue($value,$aContentToLoad[$key]);
      }
    }
  }//end loadModelAttributesToObject()

}//end class

?>
