<?php

namespace GMDS\Core\Data\Database\SQL;

use GMDS\Core\Data\Database\DatabaseObject;
use GMDS\Core\Data\Database\DatabaseObjectField;
use GMDS\Core\Data\Database\DatabaseObjectFields;


use GMDS\Core\Exception\SQLQueryGeneratorException;

/**
 * SQLQueryGenerator - Classe de génération de requete SQL
 */
class SQLQueryGenerator {

  // ***************************************************************************
  // Friends classes
  // ***************************************************************************

  /**
   * Generate a Select SQL Query (without WHERE) according object in parameters
   *
   * @param  DatabaseObject $oDBObj             Source Object
   * @param  array          $aWhereConditions   WHERE Conditions
   * @return string                   Select query generated
   */
  public static function generateSelectSQLQueryFromDatabaseObject(DatabaseObject $oDBObj, $aWhereConditions=[]):string
  {
    $lsFinalSQLQuery  = '';
    $laSelectPart     = [];
    $laFromPart       = [];
    $laWherePart      = [];
    $lsMainTableAlias = 'tObj';
    $lsLinkTableAlias = 'tLnk';
    $liLinkTableCount = 0;

    // Main Table integration!
    array_push($laFromPart,$oDBObj->getTablename()." ".$lsMainTableAlias);

    // For each fields in Order !
    foreach ($oDBObj->_oFields->getAllFieldId() as $value) {
      $loField = $oDBObj->_oFields->getFieldObjectById($value);
      if ($loField instanceof DatabaseObjectField) {
        // Linked field ?
        if (!\is_null($loField->getLinkedTableName())) {
          // No Join Constraints => SQLQueryGeneratorException!
          if(count($loField->getLinkedTableJoinConstraintsArray()) === 0) {
            throw new SQLQueryGeneratorException(
              'Field definition not valid : Linked table without join constraints (linked table : %s).',
              [$loField->getLinkedTableName()]
            );
          } else {
            // New alias for linked table!
            $liLinkTableCount++;
            $lsLinkTableAliasInstance = $lsLinkTableAlias.$liLinkTableCount;

            // From Part generation!
            array_push(
              $laFromPart,
              sprintf(
                "%s %s %s ON %s.%s = %s.%s",
                $loField->getLinkedTableJoinConstraintsArray()[0],
                $loField->getLinkedTableName(),
                $lsLinkTableAliasInstance,
                $lsLinkTableAliasInstance,
                $loField->getLinkedTableJoinConstraintsArray()[1],
                $lsMainTableAlias,
                $loField->getLinkedTableJoinConstraintsArray()[2]
              )
            );

            // Select Part generation!
            array_push(
              $laSelectPart,
              sprintf(
                "%s.%s as %s",
                $lsLinkTableAliasInstance,
                $loField->getSQLDefinition(),
                $loField->getSQLAlias()
              )
            );
          }
        } else {
          // Normal field!
          // Select Part generation!
          array_push(
            $laSelectPart,
            sprintf(
              "%s.%s as %s",
              $lsMainTableAlias,
              $loField->getSQLDefinition(),
              $loField->getSQLAlias()
            )
          );
        }
      } else {
        // Field Data invalid (not instanceof)
        throw new SQLQueryGeneratorException(
          "Field with ID %s can't be founded!",
          [$value]
        );
      }
    }

    // Final SQL generation!
    $lsFinalSQLQuery = sprintf(
      "SELECT %s FROM %s",
      implode(', ', $laSelectPart),
      implode(' ', $laFromPart)
    );

    // WHERE PART!
    if (count($aWhereConditions)>0) {
      foreach ($aWhereConditions as $value) {
        //echo "Value ".$value[0]." - ".$value[1];
        $loField = $oDBObj->_oFields->getFieldObjectById($value[0]);
        \array_push(
          $laWherePart,
          sprintf(
            "%s.%s = %s",
            $lsMainTableAlias,
            $loField->getSQLDefinition(),
            ($loField->getFieldType()=='int'?$value[1]:"'".$value[1]."'")
          )
        );
      }

      $lsFinalSQLQuery = sprintf(
        "%s WHERE %s",
        $lsFinalSQLQuery,
        implode(' AND ', $laWherePart)
      );
    }

    return $lsFinalSQLQuery;
  }//end generateSelectSQLQueryFromDatabaseObject()



