<?php

namespace GDTA\Core;

use GDTA\Core\Model\Model;
use GDTA\Core\Model\ObjectDefinition;
use GDTA\Core\Model\ObjectMetaDefinition;
use GDTA\Core\Model\LinkDefinition;
use GDTA\Core\Model\LinkMetaDefinition;

/**
 * ModelManager - Gestion d'un model de données
 *
 * @static
 */
class ModelManager
{
  /**
   * Collection des modèles chargés
   *
   * @static
   * @var array
   */
  private static $_aModels = [];

  /**
   * Chargement de la définition du model depuis un fichier
   *
   * @param  [type] $sFilepath [description]
   * @return [type]            [description]
   */
  public function loadModelFromFile($sFilepath)
  {

  }//end loadModelFromFile()

  /**
   * [_checkDataValidity description]
   * @param  [type] $aModelData [description]
   * @return [type]             [description]
   */
  protected function _checkDataStructureValidity($aModelData)
  {

  }//end _checkDataValidity()


  /**
   * Mise à jour de la définition des vues des définition d'objets
   *
   * @param  Model  $oModel   Modèle concerné
   * @return [type]         [description]
   */
  public static function updateModelObjectDefinitionView(Model $oModel)
  {
    $loObjDef = new ObjectDefinition();
    $laObjDef = null;
    $lsWhereCondition = " uid_mdl = ".strval($oModel->getUid());
    $laObjDef = $loObjDef->searchObjects($lsWhereCondition);

    // Par Objet
    foreach ($laObjDef as $key => $value)
    {
      $loObjDef =  new ObjectDefinition(intval($value['Uid']));

      $laSelect = [];
      $laFROM = [];
      $laWHERE = [];

      $liCpt = 1;

      $laSelectDef = [
        "Uid" => "uid",
        "Bid" => "bid",
        "Version" => "vers",
        "Revision" => "rev",
        "Label" => "label",
        "Name" => "name",
        "Comment" => "comment",
        "JSONAddData" => "jsondata"
      ];

      // add main table -  i.e ObjectDef!
      \array_push($laFROM,$loObjDef->getFieldValue('TableName').' tobj');

      // SELECT Part!
      foreach ($laSelectDef as $keyFieldDef => $valueFieldDef) {
        if ($keyFieldDef != 'Creator' && $keyFieldDef != 'Updater' && $keyFieldDef != 'CreationDate' && $keyFieldDef != 'LastUpdate' && $keyFieldDef != 'ModelUid') {
          \array_push($laSelect,"tobj.$valueFieldDef AS $keyFieldDef");
        }
      }

      // Pour chacun des attributs (meta)!
      $loObjMetaDef = new ObjectMetaDefinition();
      $lsWhereMeta = sprintf(
        "uid_objd = %s AND uid_mdl = %s",
        $loObjDef->getUid(),
        $loObjDef->getFieldValue('ModelUid')
      );
      $laObjMetaDef = $loObjMetaDef->searchObjects($lsWhereMeta);

      // Pour chacun des metas
      foreach ($laObjMetaDef as $key => $value) {
        $loObjMetaDef = new ObjectMetaDefinition(intval($value['Uid']));

        \array_push(
          $laSelect,
          "JSON_EXTRACT(tmobj$liCpt.value,\"$.value\") AS ".$loObjMetaDef->getFieldValue('MetaSQLName')
        );

        \array_push(
          $laFROM,
          "LEFT JOIN a1000_objects tobjg$liCpt ON (tobjg$liCpt.uid_obj = tobj.uid AND tobjg$liCpt.uid_objd = ".$loObjDef->getUid().") LEFT JOIN a1000_objects_meta tmobj$liCpt ON (tmobj$liCpt.uid_obj = tobjg$liCpt.uid AND tmobj$liCpt.uid_mobd = ".$loObjMetaDef->getUid()." )"
        );
        $liCpt++;
      }

      // Creation & Update fields !
      \array_push($laSelect,"tusrc.label AS CreatorName");
      \array_push($laSelect,"tobj.cdate AS CreationDate");
      \array_push($laSelect,"tusru.label AS UpdaterName");
      \array_push($laSelect,"tobj.udate AS LastUpdate");
      \array_push($laFROM,'LEFT JOIN z0000_users tusrc ON tusrc.uid = tobj.cuser');
      \array_push($laFROM,'LEFT JOIN z0000_users tusru ON tusru.uid = tobj.uuser');

      $lSQLViewDef = sprintf(
        "SELECT %s FROM %s",
        implode(', ',$laSelect),
        implode('  ',$laFROM)
      );

      $loObjDef->setFieldValue('ViewSQLDefinition',$lSQLViewDef);
      $loObjDef->recordObject();

    }//end foreach

  }//end updateModelObjectDefinitionView()


