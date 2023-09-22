<?php

namespace TimeTracker;
use Exception;

class UserController {
  public function validatePassword(User $user, string $password): bool {
    return password_verify($password, $user->getPassword());
  }
}
