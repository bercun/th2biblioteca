<?php

require_once __DIR__ . '/../config/paths.php';

class Book implements JsonSerializable
{
    public const DEFAULT_IMAGE_PATH = 'front/assets/images/bookDefault.png';

    public ?int $id = null;
    public string $title = '';
    public string $author = '';
    public ?string $isbn = null;
    public string $description = '';
    public ?int $category_id = null;
    public ?string $season = null;
    public string $image = self::DEFAULT_IMAGE_PATH;
    public int $bonus = 1;
    public ?float $price = 1.00;
    public ?float $average_rating = null;
    public int $reviews_count = 0;

    /** Portable value stored in the DB (no host/base prefix). */
    public static function normalizeImage(?string $image): string
    {
        $image = trim((string) $image);

        if ($image === '') {
            return self::DEFAULT_IMAGE_PATH;
        }

        $base = basePath();
        if ($base !== '' && str_starts_with($image, $base . '/front/')) {
            $image = substr($image, strlen($base) + 1);
        }

        if (str_starts_with($image, '/biblioteca/front/')) {
            $image = substr($image, strlen('/biblioteca/'));
        }

        if (str_starts_with($image, '/front/')) {
            $image = substr($image, 1);
        }

        if (str_starts_with($image, '../front/')) {
            $image = substr($image, 3);
        }

        return $image;
    }

    public static function resolveImage(?string $image): string
    {
        $image = self::normalizeImage($image);

        if (str_starts_with($image, 'front/')) {
            return basePath() . '/' . $image;
        }

        return $image;
    }



    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'season' => $this->season,
            'image' => self::resolveImage($this->image),
            'bonus' => $this->bonus,
            'price' => $this->price,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