  /**
   * Mise à jour de la définition des vues des définition de liens
   *
   * @param  Model  $oModel   Modèle concerné
   * @return [type]         [description]
   */
  public static function updateModelLinksDefinitionView()
  {
    $loLnkDef = new LinkDefinition();
    $laLnkDef = null;

    // FIXME Mise à jour globale ...
    // $lObjDefSrc = new ObjectDefinition();
    // $lObjDefTar = new ObjectDefinition();
    // $lsWhereCondition = " uid_mdl = ".strval($oModel->getUid());

    $laLnkDef = $loLnkDef->searchObjects(' 1 = 1 ');

    // Pour tous les liens, par lien !
    foreach ($laLnkDef as $key => $value)
    {
      $loLnkDef =  new LinkDefinition(intval($value['Uid']));

      // SQL View necessary fields to generate View definition!
      $laSelect = [];
      $laFROM = [];
      $laWHERE = [];
      $liCpt = 1;

      $laSelectDef = [
        "Uid" => "uid",
        "Bid" => "bid_lnk",
        "LnkDef" => "uid_lnkd",
        "UIDObjSrc" => "uid_obj_source",
        "UIDObjTrg" => "uid_obj_target"
      ];

      // add main table -  i.e LinkDef!
      \array_push($laFROM,' a1000_links tlnk ');
      \array_push($laFROM,' LEFT JOIN a1000_objects tobjsrc ON (tobjsrc.uid = tlnk.uid_obj_source) ');
      \array_push($laFROM,' LEFT JOIN a0000_def_objects tobdsrc ON (tobdsrc.uid = tobjsrc.uid_objd) ');
      \array_push($laFROM,' LEFT JOIN a1000_objects tobjtrg ON (tobjtrg.uid = tlnk.uid_obj_target) ');
      \array_push($laFROM,' LEFT JOIN a0000_def_objects tobdtrg ON (tobdtrg.uid = tobjsrc.uid_objd) ');


      // SELECT Part!
      foreach ($laSelectDef as $keyFieldDef => $valueFieldDef) {
        if ($keyFieldDef != 'Creator' && $keyFieldDef != 'Updater' && $keyFieldDef != 'CreationDate' && $keyFieldDef != 'LastUpdate' ) {
          \array_push($laSelect,"tlnk.$valueFieldDef AS $keyFieldDef");
        }
      }

      // Fields about Objects (Source and target)!
      \array_push($laSelect,"tobjsrc.bid_obj AS ObjSrcBID ");
      \array_push($laSelect,"tobjsrc.ver_obj AS ObjSrcVersion");
      \array_push($laSelect,"tobjsrc.rev_obj AS ObjSrcRevision");
      \array_push($laSelect,"tobdsrc.label AS ObjSrcLabel");
      \array_push($laSelect,"tobdsrc.table_name AS ObjSrcTableName");
      \array_push($laSelect,"tobdsrc.view_name AS ObjSrcViewName");

      \array_push($laSelect,"tobjtrg.bid_obj AS ObjTrgBID ");
      \array_push($laSelect,"tobjtrg.ver_obj AS ObjTrgVersion");
      \array_push($laSelect,"tobjtrg.rev_obj AS ObjTrgRevision");
      \array_push($laSelect,"tobdtrg.label AS ObjTrgLabel");
      \array_push($laSelect,"tobdtrg.table_name AS ObjTrgTableName");
      \array_push($laSelect,"tobdtrg.view_name AS ObjTrgViewName");

      // Pour chacun des attributs (meta) du lien !
      $loLnkMetaDef = new LinkMetaDefinition();
      $lsWhereMeta = sprintf(
        "uid_lnkd = %s ",
        $loLnkDef->getUid()
      );
      $laLnkMetaDef = $loLnkMetaDef->searchObjects($lsWhereMeta);

      // Pour chacun des metas sur lien
      foreach ($laLnkMetaDef as $key => $value) {
        $loLnkMetaDef = new LinkMetaDefinition(intval($value['Uid']));

        \array_push(
          $laSelect,
          "JSON_EXTRACT(tmlnd$liCpt.value,\"$.value\") AS ".$loLnkMetaDef->getFieldValue('LinkMetaSQLName')
        );

        \array_push(
          $laFROM,
          "LEFT JOIN a1000_links_meta tmlnd$liCpt ON (tmlnd$liCpt.uid_lnk = tlnk.uid AND tmlnd$liCpt.uid_lnkd = ".$loLnkDef->getUid()." )"
        );
        $liCpt++;
      }

      // Creation & Update fields !
      \array_push($laSelect,"tusrc.label AS CreatorName");
      \array_push($laSelect,"tlnk.cdate AS CreationDate");
      \array_push($laSelect,"tusru.label AS UpdaterName");
      \array_push($laSelect,"tlnk.udate AS LastUpdate");
      \array_push($laFROM,'LEFT JOIN z0000_users tusrc ON tusrc.uid = tlnk.cuser');
      \array_push($laFROM,'LEFT JOIN z0000_users tusru ON tusru.uid = tlnk.uuser');

      $lSQLViewDef = sprintf(
        "SELECT %s FROM %s",
        implode(', ',$laSelect),
        implode('  ',$laFROM)
      );

      $loLnkDef->setFieldValue('LinkSQLViewDefintion',$lSQLViewDef);
      $loLnkDef->recordObject();

    }//end foreach

  }//end updateModelLinksDefinitionView()

