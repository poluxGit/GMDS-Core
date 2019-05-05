<?php

namespace GDTA\Core\Data\Business;

use GDTA\Core\Data\Database\DatabaseObject;
use GDTA\Core\Data\Database\DatabaseHandler;
use GDTA\Core\Model\ObjectDefinition;
use GDTA\Core\Model\ObjectMetaDefinition;
use GDTA\Core\Model\LinkMetaDefinition;
use GDTA\Core\Model\LinkDefinition;
use GDTA\Core\Model\Model;

use GDTA\Core\Exception\BusinessObjectException;
use GDTA\Core\Exception\LinkObjectException;

/**
 * BusinessDataFactory - Gestionnaire d'objet Business
 */
class BusinessDataFactory
{
  private static $_oDBHandler = null;

  //////////////////////////////////////////////////////////////////////////////
  // Méthodes statiques publiques
  //////////////////////////////////////////////////////////////////////////////
  /**
   * Chargement d'une version/revision spécifique d'un objet Business
   *
   * @param  string $sBid       BID de l'objet
   * @param  string $sVersion   Version de l'objet
   * @param  int    $iRevision  Revision de l'objet
   * @return BusinessObject     BusinessObject trouvé.
   */
  public static function getBusinessObjectSpecificVersionRevision($sBid, $sVersion, $iRevision):BusinessObject
  {
    $lObjReturned     = null;
    $lBusinessObjTmp  = new InternalObject();
    $lsCondition      = sprintf(
      " bid_obj = '%s' AND ver_obj = '%s' AND rev_obj = %s ",
      $sBid,
      $sVersion,
      $iRevision
    );
    $laBusinessObjects = $lBusinessObjTmp->searchObjects($lsCondition);

    if (count($laBusinessObjects) == 1) {
      $lObjReturned =  new BusinessObject($laBusinessObjects[0]['uid_obj']);
    }
    return $lObjReturned;
  }//end getBusinessObjectSpecificVersionRevision()

  /**
   * Chargement de la dernière révision de l'objet Business pour une version données
   *
   * @param  string $sBid      [description]
   * @param  string $sVersion  [description]
   * @return BusinessObject    [description]
   */
  public static function getBusinessObjectLastestRevision($sBid, $sVersion):BusinessObject
  {
    $lObjReturned = null;
    return $lObjReturned;
  }//end loadBusinessObjectFromBid()

  /**
   * Retourne une definition d'objet depuis son Uid
   *
   * @static
   * @param  int $iUid [description]
   * @return ObjectDefinition       [description]
   */
  public static function getObjectDefinitionFromUid($iUid):ObjectDefinition
  {
    $lObjReturned = null;
    $lObjReturned = new ObjectDefinition($iUid);
    return $lObjReturned;
  }//end getObjectDefinitionFromUid()

  /**
   * Retourne une definition de meta d'objet depuis son Uid
   *
   * @static
   * @param  int $iUid [description]
   * @return ObjectMetaDefinition       [description]
   */
  public static function getObjectMetaDefinitionFromUid($iUid):ObjectMetaDefinition
  {
    $lObjReturned = null;
    $lObjReturned = new ObjectMetaDefinition($iUid);
    return $lObjReturned;
  }//end getObjectMetaDefinitionFromUid()

  /**
   * Retourne une definition de meta de lien entre objet d'objet depuis son Uid
   *
   * @static
   * @param  int $iUid [description]
   * @return LinkMetaDefinition       [description]
   */
  public static function getLinkMetaDefinitionFromUid($iUid):LinkMetaDefinition
  {
    $lObjReturned = null;
    $lObjReturned = new LinkMetaDefinition($iUid);
    return $lObjReturned;
  }//end getLinkMetaDefinitionFromUid()

  /**
   * Retourne une definition de lien entre objet d'objet depuis son Uid
   *
   * @static
   * @param  int $iUid [description]
   * @return LinkDefinition       [description]
   */
  public static function getLinkDefinitionFromUid($iUid):LinkDefinition
  {
    $lObjReturned = null;
    $lObjReturned = new LinkDefinition($iUid);
    return $lObjReturned;
  }//end getLinkDefinitionFromUid()

