<?php
namespace GDTA\Core\Tools;

/**
 * Classe Tools
 *
 * Boite à outils.
 */
class Tools {

  /**
   * Convertit une données en tableau associatifs
   *
   * @param mixed   Données à convertir
   * @return array  Tableau associatif de l'objet
   */
  public static function convertObjectToArray($data)
  {
    print_r($data);
      if (is_array($data) || is_object($data))
      {
          $result = array();
          foreach ($data as $key => $value)
          {
              $result[$key] = self::convertObjectToArray($value);
          }
          print_r($result);
          return $result;
      }


      return $data;
  }//end convertObjectToArray()

}//end class

?>
