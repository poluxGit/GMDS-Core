<?php

use PHPUnit\Framework\TestCase;
use GMDS\Core\CoreManager;
use GMDS\Core\Data\Database\DatabaseObject;
use GMDS\Core\Data\Database\SQL\SQLQueryGenerator;

/**
 * SQLQueryGeneratorTest
 *
 */
final class SQLQueryGeneratorTest extends TestCase
{
  /**
   * testGenerateSelectSQLQueryFromDatabaseObject
   */
  public function testGenerateSelectSQLQueryFromDatabaseObjectCaseOk(): void
  {
    $lObj = new DatabaseObject('table_test');
    $lObj->defineCommonField('Uid','uid','int',null,false,true,1);
    $lObj->defineCommonField('Label','label','string',null,false,false,2);

    $lsSQL = SQLQueryGenerator::generateSelectSQLQueryFromDatabaseObject($lObj);
    $this->assertEquals($lsSQL,"SELECT tObj.uid as Uid, tObj.label as Label FROM table_test tObj");
  }//end testGenerateSelectSQLQueryFromDatabaseObjectCaseOk()

  /**
   * testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedField
   */
  public function testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedField(): void
  {
    $lObj = new DatabaseObject('table_test');
    $lObj->defineCommonField('Uid','uid','int',null,false,true,1);
    $lObj->defineCommonField('Label','label','string',null,false,false,2);
    $lObj->defineLinkedField('tableLnk','LnkField','uid_mdl','uid','uid',3);

    $lsSQL = SQLQueryGenerator::generateSelectSQLQueryFromDatabaseObject($lObj);
    $this->assertEquals($lsSQL,"SELECT tObj.uid as Uid, tObj.label as Label, tLnk1.uid_mdl as LnkField FROM table_test tObj LEFT JOIN tableLnk tLnk1 ON tLnk1.uid = tObj.uid");
  }//end testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedField()

  /**
   * testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedFieldAndWhereCondition
   */
  public function testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedFieldAndWhereCondition(): void
  {
    $lObj = new DatabaseObject('table_test');
    $lObj->defineCommonField('Uid','uid','int',null,false,true,1);
    $lObj->defineCommonField('Label','label','string',null,false,false,2);
    $lObj->defineLinkedField('tableLnk','LnkField','uid_mdl','uid','uid',3);

    $lsSQL = SQLQueryGenerator::generateSelectSQLQueryFromDatabaseObject($lObj,[['uid',1]]);
    $this->assertEquals($lsSQL,"SELECT tObj.uid as Uid, tObj.label as Label, tLnk1.uid_mdl as LnkField FROM table_test tObj LEFT JOIN tableLnk tLnk1 ON tLnk1.uid = tObj.uid WHERE tObj.uid = 1");
  }//end testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedFieldAndWhereCondition()

  /**
   * testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedFieldAndWhereConditions
   */
  public function testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedFieldAndWhereConditions(): void
  {
    $lObj = new DatabaseObject('table_test');
    $lObj->defineCommonField('Uid','uid','int',null,false,true,1);
    $lObj->defineCommonField('Label','label','string',null,false,false,2);
    $lObj->defineCommonField('Model','mdl','string',null,false,false,3);
    $lObj->defineLinkedField('tableLnk','LnkField','uid_mdl','uid','uid',4);

    $lsSQL = SQLQueryGenerator::generateSelectSQLQueryFromDatabaseObject($lObj,[['Uid',1],['Model','toto']]);
    $this->assertEquals($lsSQL,"SELECT tObj.uid as Uid, tObj.label as Label, tObj.mdl as Model, tLnk1.uid_mdl as LnkField FROM table_test tObj LEFT JOIN tableLnk tLnk1 ON tLnk1.uid = tObj.uid WHERE tObj.uid = 1 AND tObj.mdl = 'toto'");
  }//end testGenerateSelectSQLQueryFromDatabaseObjectCaseOkWithLinkedFieldAndWhereConditions()

}//end class
