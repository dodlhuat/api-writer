<?php
namespace TimeTracker;

class Model {
  public string $table = '';
  public bool $soft_delete = true;
  public bool $timestamps = true;
  public array $mandatory = ['id'];
  protected array $data;

  public function __construct($id = null) {
    if ($this->table === '') {
      // get class name and try to extract table name (class name plural)
      $expl = explode('\\', get_class($this));
      $split = preg_split('/(?=[A-Z])/',end($expl));
      array_shift($split);
      $this->table = strtolower(implode('_', $split)) . 's';
    }
    if ($id !== null) {

      $builder = new Builder();
      $data = $builder->select('*')->from($this->table)->where('id', '=', $id)->execute();
      if (isset($data['data']) && is_array($data['data'])) {
        $data = $data['data'][0];
        foreach (array_keys($data) as $key) {
          $this->data[$key] = Helpers::castElement($data[$key]);
        }
      }
    }
  }

  public function toArray() {
    return $this->data;
  }

  public function getId(): int {
    return $this->data['id'] ?? 0;
  }

  public function setId($id): void {
    $this->data['id'] = $id;
  }

  public function getTable(): string {
    return $this->table;
  }

  public function save(): array {
    // TODO: check for timestamps and add info
    if ($this->timestamps) {
      $this->data['updated_at'] = date('Y-m-d H:i:s');
    }
    $builder = new Builder();
    if ($this->getId() === 0) {
      if ($this->timestamps) {
        $this->data['created_at'] = date('Y-m-d H:i:s');
      }
      // TODO: überprüfen ob alle benötigten daten gesetzt sind
      $builder->insert($this->table, $this->toArray());
    } else {
      $builder->update($this->table, $this->toArray());
    }
    return [];
  }

  public function delete(): array {
    $builder = new Builder();
    return $builder->delete($this->table, $this->getId(), $this->soft_delete);
  }
}
