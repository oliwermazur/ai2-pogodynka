<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Location;
use App\Repository\MeasurementRepository;
use App\Repository\LocationRepository;
use App\Service\WeatherUtil;

class WeatherController extends AbstractController
{
    private WeatherUtil $weatherUtil;

    public function __construct(WeatherUtil $weatherUtil)
    {
        $this->weatherUtil = $weatherUtil;
    }

    #[Route('/weather/{city}', name: 'app_weather')]
    public function city(string $city, LocationRepository $locationRepository, MeasurementRepository $repository): Response
    {

        $location = $locationRepository->findOneBy(['city' => $city]);

        if (!$location) {
            throw $this->createNotFoundException('Nie znaleziono danych dla miasta');
        }

        $measurements = $this->weatherUtil->getWeatherForLocation($location);

        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }
}
