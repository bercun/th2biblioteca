<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/BookCategory.php';

class RepoBookCategory
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getConnection();
    }

    public function exists(int $id): bool
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM books_category WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);

        return (bool) $stmt->fetch();
    }

    /** @return BookCategory[] */
    public function findAll(): array
    {
        $rows = $this->db
            ->query(
                'SELECT id, season
                 FROM books_category
                 ORDER BY id'
            )
            ->fetchAll();

        return array_map(
            function (array $row) {
                $category = new BookCategory();
                $category->id = (int) $row['id'];
                $category->season = (string) $row['season'];

                return $category;
            },
            $rows
        );
    }
}
