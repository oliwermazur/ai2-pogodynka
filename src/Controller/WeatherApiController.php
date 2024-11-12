<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpFoundation\Request;
use App\Service\WeatherUtil;
use App\Entity\Measurement;
use Symfony\Component\HttpFoundation\Response;

class WeatherApiController extends AbstractController
{
    private WeatherUtil $weatherUtil;

    public function __construct(WeatherUtil $weatherUtil)
    {
        $this->weatherUtil = $weatherUtil;
    }

    #[Route('/api/v1/weather', name: 'app_weather_api', methods: ['GET'])]
    public function index(
        #[MapQueryParameter] string $country = null,
        #[MapQueryParameter] string $city = null,
        #[MapQueryParameter] string $format = 'json', // Nowy parametr format
        #[MapQueryParameter('twig')] bool $twig = false  // Parametr twig, domyślnie false
    ): Response {
        // Sprawdzamy, czy parametry country i city są dostępne
        if (!$country || !$city) {
            return $this->json(['error' => 'Both "country" and "city" parameters are required.'], 400);
        }

        // Pobieramy dane z serwisu WeatherUtil
        try {
            $measurements = $this->weatherUtil->getWeatherForCountryAndCity($country, $city);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        // Jeśli format to CSV
        if ($format === 'csv') {
            // Jeśli parametr twig jest ustawiony na true, renderujemy odpowiedź za pomocą TWIG
            if ($twig) {
                return $this->render('weather_api/index.csv.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $measurements,
                ]);
            }

            // Generowanie CSV
            $csvData = array_map(function (Measurement $m) use ($city, $country) {
                return sprintf(
                    '"%s","%s","%s","%s","%s"',
                    $city, // Miejscowość (redundantnie)
                    $country, // Kraj (redundantnie)
                    $m->getDate()->format('Y-m-d'), // Data
                    $m->getCelsius(), // Temperatura w Celsjuszu
                    $m->getFahrenheit() // Temperatura w Fahrenheitach
                );
            }, $measurements);

            // Łączenie wierszy CSV
            $csvContent = implode("\n", $csvData);

            // Tworzymy odpowiedź z nagłówkami dla CSV
            return new Response(
                $csvContent,
                Response::HTTP_OK,
                ['Content-Type' => 'text/csv']
            );
        }

        // Jeśli format to JSON
        if ($format === 'json') {
            // Jeśli parametr twig jest ustawiony na true, renderujemy odpowiedź za pomocą TWIG
            if ($twig) {
                return $this->render('weather_api/index.json.twig', [
                    'city' => $city,
                    'country' => $country,
                    'measurements' => $measurements,
                ]);
            }

            // Generowanie JSON (domyślnie)
            $measurementsData = array_map(fn(Measurement $m) => [
                'date' => $m->getDate()->format('Y-m-d'),
                'celsius' => $m->getCelsius(),
                'fahrenheit' => $m->getFahrenheit(), // Dodajemy temperaturę w Fahrenheitach
            ], $measurements);

            return $this->json([
                'city' => $city,
                'country' => $country,
                'measurements' => $measurementsData,
            ]);
        }

        return $this->json(['error' => 'Invalid format'], 400);
    }
}

