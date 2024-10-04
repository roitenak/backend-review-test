<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Event;
use App\Entity\EventType;
use Symfony\Component\Serializer\Exception\UnsupportedException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EventNormalizer implements DenormalizerInterface
{
    private const EVENT_TYPE_MAP = [
        'CommitCommentEvent' => EventType::COMMENT,
        'IssueCommentEvent' => EventType::COMMENT,
        'PullRequestEvent' => EventType::PULL_REQUEST,
        'PullRequestReviewCommentEvent' => EventType::COMMENT,
        'PushEvent' => EventType::COMMIT,
    ];

    public function __construct(private ObjectNormalizer $normalizer) {}

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return $type === Event::class;
    }

    public function denormalize($data, $class, $format = null, array $context = []): Event
    {
        if (isset($data['type']) && array_key_exists($data['type'], self::EVENT_TYPE_MAP)) {
            $data['type'] = self::EVENT_TYPE_MAP[$data['type']];
        } else {
            throw new UnsupportedException('Invalid event type');
        }

        return $this->normalizer->denormalize($data, $class, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Event::class => true];
    }
}
