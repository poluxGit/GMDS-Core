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
		 * Chargement des paramètres depuis un fichier invalide.
		 * @expectedException GMDS\Core\Exception\CoreException
		 */
  	public function testLoadSystemSettingsFromNonExistingFile(): void
  	{
			CoreManager::loadSystemSettingsFromJSONFile('./non-existing-file.json');
	  }//end testLoadSystemSettingsFromNonExistingFile()

		/**
		 * testLoadSystemSettingsFromInvalidFileMandatoryFieldsMissing
		 *
		 * Chargement des paramètres depuis un fichier au format JSON invalide.
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
     * Chargement des paramètres depuis un fichier au format JSON invalide.
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
     */
    public function testLoadSystemSettingsFromValidFile(): void
    {
      $lsDirSettingsFile =  './tests/test-settings/';
      CoreManager::loadSystemSettingsFromJSONFile($lsDirSettingsFile.'app-settings-local.valid.json');
      $this->assertEquals(true,true);
    }//end testLoadSystemSettingsFromValidFile()

    /**
     * testDeployCoreDatabaseStructure
     *
     *  @depends testLoadSystemSettingsFromValidFile
     */
    public function testDeployCoreDatabaseStructure()
    {
      $lsDirSettingsFile = './tests/test-settings/';
      CoreManager::setApplicationLoggerFilepath('./logs/logApplication.log');
      CoreManager::setApplicationDatabaseLoggerFilepath('./logs/logDBQueries.log');
      CoreManager::deployDatabaseSchemaIntoDatabase();
      $this->assertEquals(true,true);
    }//end testDeployCoreDatabaseStructure()

    // /**
    //  * testApplicationLoadSettingsFileMandaotryParamMissing
    //  *
    //  * Chargement des paramètres depuis un fichier au format JSON invalide.
    //  * @expectedException GOM\Core\Internal\Exception\ApplicationSettingsMandatorySettingNotDefinedException
    //  */
    // public function testApplicationLoadSettingsFileMandatoryParamMissing(): void
    // {
    //  //	$this->expectException(\Exception::class);
    //   Application::loadDBSettings('./tests/datasets/app-settings_02-invalidNoMand.json');
    // }//end testApplicationLoadSettingsFileMandatoryParamMissing()
    //
    // /**
    //  * testApplicationInvalidDatabaseConnection
    //  *
    //  * Chargement des paramètres depuis un fichier au format JSON invalide.
    //  * @expectedException \PDOException
    //  */
    // public function testApplicationInvalidDatabaseConnection(): void
    // {
    //    Application::loadDBSettings('./tests/datasets/app-settings_03-invalidconnec.json');
    // }//end testApplicationInvalidDatabaseConnection()
    //
		// /**
		//  * testApplicationLoadValideSettingsFile
		//  *
		//  * Chargement des paramètres depuis un fichier au format JSON valide.
    //  */
  	// public function testApplicationLoadSettingsFromValidFile(): void
  	// {
		//  	Application::loadDBSettings('./tests/datasets/app-settings_02-valid.json');
		//  	$this->assertTrue(true);
		// }//end testApplicationLoadSettingsFromValidFile()
    //
    // // /**
    // //  * testApplicationDeployingIntoTargetDatabase
    // //  *
    // //  * Chargement des paramètres depuis un fichier au format JSON valide.
    // //  */
    // // public function testApplicationDeployingIntoTargetDatabase(): void
    // // {
    // //   Application::deploySchemaToTargetDB(
    // //     'GDM_TEST',
    // //     'root',
    // //     'dev',
    // //     '172.17.0.2',
    // //     '3306'
    // //   );
    // //   $this->assertTrue(true);
    // // }//end testApplicationDeployingIntoTargetDatabase()
    //
    //
    // /**
    //  * testApplicationDeployingDefaultApplicationDatabase
    //  *
    //  * Chargement des paramètres depuis un fichier au format JSON valide.
    //  */
    // public function testApplicationDeployingDefaultApplicationDatabase(): void
    // {
    //   Application::loadDBSettings('./tests/datasets/app-settings_02-valid.json');
    //   Application::deploySchemaToDefaultAppliDB(
    //     'root',
    //     'dev'
    //   );
    //   $this->assertTrue(true);
    // }//end testApplicationDeployingDefaultApplicationDatabase()

}//end class
