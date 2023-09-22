<?php
namespace TimeTracker;

use ErrorException;

require 'vendor/autoload.php';

class Helpers {
  public static function createResponse($data, $status = 'OK', $as_json = true): array {
    return ['data' => $data, 'status' => $status, 'code' => Helpers::responseStatusLookup($status)];
  }

  /**
   *
   * @param string $status
   * @return int
   */
  public static function responseStatusLookup(string $status): int {
    return match ($status) {
      'OK' => 200,
      'Created' => 201,
      'Unauthorized' => 401,
      'Forbidden' => 403,
      'Not Found' => 404,
      'Method Not Allowed' => 405,
      'Internal Server Error' => 500,
      default => 0,
    };
  }

  /**
   *
   * @throws ErrorException
   */
  public static function loadEnv(): void {
    $env_file_path = "./.env";
    if (!is_file($env_file_path)) {
      throw new ErrorException("Environment File is Missing.");
    }
    //Check .envenvironment file is readable
    if (!is_readable($env_file_path)) {
      throw new ErrorException("Permission Denied for reading the " . ($env_file_path) . ".");
    }
    $var_arrs = [];
    $fopen = fopen($env_file_path, 'r');
    if ($fopen) {
      //Loop the lines of the file
      while (($line = fgets($fopen)) !== false) {
        // Check if line is a comment
        $line_is_comment = (substr(trim($line), 0, 1) == '#') ? true : false;
        // If line is a comment or empty, then skip
        if ($line_is_comment || empty(trim($line))) continue;
        // Split the line variable and succeeding comment on line if exists
        $line_no_comment = explode("#", $line, 2)[0];
        // Split the variable name and value
        $env_ex = preg_split('/(\s?)\=(\s?)/', $line_no_comment);
        $env_name = trim($env_ex[0]);
        $env_value = isset($env_ex[1]) ? trim($env_ex[1]) : "";
        $var_arrs[$env_name] = $env_value;
      }
      // Close the file
      fclose($fopen);
    }
    foreach ($var_arrs as $name => $value) {
      $_ENV[$name] = $value;
    }
  }

  public static function castElement($element) {
    if ($element === null) {
      return $element;
    } elseif(strtotime($element)){
      return strtotime($element);
    } elseif (is_numeric($element)) {
      return (float)$element;
    } else {
      return $element;
    }
  }
}
