<?php

namespace TimeTracker;

use Exception;
use PDO;
use PDOException;

class DatabaseController {

  protected $connection;

  public function __construct() {
    try {
      Helpers::loadEnv();
      $this->connection = new PDO('mysql:host=' . $_ENV['HOST'] . ';dbname=' . $_ENV['DB'] . ';charset=utf8;port=' . $_ENV['PORT'], $_ENV['USER'], $_ENV['PASSWORD']);
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->connection->exec("set names utf8");
    } catch (PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      die();
    }
  }

  /**
   *
   * @param string $query
   * @param array $parameters
   * @return array
   * @throws Exception
   */
  public function executeQuery(string $query, array $parameters = []): array {
    $data = $this->connection->prepare($query);
    if (!$data->execute($parameters)) {
      throw new Exception('Query execution failed!');
    }

    $insert = '/^INSERT/';
    $select = '/^SELECT/';
    $update = '/^UPDATE/';
    $delete = '/^DELETE/';
    $show = '/^SHOW/';
    if (preg_match($insert, strtoupper(trim($query)))) {
      $response = Helpers::createResponse($this->connection->lastInsertId());
    } elseif (preg_match($select, strtoupper(trim($query)))) {
      $response = Helpers::createResponse($data->fetchAll(PDO::FETCH_ASSOC));
    } else {
      $response = Helpers::createResponse(true);
    }
    return $response;
  }
}
