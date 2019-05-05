<?php

namespace GMDS\Core\Model\Traits;

/**
 * DatabaseObjectCommonField - Generic Management about common fields
 *
 */
trait DatabaseObjectCommonField
{
  /**
   * Return Bid of Object
   * @return string  Object' BID
   */
  public function getBid():string
  {
    return $this->getFieldValue('Bid');
  }//end getBid()

  /**
   * Set Bid of Object
   * @param string $sBid  Object' Bid
   */
  public function setBid($sBid)
  {
    $this->setFieldValue('Bid',$sBid);
  }//end setBid()

  /**
   * Return Label of Object
   * @return string  Object' Label
   */
  public function getLabel():string
  {
    return $this->getFieldValue('Label');
  }//end getLabel()

  /**
   * Set Label of Object
   * @param string $sLabel  Object' Label
   */
  public function setLabel($sLabel)
  {
    $this->setFieldValue('Label',$sLabel);
  }//end setLabel()

  /**
   * Return Name of Object
   * @return string  Object' Name
   */
  public function getName():string
  {
    return $this->getFieldValue('Name');
  }//end getName()

  /**
   * Set Name of Object
   * @param string $sName  Object' Name
   */
  public function setName($sName)
  {
    $this->setFieldValue('Name',$sName);
  }//end setName()

  /**
   * Return JSONData of Object
   * @return string  Object' JSONData
   */
  public function getJSONData():string
  {
    return $this->getFieldValue('JSONData');
  }//end getJSONData()

  /**
   * Set JSONData of Object
   * @param string $sJSONData  Object' JSONData
   */
  public function setJSONData($sJSONData)
  {
    $this->setFieldValue('JSONData',$sJSONData);
  }//end setJSONData()

  /**
   * Return Comment of Object
   * @return string  Object' Comment
   */
  public function getComment():string
  {
    return $this->getFieldValue('Comment');
  }//end getComment()

  /**
   * Set Comment of Object
   * @param string $sComment  Object' Comment
   */
  public function setComment($sComment)
  {
    $this->setFieldValue('Comment',$sComment);
  }//end setComment()


  public function setCommonDBfields()
  {
    if ($this instanceof DatabaseObject) {
      $this->defineCommonField('Bid','bid','string','TODEF',true,false,0);
      $this->defineCommonField('Label','label','string','TODEF',true,false,0);
    }
      $this->addNewFieldDefinition('Label','label');
      $this->addNewFieldDefinition('Name','name');
      $this->addNewFieldDefinition('Comment','comment');
      $this->addNewFieldDefinition('JSONData','jsondata');
  }

}//end trait

 ?>
