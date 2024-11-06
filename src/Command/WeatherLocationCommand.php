<?php

namespace App\Command;

use App\Service\WeatherUtil;
use App\Repository\LocationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:location',
    description: 'Fetch weather forecast for a given location',
)]
class WeatherLocationCommand extends Command
{
    private WeatherUtil $weatherUtil;
    private LocationRepository $locationRepository;

    public function __construct(WeatherUtil $weatherUtil, LocationRepository $locationRepository)
    {
        $this->weatherUtil = $weatherUtil;
        $this->locationRepository = $locationRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('locationId', InputArgument::REQUIRED, 'The ID of the location')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locationId = $input->getArgument('locationId');

        $location = $this->locationRepository->find($locationId);

        if (!$location) {
            $io->error('Location not found!');
            return Command::FAILURE;
        }

        try {
            $measurements = $this->weatherUtil->getWeatherForLocation($location);

            if (empty($measurements)) {
                $io->warning('No weather data available for this location.');
                return Command::SUCCESS;
            }

            $io->writeln('Location: ' . $location->getCity());

            foreach ($measurements as $measurement) {
                $io->writeln(sprintf('        %s: %s',
                    $measurement->getDate()->format('Y-m-d'),
                    $measurement->getCelsius()
                ));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error fetching weather data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
