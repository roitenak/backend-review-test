<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity]
#[ORM\Table(name: '`event`', indexes: [new ORM\Index(name: 'IDX_EVENT_TYPE', columns: ['type'])])]
class Event
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'bigint')]
        #[ORM\GeneratedValue(strategy: 'NONE')]
        public int $id,

        #[ORM\Column(type: 'EventType', nullable: false)]
        public string $type,

        #[ORM\ManyToOne(targetEntity: Actor::class, cascade: ['persist'])]
        #[ORM\JoinColumn(name: 'actor_id', referencedColumnName: 'id')]
        public Actor $actor,

        #[ORM\ManyToOne(targetEntity: Repo::class, cascade: ['persist'])]
        #[ORM\JoinColumn(name: 'repo_id', referencedColumnName: 'id')]
        public Repo $repo,

        #[ORM\Column(type: 'json', nullable: false, options: ['jsonb' => true])]
        public array $payload,

        #[ORM\Column(type: 'datetime_immutable', nullable: false)]
        #[SerializedName('created_at')]
        public \DateTimeImmutable $createAt,

        #[ORM\Column(type: 'text', nullable: true)]
        public ?string $comment,

        #[ORM\Column(type: 'integer', nullable: false)]
        public int $count = 1,
    ) {
        EventType::assertValidChoice($type);
        $this->count = EventType::COMMIT === $type ? ($payload['size'] ?? 1) : 1;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'count' => $this->count,
            'repo_id' => $this->repo->id,
            'actor_id' => $this->actor->id,
            'payload' => json_encode($this->payload),
            'create_at' => $this->createAt->format('c'),
            'comment' => $this->comment,
        ];
    }
}
