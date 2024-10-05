<?php

declare(strict_types=1);

namespace App\Command;

use App\Client\GHArchiveClient;
use App\Entity\Actor;
use App\Entity\Event;
use App\Entity\Repo;
use App\Parser\GzipJsonParser;
use App\Repository\DbalWriteObjectManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Exception\UnsupportedException;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events',
)]
class ImportGitHubEventsCommand extends Command
{
    private const BATCH_SIZE = 1000;
    private const INVALID_DATE_MESSAGE = 'Invalid date format. Please use Y-m-d format.';
    private const INVALID_HOUR_MESSAGE = 'Invalid hour format. Please provide an hour between 0 and 23.';

    public function __construct(
        private readonly GHArchiveClient $ghArchiveClient,
        private readonly GzipJsonParser $parser,
        private readonly SerializerInterface $serializer,
        private readonly DbalWriteObjectManager $objectManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $now = $this->getDefaultDate();

        $this
            ->setDescription('Import GH events')
            ->setHelp('This command allows you to import GH events')
            ->addArgument('date', InputArgument::OPTIONAL, 'Date of the events to import', $now->format('Y-m-d'))
            ->addArgument('hour', InputArgument::OPTIONAL, 'Hour of the events to import', $now->format('G'))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');
        $hour = $input->getArgument('hour');

        $this->validate($date, $hour);

        $output->writeln(sprintf('Importing %s-%s.json.gz : Downloading...', $date, $hour));

        $eventsFile = $this->ghArchiveClient->downloadEvents($date, $hour);

        $output->writeln(sprintf('Importing %s-%s.json.gz : Processing...', $date, $hour));

        $rows = $this->parser->parse($eventsFile);

        $repos = [];
        $actors = [];
        $events = [];
        $n = 0;
        $discarded = 0;
        $errors = 0;
        foreach ($rows as $row) {
            try {
                $event = $this->serializer->deserialize($row, Event::class, 'json');
                $repo = $event->repo;
                $actor = $event->actor;

                $repos[$repo->id] = $repo;
                $actors[$actor->id] = $actor;
                $events[$event->id] = $event;

                if (0 === $n % self::BATCH_SIZE) {
                    $this->processBatch($events, $repos, $actors);
                    $output->writeln('[Info] Flushed after processing '.$n.' events.', OutputInterface::VERBOSITY_VERY_VERBOSE);
                }
            } catch (UnsupportedException $e) {
                ++$discarded;
                $output->writeln(sprintf('[Warning] While processing event: %s', $e->getMessage()), OutputInterface::VERBOSITY_VERY_VERBOSE);
            } catch (\Throwable $e) {
                ++$errors;
                $output->writeln(sprintf('[Error] While processing event: %s', $e->getMessage()));
            } finally {
                ++$n;
            }
        }

        $this->processBatch($events, $repos, $actors);

        $output->writeln(sprintf('Importing %s-%s.json.gz : Done!', $date, $hour));
        $output->writeln("$n events, $discarded discarded, $errors errors.");

        return Command::SUCCESS;
    }

    private function processBatch(array &$events, array &$repos, array &$actors): void
    {
        $this->objectManager->upsert(Repo::class, $repos);
        $this->objectManager->upsert(Actor::class, $actors);
        $this->objectManager->upsert(Event::class, $events);

        $repos = [];
        $actors = [];
        $events = [];
    }

    private function validate(string $date, string $hour): void
    {
        if (!$this->isValidDate($date)) {
            throw new \InvalidArgumentException(self::INVALID_DATE_MESSAGE);
        }

        if (!$this->isValidHour($hour)) {
            throw new \InvalidArgumentException(self::INVALID_HOUR_MESSAGE);
        }
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $d && $d->format('Y-m-d') === $date;
    }

    private function isValidHour(string $hour): bool
    {
        return is_numeric($hour) && (int) $hour >= 0 && (int) $hour <= 23;
    }

    private function getDefaultDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('-1 hour');
    }
}
