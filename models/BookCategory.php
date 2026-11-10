<?php

class BookCategory implements JsonSerializable
{
    public ?int $id = null;
    public string $season = '';

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'season' => $this->season
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
