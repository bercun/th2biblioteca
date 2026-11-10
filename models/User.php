<?php

class User implements JsonSerializable
{
    public ?int $id = null;
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public ?string $password = null;
    public int $login_count = 0;
    public int $bonus = 0;
    public string $role = 'user';
    public int $purchases_count = 0;

    public function toArray(bool $includePassword = false): array
    {
        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'login_count' => $this->login_count,
            'bonus' => $this->bonus,
            'role' => $this->role,
            'purchases_count' => $this->purchases_count
        ];

        if ($includePassword) {
            $data['password'] = $this->password;
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
