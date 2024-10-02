<?php

declare(strict_types=1);

namespace App\Tests\Unit\Normalizer;

use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Normalizer\EventNormalizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\UnsupportedException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EventNormalizerTest extends TestCase
{
    private MockObject&ObjectNormalizer $normalizer;
    private EventNormalizer $eventNormalizer;

    protected function setUp(): void
    {
        $this->normalizer = $this->createMock(ObjectNormalizer::class);
        $this->eventNormalizer = new EventNormalizer($this->normalizer);
    }

    public function testSupportsDenormalization(): void
    {
        $data = [];
        $type = Event::class;
        $format = null;
        $context = [];

        $result = $this->eventNormalizer->supportsDenormalization($data, $type, $format, $context);

        $this->assertTrue($result);
    }

    public function testDoesNotSupportDenormalization(): void
    {
        $data = [];
        $type = 'SomeOtherClass';
        $format = null;
        $context = [];

        $result = $this->eventNormalizer->supportsDenormalization($data, $type, $format, $context);

        $this->assertFalse($result);
    }

    public function testDenormalizeWithValidEventType(): void
    {
        $data = ['type' => 'CommitCommentEvent'];
        $class = Event::class;
        $format = null;
        $context = [];

        $this->normalizer->expects($this->once())
            ->method('denormalize')
            ->with(['type' => 'MSG'], $class, $format, $context)
            ->willReturn(new Event(
                1,
                'MSG',
                new Actor(1, 'login', 'avatar', 'url'),
                new Repo(1, 'name', 'url'),
                [],
                new \DateTimeImmutable(),
                null
            ))
        ;

        $result = $this->eventNormalizer->denormalize($data, $class, $format, $context);

        $this->assertInstanceOf(Event::class, $result);
    }

    public function testDenormalizeWithInvalidEventType(): void
    {
        $this->expectException(UnsupportedException::class);
        $this->expectExceptionMessage('Invalid event type');

        $data = ['type' => 'InvalidEventType'];
        $class = Event::class;
        $format = null;
        $context = [];

        $this->eventNormalizer->denormalize($data, $class, $format, $context);
    }
}
