<?php

namespace TimeTracker;

require 'vendor/autoload.php';

class Builder {
  private array $fields;
  private array $where;
  private array $or_where;
  private string $from;
  private array $parameters = [];
  private array $or_parameters = [];
  private array $join;
  private int $limit = 0;

  public function select(string ...$fields): self {
    $this->fields = $fields;
    $this->where = [];
    $this->parameters = [];
    $this->limit = 0;
    return $this;
  }

  public function where(string $element, string $operator, string $value): self {
    $this->where[] = "$element $operator ?";
    $this->parameters[] = $value;
    return $this;
  }

  public function whereIn(string $element, array $values): self {
    $this->where[] = "$element IN (" . $this->createSqlArrayString($values) . ")";
    $this->parameters = [...$this->parameters, ...$values];
    return $this;
  }

  public function from(string $table): self {
    $this->from = $table;
    return $this;
  }

  public function limit(int $limit): self {
    $this->limit = $limit;
    return $this;
  }

  public function orWhere(string $element, string $operator, string $value): self {
    $this->or_where[] = "$element $operator ?";
    $this->or_parameters[] = $value;
    return $this;
  }

  public function orWhereIn(string $element, array $values): self {
    $this->or_where[] = "$element IN (" . $this->createSqlArrayString($values) . ")";
    $this->or_parameters = [...$this->parameters, ...$values];
    return $this;
  }

  public function join(string $table, string ...$on): self {
    $query = "JOIN {$table} ON ";
    foreach ($on as $index => $elem) {
      if ($index % 3 === 0 && $index > 0) {
        $query .= " AND";
      }
      $query .= " $elem";
    }
    $this->join[] = $query;
    return $this;
  }

  public function insert(string $table, array ...$elements) {
    $query = "INSERT INTO {$table} (" . implode(', ', array_keys($elements[0])) . ") VALUES ";
    $values = [];
    foreach ($elements as $element) {
      $values[] = "(" . $this->sqlImplode(', ', $element) . ")";
    }
    $query .= implode(', ', $values);
    $db = new DatabaseController();
    return $db->executeQuery($query);
  }

  public function update(string $table, array $values) {
    $id = $values['id'];
    unset($values['id']);
    $query = "UPDATE {$table} SET ";
    $update_values = [];
    foreach ($values as $key => $value) {
      $update_values[] = "{$key} = ?";
    }
    $query .= implode(', ', $update_values);
    $query .= " WHERE id = ?";
    $db = new DatabaseController();
    return $db->executeQuery($query, [...$values, $id]);
  }

  public function delete(string $table, int $id, bool $soft_delete) {

    if ($soft_delete) {
      $parameters = [date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $id];
      $query = "UPDATE {$table} SET deleted_at = ?, updated_at = ? WHERE id = ?";
    } else {
      $parameters = [$id];
      $query = "DELETE FROM {$table} WHERE id = ?";
    }
    $db = new DatabaseController();
    return $db->executeQuery($query, $parameters);
  }

  public function execute(): array {
    $query = 'SELECT ' . implode(', ', $this->fields) . ' ';
    $query .= 'FROM ' . $this->from . ' ';
    if (isset($this->join) && count($this->join) > 0) {
      $query .= implode($this->join) . ' ';
    }
    if (!empty($this->where) || !empty($this->or_where)) {
      $query .= 'WHERE ';
      if (!empty($this->where)) {
        $query .= implode(' AND ', $this->where);
      }
      if (!empty($this->where) && !empty($this->or_where)) {
        $query .= ' OR ';
      }
      if (!empty($this->or_where)) {
        $query .= implode(' OR ', $this->or_where);
        $this->parameters = [...$this->parameters, ...$this->or_parameters];
      }
    }
    if ($this->limit > 0) {
      $query .= ' LIMIT ' . $this->limit;
    }

    $db = new DatabaseController();
    return $db->executeQuery($query, $this->parameters);
  }

  private function createSqlArrayString($array) {
    if (!is_array($array)) {
      $array = [$array];
    }
    $keys = array_fill(0, count($array), '?');
    return implode(',', $keys);
  }

  private function sqlImplode(string $delimiter, array $data) {
    $values = [];
    foreach ($data as $element) {
      if (!is_numeric($element)) {
        $element = "'{$element}'";
      }
      $values[] = $element;
    }
    return implode($delimiter, $values);
  }
}