  /**
   * Deploiement de la structure SQL d'un model en base de données
   *
   * @param  Model  $oModel   Modèle concerné
   * @return [type]         [description]
   */
  public static function deployModelToDatabase(Model $oModel)
  {
    $loObjDef = new ObjectDefinition();
    $laObjDef = null;
    $lsWhereCondition = " uid_mdl = ".strval($oModel->getUid());
    $laObjDef = $loObjDef->searchObjects($lsWhereCondition);

    // Pour chacun des objets
    foreach ($laObjDef as $key => $value) {
      $loObjDef =  new ObjectDefinition(intval($value['Uid']));

      // Smarty template usage!
      $loSmarty = new \Smarty();
      $loSmarty->assign('ObjectDefinitionTableName',$loObjDef->getFieldValue('TableName'));
      $loSmarty->assign('ObjectDefinitionCode',\str_replace('-','',$loObjDef->getFieldValue('ObjectBIDPattern')));

       //$lsPath = realpath(dirname(__FILE__).'./../../logs');

      if ($loObjDef->getFieldValue('isVersionable')) {
        $lsSQLScript = $loSmarty->fetch('file:F:/projects/gdta-dev/sources/ressources/SQL-CREATE_COMPLEX_OBJ.sql.tpl');
      } else {
        $lsSQLScript = $loSmarty->fetch(
          'file:F:/projects/gdta-dev/sources/ressources/SQL-CREATE_SIMPLE_OBJ.sql.tpl'
        );
      }

      // Création table et triggers!
      ApplicationManager::getDefaultDatabaseConnection()->execScript($lsSQLScript);

      // CREATE / UPDATE SQL Object View
      // -----------------------------------------------------------------------
      $lsSQLViewDef = $loObjDef->getSQLViewCreationOrder();
      ApplicationManager::getDefaultDatabaseConnection()->execScript($lsSQLViewDef);
    }//end foreach

    // LINK
    $loLnkDef = new LinkDefinition();
    $laLnkDef = null;
    $laLnkDef = $loLnkDef->searchObjects('1=1');

    // Pour chacun des objets
    foreach ($laLnkDef as $key => $value) {
      $loLnkDef =  new LinkDefinition(intval($value['Uid']));
      // CREATE / UPDATE SQL Link View
      // -----------------------------------------------------------------------
      $lsSQLViewDef = $loLnkDef->getSQLViewCreationOrder();
      ApplicationManager::getDefaultDatabaseConnection()->execScript($lsSQLViewDef);
    }//end foreach

  }//end deployModelToDatabase()

}//end class

?>
