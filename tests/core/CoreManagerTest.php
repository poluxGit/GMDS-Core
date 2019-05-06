<?php

use PHPUnit\Framework\TestCase;
use GMDS\Core\CoreManager;

/**
 * CoreManagerTest
 *
 * GMDS\Core\CoreManager
 */
final class CoreManagerTest extends TestCase
{
		/**
		 * testLoadSystemSettingsFromNonExistingFile
		 *
		 * @expectedException GMDS\Core\Exception\CoreException
		 */
  	public function testLoadSystemSettingsFromNonExistingFile(): void
  	{
			CoreManager::loadSystemSettingsFromJSONFile('./non-existing-file.json');
	  }//end testLoadSystemSettingsFromNonExistingFile()

		/**
		 * testLoadSystemSettingsFromInvalidFileMandatoryFieldsMissing
		 *
		 * @expectedException GMDS\Core\Exception\CoreException
		 */
  	public function testLoadSystemSettingsFromInvalidFileMandatoryFieldsMissing(): void
  	{
      $lsDirSettingsFile = realpath(dirname(__FILE__).'/tests/test-settings/');
			CoreManager::loadSystemSettingsFromJSONFile($lsDirSettingsFile.'app-settings.invalid-01.json');
	  }//end testLoadSystemSettingsFromInvalidFileMandatoryFieldsMissing()

    /**
     * testLoadSystemSettingsFromInvalidFileDBFieldsMissing
     *
     * @expectedException GMDS\Core\Exception\CoreException
     */
    public function testLoadSystemSettingsFromInvalidFileDBFieldsMissing(): void
    {
      $lsDirSettingsFile ='./tests/test-settings/';
      CoreManager::loadSystemSettingsFromJSONFile($lsDirSettingsFile.'app-settings.invalid-02.json');
    }//end testLoadSystemSettingsFromInvalidFileDBFieldsMissing()

    /**
     * testLoadSystemSettingsFromValidFile
     *
     * Most common case.
     */
    public function testLoadSystemSettingsFromValidFile(): void
    {
      $lsDirSettingsFile =  './tests/test-settings/';
      CoreManager::loadSystemSettingsFromJSONFile($lsDirSettingsFile.'app-settings-local.valid.json');
      CoreManager::setApplicationLoggerFilepath('./logs/logApplication.log');
      CoreManager::setApplicationDatabaseLoggerFilepath('./logs/logDBQueries.log');
      $this->assertEquals(true,true);
    }//end testLoadSystemSettingsFromValidFile()

    // /**
    //  * testDeployCoreDatabaseStructure
    //  *
    //  * @depends testLoadSystemSettingsFromValidFile
    //  */
    // public function testDeployCoreDatabaseTables()
    // {
    //   $lsDirSettingsFile = './tests/test-settings/';
    //
    //   $lDBHandler = CoreManager::getDefaultDatabaseConnection();
    //   $lDBHandler->execScript("DROP SCHEMA ".$lDBHandler->getSchemaName().";");
    //   $lDBHandler->execScript("CREATE SCHEMA ".$lDBHandler->getSchemaName().";");
    //
    //   $laResult = $lDBHandler->queryAndFetch(
    //     sprintf(
    //       "select count(*) as NB from information_schema.tables WHERE table_schema ='%s';",
    //       $lDBHandler->getSchemaName()
    //     )
    //   );
    //
    //   $this->assertEquals(0,intval($laResult[0]['NB']));
    //   CoreManager::deployDatabaseSchemaIntoDatabase();
    //
    //   $laResult = $lDBHandler->queryAndFetch(
    //     sprintf(
    //       "select count(*) as NB from information_schema.tables WHERE table_schema ='%s';",
    //       $lDBHandler->getSchemaName()
    //     )
    //   );
    //
    //   $this->assertEquals(12,intval($laResult[0]['NB']));
    // }//end testDeployCoreDatabaseStructure()

}//end class
