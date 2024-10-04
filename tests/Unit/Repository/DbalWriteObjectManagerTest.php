<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Repository\DbalWriteObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DbalWriteObjectManagerTest extends TestCase
{
    private Connection&MockObject $connection;
    private DbalWriteObjectManager $dbalWriteObjectManager;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->dbalWriteObjectManager = new DbalWriteObjectManager($this->connection);
    }

    public function testUpsertRepo()
    {
        $repo = new Repo(1, 'test-repo', 'http://example.com/repo');

        $statement = $this->createMock(Statement::class);
        $statement->expects($this->once())
            ->method('executeStatement')
            ->with([1, 'test-repo', 'http://example.com/repo']);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO repo (id, name, url) VALUES (?, ?, ?) ON CONFLICT (id) DO UPDATE SET name = excluded.name, url = excluded.url')
            ->willReturn($statement);

        $this->dbalWriteObjectManager->upsert(Repo::class, [$repo]);
    }

    public function testUpsertActor()
    {
        $actor = new Actor(1, 'test-actor', 'http://example.com/actor', 'http://example.com/avatar');

        $statement = $this->createMock(Statement::class);
        $statement->expects($this->once())
            ->method('executeStatement')
            ->with([1, 'test-actor', 'http://example.com/actor', 'http://example.com/avatar']);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO actor (id, login, url, avatar_url) VALUES (?, ?, ?, ?) ON CONFLICT (id) DO UPDATE SET login = excluded.login, url = excluded.url, avatar_url = excluded.avatar_url')
            ->willReturn($statement);

        $this->dbalWriteObjectManager->upsert(Actor::class, [$actor]);
    }

    public function testUpsertEvent()
    {
        $repo = new Repo(1, 'test-repo', 'http://example.com/repo');
        $actor = new Actor(1, 'test-actor', 'http://example.com/actor', 'http://example.com/avatar');
        $event = new Event(1, 'COM', $actor, $repo, ['payload' => 'value'], new \DateTimeImmutable(), 'comment');

        $statement = $this->createMock(Statement::class);
        $statement->expects($this->once())
            ->method('executeStatement')
            ->with([1, 'COM', 1, 1, 1, '{"payload":"value"}', $event->createAt->format('c'), 'comment']);

        $this->connection->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO event (id, type, count, repo_id, actor_id, payload, create_at, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON CONFLICT (id) DO NOTHING')
            ->willReturn($statement);

        $this->dbalWriteObjectManager->upsert(Event::class, [$event]);
    }
}
