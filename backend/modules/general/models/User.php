<?php

namespace TimeTracker;

use Exception;

class User extends Model {
  public function getFirstname(): string {
    return $this->data['firstname'];
  }

  public function setFirstname(string $firstname): void {
    $this->data['firstname'] = $firstname;
  }

  public function getLastname(): string {
    return $this->data['lastname'];
  }

  public function setLastname(string $lastname): void {
    $this->data['lastname'] = $lastname;
  }

  public function getEmail(): string {
    return $this->data['email'];
  }

  public function setEmail(string $email): void {
    $this->data['email'] = $email;
  }

  public function setPassword($password): void {
    $this->data['password'] = password_hash($password, PASSWORD_DEFAULT);
  }

  public function getPassword(): string {
    return $this->data['password'];
  }
}
