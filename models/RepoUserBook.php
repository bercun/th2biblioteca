<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/UserBook.php';

class RepoUserBook
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getConnection();
    }

    public function userOwnsBook(int $userId, int $bookId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT user_id
             FROM user_books
             WHERE user_id = :user_id
               AND book_id = :book_id'
        );

        $stmt->execute([
            'user_id' => $userId,
            'book_id' => $bookId
        ]);

        return (bool) $stmt->fetch();
    }

    public function purchase(int $userId, int $bookId): string
    {
        $stmt = $this->db->prepare(
            'INSERT INTO user_books
                (user_id, book_id, purchased_at)
             VALUES
                (:user_id, :book_id, NOW())'
        );

        $stmt->execute([
            'user_id' => $userId,
            'book_id' => $bookId
        ]);

        $stmt = $this->db->prepare(
            'SELECT purchased_at
             FROM user_books
             WHERE user_id = :user_id
               AND book_id = :book_id'
        );

        $stmt->execute([
            'user_id' => $userId,
            'book_id' => $bookId
        ]);

        return (string) $stmt->fetchColumn();
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*)
             FROM user_books
             WHERE user_id = :user_id'
        );

        $stmt->execute(['user_id' => $userId]);

        return (int) $stmt->fetchColumn();
    }

    /** @return UserBook[] */
    public function listOwnedByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                books.id,
                books.title,
                books.author,
                books.bonus,
                user_books.purchased_at,
                reviews.rating AS user_rating
             FROM user_books
             INNER JOIN books
                ON books.id = user_books.book_id
             LEFT JOIN reviews
                ON reviews.book_id = user_books.book_id
               AND reviews.user_id = user_books.user_id
             WHERE user_books.user_id = :user_id
             ORDER BY user_books.purchased_at DESC, books.title'
        );

        $stmt->execute(['user_id' => $userId]);

        return array_map(
            function (array $row) use ($userId) {
                $item = new UserBook();
                $item->user_id = $userId;
                $item->id = (int) $row['id'];
                $item->book_id = (int) $row['id'];
                $item->title = (string) $row['title'];
                $item->author = (string) $row['author'];
                $item->bonus = (int) $row['bonus'];
                $item->purchased_at = $row['purchased_at'] ?? null;
                $item->user_rating = $row['user_rating'] !== null
                    ? (int) $row['user_rating']
                    : null;

                return $item;
            },
            $stmt->fetchAll()
        );
    }

    /** @return UserBook[] */
    public function listPurchasesForAdmin(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                b.id,
                b.title,
                b.author,
                b.isbn,
                b.price,
                b.bonus,
                ub.purchased_at
             FROM user_books ub
             INNER JOIN books b
                ON b.id = ub.book_id
             WHERE ub.user_id = :user_id
             ORDER BY ub.purchased_at DESC'
        );

        $stmt->execute(['user_id' => $userId]);

        return array_map(
            function (array $row) use ($userId) {
                $item = new UserBook();
                $item->user_id = $userId;
                $item->id = (int) $row['id'];
                $item->book_id = (int) $row['id'];
                $item->title = (string) $row['title'];
                $item->author = (string) $row['author'];
                $item->isbn = $row['isbn'] ?? null;
                $item->price = (float) $row['price'];
                $item->bonus = (int) $row['bonus'];
                $item->purchased_at = $row['purchased_at'] ?? null;

                return $item;
            },
            $stmt->fetchAll()
        );
    }
}
