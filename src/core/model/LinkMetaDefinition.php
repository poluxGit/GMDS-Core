<?php

namespace GDTA\Core\Model;

use GDTA\Core\Data\Database\DatabaseObject;

/**
 * LinkMetaDefinition class
 *
 */
class LinkMetaDefinition extends DatabaseObject
{
  use \GDTA\Core\Model\Traits\DatabaseObjectUidKeyField;
  use \GDTA\Core\Model\Traits\DatabaseObjectCommonField;
  // use GDTA\Core\Model\Trait\DatabaseObjectVersionFields;
  use \GDTA\Core\Model\Traits\DatabaseObjectCreationUpdateFields;

  /**
   * Constructeur
   *
   * @param int $iUid    Optional : Uid to load
   */
  function __construct($iUid=null)
  {
    parent::__construct('a0000_def_links_meta');
    $this->setObjectKeyDBFields();
    $this->setCommonDBfields();
    $this->setCreationUpdateDBFields();
    $this->initSpecificObjectFieldsDefintion();

    if (!\is_null($iUid)) {
      $this->loadObjectFromCondition(["uid = $iUid"]);
    }
  }//end __construct()

  protected function initSpecificObjectFieldsDefintion()
  {
      // Other fields definition ...
      $this->addNewFieldDefinition('LinkDefinitionUid','uid_lnkd');
      $this->addNewFieldDefinition('LinkMetaTitle','data_title');
      $this->addNewFieldDefinition('LinkMetaType','data_type');
      $this->addNewFieldDefinition('LinkMetaPattern','data_pattern');
      $this->addNewFieldDefinition('LinkMetaOptions','data_options');
      $this->addNewFieldDefinition('LinkMetaSQLName','data_sqlname');
      $this->addNewFieldDefinition('LinkMetaSQLOrder','data_sqlorder');
  }//end initSpecificObjectFieldsDefintion()

  /**
   * Retourne un tableau d'identifiant de défintion de meta sur lien  depuis une
   * définition de lien entre objet
   *
   * @param  int  $iLinkDefinition    UID de la définition de lien entre objets.
   * @return array                    Tableau d'UID des meta définition trouvée.
   */
  public static function getLinkMetaDefinitionForLinkDefinition($iLinkDefinition):array
  {
    $laResult         = [];
    $loObjMetaDefTmp  = new LinkMetaDefinition();
    $loObjDef         = new LinkDefinition($iLinkDefinition);

    $lsCondition = sprintf(
      " uid_lnkd = %s ",
      $loObjDef->getUid()
    );

    $laResult = $loObjMetaDefTmp->searchObjects($lsCondition);
     // foreach ($laResult as $key => $value) {
     //   $laResultFinal[] = $value['Uid'];
     // }
    return $laResult;
  }//end getLinkMetaDefinitionForLinkDefinition()
}//end class

?>
