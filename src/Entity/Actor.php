<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity]
#[ORM\Table(name: 'actor')]
class Actor
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'bigint_to_int')]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        public int $id,

        #[ORM\Column(type: 'string')]
        public string $login,

        #[ORM\Column(type: 'string')]
        public string $url,

        #[ORM\Column(type: 'string')]
        #[SerializedName('avatar_url')]
        public string $avatarUrl,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['login'],
            $data['url'],
            $data['avatar_url']
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'url' => $this->url,
            'avatar_url' => $this->avatarUrl,
        ];
    }
}
