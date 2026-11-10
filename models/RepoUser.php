<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/User.php';

class RepoUser
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getConnection();
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                first_name,
                last_name,
                email,
                login_count,
                bonus,
                role
             FROM users
             WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email'
        );

        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row ? $this->mapRow($row) : null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM users WHERE email = :email'
        );

        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetch();
    }

    public function create(User $user): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users
                (first_name, last_name, email, password, login_count)
             VALUES
                (:first_name, :last_name, :email, :password, 1)'
        );

        $stmt->execute([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'password' => $user->password
        ]);

        $user->id = (int) $this->db->lastInsertId();
        $user->login_count = 1;
        $user->role = 'user';

        return $user->id;
    }

    public function incrementLoginCount(int $userId): void
    {
        $this->db->prepare(
            'UPDATE users
             SET login_count = login_count + 1
             WHERE id = :id'
        )->execute(['id' => $userId]);
    }

    public function addBonus(int $bonus, int $userId): void
    {
        $this->db->prepare(
            'UPDATE users
             SET bonus = bonus + :bonus
             WHERE id = :id'
        )->execute([
            'bonus' => $bonus,
            'id' => $userId
        ]);
    }

    public function getBonus(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT bonus FROM users WHERE id = :id'
        );

        $stmt->execute(['id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    public function isAdmin(int $userId): bool
    {
        $user = $this->findById($userId);

        return $user !== null && $user->isAdmin();
    }

    /** @return User[] */
    public function listForAdmin(): array
    {
        $sql = "
            SELECT
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.login_count,
                u.bonus,
                u.role,
                COUNT(ub.book_id) AS purchases_count
            FROM users u
            LEFT JOIN user_books ub
                ON ub.user_id = u.id
            GROUP BY
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                u.login_count,
                u.bonus,
                u.role
            ORDER BY u.id
        ";

        $rows = $this->db->query($sql)->fetchAll();

        return array_map(
            fn(array $row) => $this->mapRow($row),
            $rows
        );
    }

    private function mapRow(array $row): User
    {
        $user = new User();
        $user->id = isset($row['id']) ? (int) $row['id'] : null;
        $user->first_name = (string) ($row['first_name'] ?? '');
        $user->last_name = (string) ($row['last_name'] ?? '');
        $user->email = (string) ($row['email'] ?? '');
        $user->password = $row['password'] ?? null;
        $user->login_count = (int) ($row['login_count'] ?? 0);
        $user->bonus = (int) ($row['bonus'] ?? 0);
        $user->role = (string) ($row['role'] ?? 'user');
        $user->purchases_count = (int) ($row['purchases_count'] ?? 0);

        return $user;
    }
}
