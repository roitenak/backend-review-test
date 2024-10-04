<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "repo")]
class Repo
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: "bigint_to_int")]
        #[ORM\GeneratedValue(strategy: "NONE")]
        public int $id,

        #[ORM\Column(type: "string")]
        public string $name,

        #[ORM\Column(type: "string")]
        public string $url
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['name'],
            $data['url']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->url,
        ];
    }
}
