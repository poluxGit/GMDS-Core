<?php
include_once './../vendor/autoload.php';

use GMDS\Core\CoreManager;
use GMDS\Core\Model\Model;
use GMDS\Core\Model\ObjectDefinition;
use GMDS\Core\Tools\ModelImporter;
use GMDS\Core\Data\Business\BusinessDataFactory;

CoreManager::loadSystemSettingsFromJSONFile('./../tests/test-settings/app-settings-local.valid.json');
CoreManager::setApplicationLoggerFilepath('./../logs/logApplication.log');
CoreManager::setApplicationDatabaseLoggerFilepath('./../logs/logDBQueries.log');
CoreManager::initialize();
CoreManager::deployDatabaseSchemaIntoDatabase('maindb');
// ApplicationManager::loadApplicationSettingsFromFile('./../tests/test-settings/application-local.valid.json');
// ApplicationManager::setApplicationLoggerFilepath('./../logs/logApplication.log');
// ApplicationManager::setApplicationDatabaseLoggerFilepath('./../logs/logDBQueries.log');

// ApplicationManager::initializeApplication();
//
//
// // JEUX TEST N°5 - Import
// // -----------------------------------------------------------------------------
//  // $loImportObj = new ModelImporter('./_dev/ecm-model.json');
//  // $loImportObj->importModelIntoDatabase();
//
//  // JEUX TEST N°7 - Création d'un objet Business N°2
//  // -----------------------------------------------------------------------------
//  $lsObjetLabel = 'TypeDoc';
//  $liMdlUid = 1;
//  $laFieldsValues = [
//    'Label'     => 'Facture',
//    'Version'   => 'A',
//    'Revision'  => 1,
//    'Name'      => 'Factures diverses',
//    'Comment'   => 'Tous types de factures',
//    'Bid'       => 'TDOC-FACT'
//  ];
//  $loObj = BusinessDataFactory::createNewBusinessObjectFromLabelAndModel(
//    $lsObjetLabel,
//    $laFieldsValues,
//    $liMdlUid
//  );
//
//  print_r($loObj->recordObject());
//  $loObj->setBusinessMetaValue(1,'FACT');
//
//  // JEUX TEST N°7 - Création d'un objet Business N°2
//  // -----------------------------------------------------------------------------
//  $lsObjetLabel = 'TypeDoc';
//  $liMdlUid = 1;
//  $laFieldsValues = [
//    'Label'     => 'Info',
//    'Version'   => 'A',
//    'Revision'  => 0,
//    'Name'      => 'Informations',
//    'Comment'   => 'Informations',
//    'Bid'       => 'TDOC-INFO'
//  ];
//  $loObj = BusinessDataFactory::createNewBusinessObjectFromLabelAndModel(
//    $lsObjetLabel,
//    $laFieldsValues,
//    $liMdlUid
//  );
//
//  print_r($loObj->recordObject());
//  $loObj->setBusinessMetaValue(1,'INFO');
//
//
//  // JEUX TEST N°7 - Création d'un objet Business N°2
//  // -----------------------------------------------------------------------------
//  $lsObjetLabel = 'Document';
//  $liMdlUid = 1;
//  $laFieldsValues = [
//    'Label'     => 'Facture-Elec-2',
//    'Version'   => 'C',
//    'Revision'  => 10,
//    'Name'      => 'Facture Electricté Test N°2',
//    'Comment'   => 'Facture Electricté Test du mois d octobre.',
//    'Bid'       => 'DOC-FACT-EDF_02'
//  ];
//  $loObj = BusinessDataFactory::createNewBusinessObjectFromLabelAndModel(
//    $lsObjetLabel,
//    $laFieldsValues,
//    $liMdlUid
//  );
//
//  print_r($loObj->recordObject());
//  $loObj->setBusinessMetaValue(2,2019);
//  $loObj->setBusinessMetaValue(3,10);
//  $loObj->setBusinessMetaValue(4,6);
//  //print_r($loObj);
//
//
//
// // JEUX TEST N°6 - Création d'un objet Business
// // -----------------------------------------------------------------------------
// $lsObjetLabel = 'Document';
// $liMdlUid = 1;
// $laFieldsValues = [
//   'Label'     => 'DocTest01',
//   'Version'   => 'A',
//   'Revision'  => 1,
//   'Name'      => 'Nom Long du Document',
//   'Comment'   => 'Jeux de tests N1',
//   'Bid'       => 'D-TEST_01'
// ];
// $loObj = BusinessDataFactory::createNewBusinessObjectFromLabelAndModel(
//   $lsObjetLabel,
//   $laFieldsValues,
//   $liMdlUid
// );
//
// print_r($loObj->recordObject());
// $loObj->setBusinessMetaValue(2,2019);
// //print_r($loObj);
//
//
// // Jeux TEST N°4 - Logger
// // -----------------------------------------------------------------------------
//
// // ApplicationManager::getApplicationDefaultLogger()->addLog('titi %s',['tata']);
// // $loObj = new GDTA\Core\Model\ObjectDefinition(1);
//
//
// // -----------------------------------------------------------------------------
//
//
// // JEUX TEST N°3 - Création d'objet definition
// // -----------------------------------------------------------------------------
// // $loObj = new GDTA\Core\Model\ObjectDefinition(null);
// //
// // $loObj->setFieldValue('ModelUid',1);
// // $loObj->setFieldValue('ObjectBIDPattern','CAT-%s');
// // $loObj->setFieldValue('TableName','e1000_categorie');
// // $loObj->setFieldValue('ViewName','vw1000_categorie');
// // $loObj->setFieldValue('ViewSQLDefinition','SQL to def');
// // $loObj->setLabel('Categorie');
// // $loObj->setName('CATEGORIE');
// // $loObj->setCreatorUid(1);
// // $loObj->setBid('ECM_CAT.09');
// //
// // print_r($loObj);
// //
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