  /**
   * Retourne un BusinessObject depuis son Label et son uid
   *
   * @throws BusinessObject Type d'objet non trouvé
   * @throws BusinessObject Instance d'objet non trouvé
   *
   * @param  string   $sLabel   Label de l'objet
   * @param  int      $iObjUid  Uid
   * @param  int      $iMdlUid  Modele UID
   * @return BusinessObject     Objet trouvé
   */
  public static function getBusinessObjectFromLabelAndUid($sLabel,$iObjUid,$iMdlUid):BusinessObject
  {
    // Search Object Definition from internal model
    $loObjDef = new ObjectDefinition();
    $laObjectFounded = $loObjDef->searchObjects(" UPPER(label) = UPPER('$sLabel') and uid_mdl = $iMdlUid ");

    // Only one founded?
    if (count($laObjectFounded) == 1) {
      $loObjDef = new ObjectDefinition($laObjectFounded[0]['Uid']);
    } else {
      throw new BusinessObjectException(
        \sprintf(
          "Le type d'objet '%s' a charger n'as pas ete trouve ! (MDL_UID:%s)",
          $sLabel,
          strval($iMdlUid)
          )
      );
    }//end if

    $loBusinessObj = null;
    if (!\is_null($loObjDef)) {
      $loBusinessObj = new BusinessObject(
        $iObjUid,
        $loObjDef->getFieldValue('TableName')
      );
      if (\is_null($loBusinessObj->getUid()))
      {
        throw new BusinessObjectException(
          \sprintf(
            "L'objet (UID:%s) de type '%s' n'as pas ete trouve ! (MDL_UID:%s|Tablename:%s)",
            $iObjUid,
            $sLabel,
            strval($iMdlUid),
            $loObjDef->getFieldValue('TableName')
            )
        );
      }
    }
    return $loBusinessObj;
  }//end getBusinessObjectFromLabelAndUid()

  /**
   * Retourne toutes les instances d'objet de la classe Business
   * @param  [type]         $sLabel  [description]
   * @param  [type]         $iObjUid [description]
   * @param  [type]         $iMdlUid [description]
   * @return array          [description]
   */
  public static function getAllBusinessObjectFromLabel($sLabel,$iMdlUid)
  {
    $laResult = [];
    // Search Object Definition from internal model
    $loObjDef = new ObjectDefinition();
    $laObjectFounded = $loObjDef->searchObjects(" UPPER(label) = UPPER('$sLabel') and uid_mdl = $iMdlUid ");

    // Only one founded?
    if (count($laObjectFounded) == 1) {
      $loObjDef = new ObjectDefinition($laObjectFounded[0]['Uid']);
    } else {
      throw new BusinessObjectException(
        \sprintf(
          "Le type d'objet '%s' a charger n'as pas ete trouve ! (MDL_UID:%s)",
          $sLabel,
          strval($iMdlUid)
          )
      );
    }//end if

    $loBusinessObj = null;
    if (!\is_null($loObjDef)) {
      $loBusinessObj = new GenericSimpleObject(
        $loObjDef->getFieldValue('ViewName')
      );
      $laResult = $loBusinessObj->getAllRows();

    }
    return $laResult;
  }//end getAllBusinessObjectFromLabel()



  /**
   * Retourne toutes les définition demeta sur objet
   *
   * @param  [type]         $sLabel  [description]
   * @param  [type]         $iObjUid [description]
   * @param  [type]         $iMdlUid [description]
   * @return array          [description]
   */
  public static function getAllObjectMetaDefinitionForModel($iMdlUid)
  {
    $laResult = [];
    $loModel  = new Model($iMdlUid);

    // For all ObjectDefinition of model -> get Object MetaDefinition !
    foreach ($loModel->getAllObjectDefinitions() as $value) {
      $laResult = \array_merge(
        $laResult,
        ObjectMetaDefinition::getObjectMetaDefinitionForObjectDefinition($value['Uid']));
    }
    return $laResult;
  }//end getAllObjectMetaDefinitionForModel()


  /**
   * Retourne toutes les définition de meta sur liens entre objet
   *
   * @param  [type]         $sLabel  [description]
   * @param  [type]         $iObjUid [description]
   * @param  [type]         $iMdlUid [description]
   * @return array          [description]
   */
  public static function getAllLinkMetaDefinitionForModel($iMdlUid)
  {
    $laResult = [];
    $loModel  = new Model($iMdlUid);

    // For all LinksDefinition of model -> get Link Meta Definition !
    foreach ($loModel->getAllLinkDefinitions() as $value) {
      $laResult = \array_merge(
        $laResult,
        LinkMetaDefinition::getLinkMetaDefinitionForLinkDefinition($value['Uid']));
    }
    return $laResult;
  }//end getAllLinkMetaDefinitionForModel()


