<?php

namespace GDTA\Core\Model\Traits;

/**
 * DatabaseObjectVersionFields - Generic Management about Version/revision fields
 *
 */
trait DatabaseObjectVersionFields
{
  /**
   * Return Version of Object
   * @return string  Object' BID
   */
  public function getVersion():string
  {
    return $this->getFieldValue('Version');
  }//end getVersion()

  /**
   * Set Version of Object
   * @param string $sVersion  Object' Version
   */
  public function setVersion($sVersion)
  {
    $this->setFieldValue('Version',$sVersion);
  }//end setVersion()

  /**
   * Return Revision of Object
   * @return string  Object' BID
   */
  public function getRevision():string
  {
    return $this->getFieldValue('Revision');
  }//end getRevision()

  /**
   * Set Revision of Object
   * @param string $sRevision  Object' Revision
   */
  public function setRevision($sRevision)
  {
    $this->setFieldValue('Revision',$sRevision);
  }//end setRevision()

  public function setVersionDBfields()
  {
      $this->addNewFieldDefinition('Version','vers');
      $this->addNewFieldDefinition('Revision','rev');
  }

}//end trait

 ?>
