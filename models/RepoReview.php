<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/Review.php';

class RepoReview
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getConnection();
    }

    public function save(Review $review): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO reviews (user_id, book_id, rating)
             VALUES (:user_id, :book_id, :rating)
             ON DUPLICATE KEY UPDATE
                rating = VALUES(rating),
                updated_at = CURRENT_TIMESTAMP'
        );

        $stmt->execute([
            'user_id' => $review->user_id,
            'book_id' => $review->book_id,
            'rating' => $review->rating
        ]);
    }

    public function getBookStats(int $bookId): Review
    {
        $stmt = $this->db->prepare(
            'SELECT
                ROUND(AVG(rating), 1) AS average_rating,
                COUNT(*) AS reviews_count
             FROM reviews
             WHERE book_id = :book_id'
        );

        $stmt->execute(['book_id' => $bookId]);
        $stats = $stmt->fetch() ?: [];

        $review = new Review();
        $review->book_id = $bookId;
        $review->average_rating = isset($stats['average_rating']) && $stats['average_rating'] !== null
            ? (float) $stats['average_rating']
            : null;
        $review->reviews_count = (int) ($stats['reviews_count'] ?? 0);

        return $review;
    }
}