  /**
   * Retourne l'instance de meta données sur objet
   *
   * @param  int    $iUidObj              UID de l'objet 'interne'
   * @param  int    $iUidObjMetaDef       UID de la définition de meta d'objet
   * @return BusinessObjectMeta Instance de valeur.
   */
  public static function getBusinessMetaObjectFromObjectAndMetaDef($iUidObj,$iUidObjMetaDef)
  {
    $loObj = new BusinessObjectMeta();
    $laObj = $loObj->searchObjects(
      sprintf(
        " uid_obj = %s AND uid_mobd = %s ",
        $iUidObj,
        $iUidObjMetaDef
      )
    );
    $loObjResult = null;

    if (count($laObj)>0) {
      $loObjResult = new BusinessObjectMeta($laObj[0]['Uid']);
    }
    return $loObjResult;
  }//end getBusinessMetaObjectFromObjectAndMetaDef()

  /**
   * Retourne l'instance de meta données sur lien entre objet business
   *
   * @param  int    $iUidLnk              UID du lien
   * @param  int    $iUidLnkMetaDef       UID de la définition de meta sur lien
   * @return LinkObjectMeta   Instance de valeur.
   */
  public static function getBusinessMetaLinkFromLinkAndMetaDef($iUidLnk,$iUidLnkMetaDef)
  {
    $loObj = new LinkObjectMeta();
    $laObj = $loObj->searchObjects(
      sprintf(
        " uid_lnk = %s AND uid_lnkd = %s ",
        $iUidLnk,
        $iUidLnkMetaDef
      )
    );
    $loObjResult = null;

    if (count($laObj)>0) {
      $loObjResult = new LinkObjectMeta($laObj[0]['Uid']);
    }
    return $loObjResult;
  }//end getBusinessMetaLinkFromLinkAndMetaDef()

  /**
   * Creer une instance d'objet BusinessObjet
   *
   * @internal ObjectNotRegsitered in Database.
   * @throws BusinessObject Type d'objet non trouvé
   * @throws BusinessObject Instance d'objet non trouvé
   *
   * @param  string   $sLabel         Label de l'objet
   * @param  array    $aFieldsValues  Uid
   * @param  int      $iMdlUid        Modele UID
   * @return BusinessObject           Objet trouvé
   */
  public static function createNewBusinessObjectFromLabelAndModel($sLabel,$aFieldsValues,$iMdlUid)
  {
    // Search Object Definition from internal model
    $loObjDef = new ObjectDefinition();
    $laObjectFounded = $loObjDef->searchObjects(" UPPER(label) = UPPER('$sLabel') and uid_mdl = $iMdlUid ");

    // DEBUG print_r($laObjectFounded);

    // Only one founded?
    if (count($laObjectFounded) == 1) {
      $loObjDef = new ObjectDefinition($laObjectFounded[0]['Uid']);
    } else {
      throw new BusinessObjectException(
        \sprintf(
          "Le type d'objet '%s' a creer n'as pas ete trouve ! (MDL_UID:%s)",
          $sLabel,
          strval($iMdlUid)
          )
      );
    }//end if

    $loBusinessObj = null;
    if (!\is_null($loObjDef)) {
      $loBusinessObj = new BusinessObject(
        null,
        $loObjDef->getFieldValue('TableName')
      );

      if (!\is_null($loBusinessObj)) {
        // Business Object well initialized ?
        $iObjUid = $loBusinessObj->getFieldValue('Uid');
        if (!\is_null($iObjUid)) {
          // NOT !
          throw new BusinessObjectException(
            \sprintf(
              "L'objet (UID:%s) de type '%s' n'as pas ete trouve ! (MDL_UID:%s|Tablename:%s)",
              $iObjUid,
              $sLabel,
              strval($iMdlUid),
              $loObjDef->getFieldValue('TableName')
              )
          );
        } else {
          // SURE, attributes definition ...!
          foreach ($aFieldsValues as $lsKey => $lxValue){
            $loBusinessObj->setFieldValue($lsKey,$lxValue);
          }
          //TODO Gestion Utilisateurs -> Affectatioon de l'utilisateur courante
          $loBusinessObj->setCreatorUid(1);
        }
      }
    }

    return $loBusinessObj;
  }//end getBusinessObjectFromLabelAndUid()