  /**
   * Generate SQL Select Query
   *
   * @param  array  $aSelectFields    SQL Select Fields (i.e array('ALIAS'=>'FIELDDEF/FIELDNAME'))
   * @param  string $sTablename       SQL Tablename
   * @param  array  $aWhereConditions SQL WHERE Conditions
   * @param  string $sOrderby         SQL Order By
   * @return string                   SQL Query generated.
   */
  public static function generateSelectSQLQuery($aSelectFields,$sTablename,$aWhereConditions=null,$sOrderby=null)
  {
    $lsSQLQueryPattern = "SELECT %s FROM %s";
    $lsSQLQuery = "";

    // SELECT & FROM Part !
    $laSQLField = [];
    $lsSelectSQLQuery = "*";
    if (!\is_null($aSelectFields) && count($aSelectFields)>0) {
      foreach ($aSelectFields as $key => $value) {
        array_push($laSQLField, $value.' as '.$key);
      }
      $lsSelectSQLQuery =   implode(', ',$laSQLField);
    }

    $lsSQLQuery = sprintf(
      $lsSQLQueryPattern,
      $lsSelectSQLQuery,
      $sTablename
    );

    // WHERE Part - Needed ?
    if (!\is_null($aWhereConditions) && \is_array($aWhereConditions) && sizeof($aWhereConditions) > 0) {
      $lsSQLQuery .= " WHERE ".\array_shift($aWhereConditions);
    } else {
      $lsSQLQuery .= " WHERE ".$aWhereConditions;
    }

    // ORDER BY Part - Needed ?
    if (!\is_null($sOrderby)) {
      $lsSQLQuery .= " ORDER BY ".$sOrderby;
    }
    return $lsSQLQuery;
  }//end generateSelectSQLQuery()

  /**
   * Generate SQL Update Query
   *
   * @param  array  $aUpdateFieldAndValues    Champs(Key)/Valeur à mettre à jour
   * @param  string $sTablename               Nom de la table à mettre à jour
   * @param  array  $aWhereConditions         Conditions SQL 'AND'
   * @return string                           SQL Query generated.
   */
  public static function generatUpdateSQLQuery($aUpdateFieldAndValues,$sTablename,$aWhereConditions)
  {
    $lsSQLQueryPattern = "UPDATE %s SET %s WHERE %s";
    $lsSQLQuery = "";

    // SET & FROM Parts !
    $laSQLUpdateField = [];

    foreach ($aUpdateFieldAndValues as $key => $value) {
      $lsSetdef = $key." = ".((\is_string($value))?"'$value'":"$value");
      array_push($laSQLUpdateField, $lsSetdef);
    }


    // WHERE Part Well formed ?
    if (\is_null($aWhereConditions) || sizeof($aWhereConditions) == 0) {
      // TODO Classe d'exception spécifique
      throw new ApplicationException(
        "loadObjectFromCondition",
        "Impossible de charger l'objet - Plusieurs données sources trouvées. Nb: %s",
        \strval(count($laResult))
      );
    }

    // TODO Robuste !!! Nouvelle manière de générer les conditions SQL (array(array(array()))) ....
    $lsSQLWhereQuery = \array_shift($aWhereConditions);
  //  print_r($lsSQLWhereQuery);


    $lsSQLQuery = sprintf(
      $lsSQLQueryPattern,
      $sTablename,
      implode(', ',$laSQLUpdateField),
      $lsSQLWhereQuery
    );

    // DEBUG - Print Query Generated - echo $lsSQLQuery;

    return $lsSQLQuery;
  }//end generatUpdateSQLQuery()

  /**
   * Generate SQL Insert Query
   *
   * @param  array  $aInsertFieldAndValues Champs / Valeurs à définir
   * @param  string $sTablename            Nom de la table où insérer les données.
   * @return string                        SQL Query generated.
   */
  public static function generatInsertSQLQuery($aInsertFieldAndValues,$sTablename)
  {
    $lsSQLQueryPattern = "INSERT INTO %s (%s) VALUES (%s)";
    $lsSQLQuery = "";

    // SET & FROM Parts !
    $laSQLInsertFieldNames = [];
    $laSQLInsertFieldValues = [];

    foreach ($aInsertFieldAndValues as $key => $value) {
      array_push($laSQLInsertFieldNames, $key);
      array_push($laSQLInsertFieldValues, ((\is_string($value))?"'$value'":"$value"));
    }

    $lsSQLQuery = sprintf(
      $lsSQLQueryPattern,
      $sTablename,
      implode(', ',$laSQLInsertFieldNames),
      implode(', ',$laSQLInsertFieldValues)
    );

    // DEBUG - Print Query Generated -echo $lsSQLQuery;

    return $lsSQLQuery;
  }//end generatUpdateSQLQuery()

}//end class

?>
