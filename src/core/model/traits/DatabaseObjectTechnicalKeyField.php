<?php

namespace GMDS\Core\Model\Traits;

use GMDS\Core\Data\Database\DatabaseObject;

/**
 * DatabaseObjectTechnicalKeyField - Generic Management about Uid of all objects
 *
 */
trait DatabaseObjectTechnicalKeyField
{
  /**
   * Return Uid of Object
   * @return int  Object' UID
   */
  public function getUid()
  {
    return $this->getFieldValue('Uid');
  }//end getUid()

  /**
   * Set Uid of Object
   * @param int $iUid  Object' UID
   */
  public function setUid($iUid)
  {
    $this->setFieldValue('Uid',$iUid);
  }//end setUid()

  public function setObjectKeyDBFields()
  {
      // UID Key !

      $this->addNewKeyFieldDefinition('Uid','uid');
  }

}//end trait

 ?>