  /**
   * Creer une instance de lien entre BusinessObject
   *
   * @param  int     $iLnkd           UID de la définition de lien
   * @param  int     $iUIDObjSource   UID de l'objet source.
   * @param  int     $iUidObjTarget   UID de l'objet destination.
   * @return LinkObject               Objet Crée.
   */
  public static function createNewLinkBetweenObjectFromLinkDefUID($iLnkd,$iUIDObjSource,$iUidObjTarget):LinkObject
  {
    $loNewLnkObj = null;
    // Search Object Definition from internal model
    $loLnkDef = new LinkDefinition($iLnkd);

    // Link Defintion Valid ?
    if (!\is_null($loLnkDef)) {
      $loNewLnkObj = new LinkObject();
      $lsBID = sprintf(
        "%s_%s",
        'LNK',
        'TMP'
      );
      // New LinkObject Ok ?
      if (!\is_null($loNewLnkObj)) {
        $loNewLnkObj->setFieldValue('LinkDefUid',$iLnkd);
        $loNewLnkObj->setFieldValue('ObjectSrcUid',$iUIDObjSource);
        $loNewLnkObj->setFieldValue('ObjectTrgUid',$iUidObjTarget);

        //TODO Gestion Utilisateurs -> Affectatioon de l'utilisateur courante
        $loNewLnkObj->setCreatorUid(1);

      }
    }

    return $loNewLnkObj;
  }//end createNewLinkBetweenObjectFromLinkDefUID()


  /**
   * Creation d'un lien entre 2 BusinessObject
   *
   * @param  string     $sBIDLnkd           BID de la definition de lien
   * @param  string     $sBIDObjSource      BID de l'objet source
   * @param  string     $sVersionObjSource  Version de l'objet source
   * @param  int        $iRevisionObjSource Revison de l'objet source
   * @param  string     $sBIDObjTarget      BID de l'objet cible
   * @param  string     $sVersionObjTarget  Version de l'objet cible
   * @param  int        $iRevisionObjTarget Revision de l'objet cible
   * @return LinkObject                     Objet lien crée.
   */
  public static function createNewLinkBetweenObjectFromLinkDefBID(
      $sBIDLnkd,
      $sBIDObjSource,
      $sVersionObjSource,
      $iRevisionObjSource,
      $sBIDObjTarget,
      $sVersionObjTarget,
      $iRevisionObjTarget
    ):LinkObject
  {
    // GETTING LinkDefinition fomr BID!
    // -------------------------------------------------------------------------
    $loLnkDef = new LinkDefinition();
    $lsCondition = sprintf(
      " bid = '%s'",
      $sBIDLnkd
    );
    $laLnkDef = $loLnkDef->searchObjects($lsCondition);

    // Link Defintion founded ?
    if (count($laLnkDef) == 1) {
      $loLnkDef = new LinkDefinition($laLnkDef[0]['Uid']);
    }
    else {
      // NOT !
      throw new LinkObjectException(
        \sprintf(
          "La definition de lien (BID:%s) n'as pas ete trouve ! Creation de lien annulee!",
          $sBIDLnkd
        )
      );
    }

    // GETTING InternalObject for ObjectSource from BID,Version,Revision!
    // -------------------------------------------------------------------------
    $loIntObjSrc = new InternalObject();
    $lsCondition = sprintf(
      " bid_obj = '%s' AND ver_obj = '%s' AND rev_obj = %s ",
      $sBIDObjSource,
      $sVersionObjSource,
      strval($iRevisionObjSource)
    );
    $laObjDefSrc = $loIntObj->searchObjects($lsCondition);

    if (count($laObjDefSrc)==1) {
      $loIntObjSrc = new InternalObject($laObjDefSrc[0]['Uid']);
    } else {
        throw new BusinessObjectException(
          \sprintf(
            "L'objet Source (BID:'%s'|Vers:'%s'|Rev:'%s') n'as pas ete trouve ! Creation de lien annulee!",
            $sBIDObjSource,
            $sVersionObjSource,
            $iRevisionObjSource
          )
        );
    }

    // GETTING InternalObject for ObjectTarget from BID,Version,Revision!
    // -------------------------------------------------------------------------
    $loIntObjTrg = new InternalObject();
    $lsCondition = sprintf(
      " bid_obj = '%s' AND ver_obj = '%s' AND rev_obj = %s ",
      $sBIDObjTarget,
      $sVersionObjTarget,
      strval($iRevisionObjTarget)
    );
    $laObjDefTrg = $loIntObjTrg->searchObjects($lsCondition);

    if (count($laObjDefTrg)==1) {
      $loIntObjTrg = new InternalObject($laObjDefTrg[0]['Uid']);
    } else {
        throw new BusinessObjectException(
          \sprintf(
            "L'objet Target (BID:'%s'|Vers:'%s'|Rev:'%s') n'as pas ete trouve ! Creation de lien annulee!",
            $sBIDObjTarget,
            $sVersionObjTarget,
            $iRevisionObjTarget
          )
        );
    }

    return self::createNewLinkBetweenObjectFromLinkDefUID(
        $loLnkDef->getUid(),
        $loIntObjSrc->getUid(),
        $loIntObjTrg->getUid()
    );
  }//end createNewLinkBetweenObjectFromLinkDefBID()

}//end class
?>
