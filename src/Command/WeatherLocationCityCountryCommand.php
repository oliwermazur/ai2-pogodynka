<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Service\WeatherUtil;
use App\Repository\LocationRepository;

#[AsCommand(
    name: 'weather:location-city-country',
    description: 'Add a short description for your command',
)]
class WeatherLocationCityCountryCommand extends Command
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
            ->addArgument('countryCode', InputArgument::REQUIRED)
            ->addArgument('cityName', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $countryCode = $input->getArgument('countryCode');
        $cityName = $input->getArgument('cityName');

        try {
            $measurements = $this->weatherUtil->getWeatherForCountryAndCity($countryCode, $cityName);

            if (empty($measurements)) {
                $io->warning('No weather data available for this location.');
                return Command::SUCCESS;
            }

            $io->writeln(sprintf('Location: %s, %s', $cityName, $countryCode));

            foreach ($measurements as $measurement) {
                $io->writeln(sprintf(
                    '        %s: %s°C',
                    $measurement->getDate()->format('Y-m-d'),
                    $measurement->getCelsius()
                ));
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
