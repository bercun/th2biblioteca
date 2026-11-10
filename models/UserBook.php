<?php

class UserBook implements JsonSerializable
{
    public ?int $user_id = null;
    public ?int $book_id = null;
    public ?string $purchased_at = null;

    // Joined book fields for owned/purchase lists
    public ?int $id = null;
    public string $title = '';
    public string $author = '';
    public ?string $isbn = null;
    public float $price = 0.0;
    public int $bonus = 0;
    public ?int $user_rating = null;

    public function toArray(): array
    {
        return [
            'id' => $this->id ?? $this->book_id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'price' => $this->price,
            'bonus' => $this->bonus,
            'purchased_at' => $this->purchased_at,
            'user_rating' => $this->user_rating
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
