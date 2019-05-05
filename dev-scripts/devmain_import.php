<?php
include_once 'vendor/autoload.php';

use GDTA\Core\ApplicationManager;
use GDTA\Core\Model\Model;
use GDTA\Core\Model\ObjectDefinition;
use GDTA\Core\Tools\ModelImporter;
use GDTA\Core\Data\Business\BusinessDataFactory;

ApplicationManager::loadApplicationSettingsFromFile('./exec/application-local.json');
ApplicationManager::setApplicationLoggerFilepath('./logs/logApplication.log');
ApplicationManager::setApplicationDatabaseLoggerFilepath('./logs/logDBQueries.log');

ApplicationManager::initializeApplication();


// JEUX TEST N°5 - Import
// -----------------------------------------------------------------------------
$loImportObj = new ModelImporter('./_dev/ecm-model-v1.json');
$loImportObj->importModelIntoDatabase();


// Jeux TEST N°4 - Logger
// -----------------------------------------------------------------------------

// ApplicationManager::getApplicationDefaultLogger()->addLog('titi %s',['tata']);
// $loObj = new GDTA\Core\Model\ObjectDefinition(1);


// -----------------------------------------------------------------------------


// JEUX TEST N°3 - Création d'objet definition
// -----------------------------------------------------------------------------
// $loObj = new GDTA\Core\Model\ObjectDefinition(null);
//
// $loObj->setFieldValue('ModelUid',1);
// $loObj->setFieldValue('ObjectBIDPattern','CAT-%s');
// $loObj->setFieldValue('TableName','e1000_categorie');
// $loObj->setFieldValue('ViewName','vw1000_categorie');
// $loObj->setFieldValue('ViewSQLDefinition','SQL to def');
// $loObj->setLabel('Categorie');
// $loObj->setName('CATEGORIE');
// $loObj->setCreatorUid(1);
// $loObj->setBid('ECM_CAT.09');
//
// print_r($loObj);
//
// //$loObj->setFieldValue('ModelVersion','DEV02');
// $liUID = $loObj->recordObject();
//
// print_r($loObj);

// -----------------------------------------------------------------------------

// JEUX TEST N°2 - Test Default dabase ref
// -----------------------------------------------------------------------------
// $loObj = new GDTA\Core\Model\Model(null);
// print_r($loObj);
//
// $loObj->setFieldValue('ModelVersion','DEV02');
// $liUID = $loObj->recordObject();
//
// print_r($loObj);

// -----------------------------------------------------------------------------


// JEUX TEST N°1 - Maj model
// -----------------------------------------------------------------------------
// $loObj = new GDTA\Core\Model\Model(1);
// echo "NeedUpdate : ".$loObj->needAnUpdate()."\n";
//
// $loObj->setFieldValue('ModelName','TOTA');
//
// echo "NeedUpdate : ".$loObj->needAnUpdate()."\n";
// print_r($loObj);
//
// echo "NBRows :".$loObj->updateObjectInDatabase();
// print_r($loObj);
// $loObj->setFieldValue('ModelName','Modèle ECM Générique de DEV (01)');
// print_r($loObj);
// echo "NBRows :".$loObj->updateObjectInDatabase();
// -----------------------------------------------------------------------------


// JEUX TEST N°2 - Test Default dabase ref
// -----------------------------------------------------------------------------
//print_r(ApplicationManager::getDefaultDatabaseConnection());
// echo ApplicationManager::getAppName();
// -----------------------------------------------------------------------------

?>
