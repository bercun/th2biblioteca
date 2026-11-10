<?php

class Review implements JsonSerializable
{
    public ?int $id = null;
    public ?int $user_id = null;
    public ?int $book_id = null;
    public int $rating = 0;
    public ?float $average_rating = null;
    public int $reviews_count = 0;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'book_id' => $this->book_id,
            'rating' => $this->rating,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
