<?php
namespace Sushi\M5StreamWebsite\Services;

use MongoDB\Database;

class AuthService
{
    private $db;
    private $users;

    public function __construct(Database $db)
    {
        $this->db = $db;
        $this->users = $db->users;
    }

    public function register(string $username, string $password): bool
    {
        // Check if username exists
        $existing = $this->users->findOne(['username' => $username]);
        if ($existing) {
            return false;
        }

        // Hash password
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $this->users->insertOne([
            'username' => $username,
            'password' => $hashed,
            'created_at' => new \MongoDB\BSON\UTCDateTime()
        ]);

        return true;
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->users->findOne(['username' => $username]);
        if (!$user) return false;

        return password_verify($password, $user['password']);
    }
}
