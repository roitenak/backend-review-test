<?php

namespace App\Controller;

use App\Dto\SearchInput;
use App\Dto\SearchOutput;
use App\Dto\SearchOutput\Data;
use App\Dto\SearchOutput\Meta;
use App\Repository\ReadEventRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class SearchController
{
    public function __construct(
        private readonly ReadEventRepository $repository,
        private readonly SerializerInterface&DenormalizerInterface $serializer,
    ) {
    }

    #[Route(path: '/api/search', name: 'api_search', methods: ['GET'])]
    public function searchCommits(Request $request): JsonResponse
    {
        // add validate input
        $searchInput = $this->serializer->denormalize($request->query->all(), SearchInput::class);

        $countByType = $this->repository->countByType($searchInput);

        $output = new SearchOutput(
            meta: new Meta(
                totalEvents: $this->repository->countAll($searchInput),
                totalPullRequests: $countByType['pullRequest'] ?? 0,
                totalCommits: $countByType['commit'] ?? 0,
                totalComments: $countByType['comment'] ?? 0,
            ),
            data: new Data(
                events: $this->repository->getLatest($searchInput),
                stats: $this->repository->statsByTypePerHour($searchInput),
            ),
        );

        return new JsonResponse($output);
    }
}
