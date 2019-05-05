<?php

namespace GDTA\Core\Model\Traits;

/**
 * DatabaseObjectUidKeyField - Generic Management about Uid of all objects
 *
 * @internal only getters because these fields are defined by DB System.
 */
trait DatabaseObjectCreationUpdateFields
{
  /**
   * Return CreationDate of Object
   * @return int  Object' CreationDate Timestamp
   */
  public function getCreationDate():int
  {
    return $this->getFieldValue('CreationDate');
  }//end getCreationDate()

  /**
   * Return LastUpdate of Object
   * @return int  Object' LastUpdate Timestamp
   */
  public function getLastUpdateTimestamp():int
  {
    return $this->getFieldValue('LastUpdate');
  }//end getLastUpdateTimestamp()

  /**
   * Return Creator of Object
   * @return int  Object' Creator Uid
   */
  public function getCreatorUid():int
  {
    return $this->getFieldValue('Creator');
  }//end getCreatorUid()


  /**
   * Return Creator of Object
   * @return int  Object' Creator Uid
   */
  public function setCreatorUid($iUid)
  {
    $this->setFieldValue('Creator',$iUid);
  }//end setCreatorUid()


  /**
   * Return Updater of Object
   * @return int  Object' Updater Uid
   */
  public function getUpdaterUid():int
  {
    return $this->getFieldValue('Updater');
  }//end getUpdaterUid()

  /**
   * Set up Creation/update fields
   */
  public function setCreationUpdateDBFields()
  {
      $this->addNewFieldDefinition('CreationDate','cdate');
      $this->addNewFieldDefinition('LastUpdate','udate');
      $this->addNewFieldDefinition('Creator','cuser');
      $this->addNewFieldDefinition('Updater','uuser');
  }//end setCreationUpdateDBFields()

}//end trait

 ?>
