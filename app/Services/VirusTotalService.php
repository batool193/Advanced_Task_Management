<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class VirusTotalService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = 'e45ea0c8bf22dc0297265bd7f3685b2b3c6e1912f7fbd3a50501be2bcd7afb3d';
    }



/**
 * Scans a file for viruses using VirusTotal API.
 *
 * @param $file The file to be scanned
 * @return array The analysis result
 * @throws Exception If an unexpected response structure is encountered
 */
public function scanFile($file) {
    $client = new \GuzzleHttp\Client();
    $response = $client->post('https://www.virustotal.com/api/v3/files', [
        'headers' => [
            'X-Apikey' => $this->apiKey,
        ],
        'multipart' => [
            [
                'name' => 'file',
                'contents' => fopen($file->getRealPath(), 'r'),
                'filename' => $file->getClientOriginalName(),
            ],
        ],
    ]);

    $result = json_decode($response->getBody(), true);
    Log::info('VirusTotal Response: ', $result);
    if (isset($result['data']['id'])) {
        // Fetch analysis details using the id
        $analysisId = $result['data']['id'];
        $analysisResponse = $client->get("https://www.virustotal.com/api/v3/analyses/{$analysisId}", [
            'headers' => [
                'X-Apikey' => $this->apiKey,
            ],
        ]);

        $analysisResult = json_decode($analysisResponse->getBody(), true);
        return $analysisResult;
    } else {
        throw new Exception('Unexpected response structure');
    }

}
}
