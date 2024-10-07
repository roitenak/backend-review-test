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
    private const GH_EVENT_TYPE_COMMIT_COMMENT = 'CommitCommentEvent';
    private const GH_EVENT_TYPE_ISSUE_COMMENT = 'IssueCommentEvent';
    private const GH_EVENT_TYPE_PULL_REQUEST = 'PullRequestEvent';
    private const GH_EVENT_TYPE_PULL_REQUEST_REVIEW_COMMENT = 'PullRequestReviewCommentEvent';
    private const GH_EVENT_TYPE_PUSH = 'PushEvent';

    private const EVENT_TYPE_MAP = [
        self::GH_EVENT_TYPE_COMMIT_COMMENT => EventType::COMMENT,
        self::GH_EVENT_TYPE_ISSUE_COMMENT => EventType::COMMENT,
        self::GH_EVENT_TYPE_PULL_REQUEST => EventType::PULL_REQUEST,
        self::GH_EVENT_TYPE_PULL_REQUEST_REVIEW_COMMENT => EventType::COMMENT,
        self::GH_EVENT_TYPE_PUSH => EventType::COMMIT,
    ];

    public function __construct(private readonly ObjectNormalizer $normalizer)
    {
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return Event::class === $type;
    }

    public function denormalize($data, $class, $format = null, array $context = []): Event
    {
        if (isset($data['type']) && array_key_exists($data['type'], self::EVENT_TYPE_MAP)) {
            match ($data['type']) {
                self::GH_EVENT_TYPE_COMMIT_COMMENT,
                self::GH_EVENT_TYPE_PULL_REQUEST_REVIEW_COMMENT,
                self::GH_EVENT_TYPE_ISSUE_COMMENT => $data['comment'] = $data['payload']['comment']['body'],
                default => null,
            };

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
