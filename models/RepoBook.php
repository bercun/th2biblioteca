<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/Book.php';

class RepoBook
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getConnection();
    }

    public function findById(int $id): ?Book
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                title,
                author,
                isbn,
                description,
                category_id,
                image,
                bonus,
                price
             FROM books
             WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRow($row) : null;
    }

    public function findForPurchase(int $id): ?Book
    {
        $stmt = $this->db->prepare(
            'SELECT
                id,
                title,
                author,
                bonus,
                price
             FROM books
             WHERE id = :id'
        );

        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? $this->mapRow($row) : null;
    }

    /** @return Book[] */
    public function search(string $query): array
    {
        $isbnDigits = preg_replace('/[^0-9Xx]/', '', $query);

        $sql = "
            SELECT
                b.id,
                b.title,
                b.author,
                b.isbn,
                b.description,
                b.image,
                b.bonus,
                b.price,
                ROUND(AVG(r.rating), 1) AS average_rating,
                COUNT(r.id) AS reviews_count
            FROM books b
            LEFT JOIN reviews r
                ON r.book_id = b.id
            WHERE b.title LIKE :query
               OR b.author LIKE :query
               OR b.isbn LIKE :query
               OR (
                    :isbn_digits <> ''
                    AND REPLACE(REPLACE(b.isbn, '-', ''), ' ', '') LIKE :isbn_like
               )
            GROUP BY
                b.id,
                b.title,
                b.author,
                b.isbn,
                b.description,
                b.image,
                b.bonus,
                b.price
            ORDER BY b.title
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'query' => '%' . $query . '%',
            'isbn_digits' => $isbnDigits,
            'isbn_like' => '%' . $isbnDigits . '%'
        ]);

        return $this->mapRows($stmt->fetchAll());
    }

    /** @return Book[] */
    public function listBySeason(?string $season = null): array
    {
        $sql = "
            SELECT
                b.id,
                b.title,
                b.author,
                b.isbn,
                b.description,
                c.season,
                b.image,
                b.bonus,
                b.price,
                ROUND(AVG(r.rating), 1) AS average_rating,
                COUNT(r.id) AS reviews_count
            FROM books b
            INNER JOIN books_category c
                ON c.id = b.category_id
            LEFT JOIN reviews r
                ON r.book_id = b.id
        ";

        $params = [];

        if ($season !== null && $season !== '') {
            $sql .= ' WHERE c.season = :season';
            $params['season'] = $season;
        }

        $sql .= '
            GROUP BY
                b.id,
                b.title,
                b.author,
                b.isbn,
                b.description,
                c.season,
                b.image,
                b.bonus,
                b.price
            ORDER BY b.id
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $this->mapRows($stmt->fetchAll());
    }

    /** @return Book[] */
    public function listForAdmin(): array
    {
        $sql = "
            SELECT
                b.id,
                b.title,
                b.author,
                b.isbn,
                b.description,
                b.category_id,
                c.season,
                b.image,
                b.bonus,
                b.price
            FROM books b
            INNER JOIN books_category c
                ON c.id = b.category_id
            ORDER BY b.id
        ";

        return $this->mapRows($this->db->query($sql)->fetchAll());
    }

    public function create(Book $book): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO books
                (title, author, isbn, description, category_id, image, bonus, price)
             VALUES
                (:title, :author, :isbn, :description, :category_id, :image, :bonus, :price)'
        );

        $book->image = Book::normalizeImage($book->image);

        $stmt->execute([
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'description' => $book->description,
            'category_id' => $book->category_id,
            'image' => $book->image,
            'bonus' => $book->bonus,
            'price' => $book->price
        ]);

        $book->id = (int) $this->db->lastInsertId();

        return $book->id;
    }

    public function update(Book $book): void
    {
        if ($book->id === null) {
            throw new RuntimeException('Book id is required for update');
        }

        $stmt = $this->db->prepare(
            'UPDATE books
             SET
                title = :title,
                author = :author,
                isbn = :isbn,
                description = :description,
                category_id = :category_id,
                image = :image,
                bonus = :bonus,
                price = :price
             WHERE id = :id'
        );

        $book->image = Book::normalizeImage($book->image);

        $stmt->execute([
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'description' => $book->description,
            'category_id' => $book->category_id,
            'image' => $book->image,
            'bonus' => $book->bonus,
            'price' => $book->price,
            'id' => $book->id
        ]);
    }

    public function delete(int $bookId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM books WHERE id = :id'
        );

        $stmt->execute(['id' => $bookId]);

        return $stmt->rowCount() > 0;
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function mapRows(array $rows): array
    {
        return array_map(
            fn(array $row) => $this->mapRow($row),
            $rows
        );
    }

    private function mapRow(array $row): Book
    {
        $book = new Book();
        $book->id = isset($row['id']) ? (int) $row['id'] : null;
        $book->title = (string) ($row['title'] ?? '');
        $book->author = (string) ($row['author'] ?? '');
        $book->isbn = $row['isbn'] ?? null;
        $book->description = (string) ($row['description'] ?? '');
        $book->category_id = isset($row['category_id'])
            ? (int) $row['category_id']
            : null;
        $book->season = $row['season'] ?? null;
        $book->image = Book::resolveImage($row['image'] ?? null);
        $book->bonus = (int) ($row['bonus'] ?? 1);
        $book->price = (float) ($row['price'] ?? 1);
        $book->reviews_count = (int) ($row['reviews_count'] ?? 0);
        $book->average_rating = isset($row['average_rating']) && $row['average_rating'] !== null
            ? (float) $row['average_rating']
            : null;

        return $book;
    }
}
