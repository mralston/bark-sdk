<?php

declare(strict_types=1);

namespace Mralston\Bark;

use Carbon\Carbon;
use Exception;
use Generator;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Mralston\Bark\Entities\Bark;
use Mralston\Bark\Entities\Buyer;
use Mralston\Bark\Entities\Category;
use Mralston\Bark\Entities\City;
use Mralston\Bark\Entities\Purchase;
use Mralston\Bark\Entities\Quote;
use Mralston\Bark\Entities\QuoteType;
use Mralston\Bark\Entities\StatusType;
use Mralston\Bark\Exceptions\InvalidSinceDateException;
use Mralston\Bark\Exceptions\NotFoundException;
use Mralston\Bark\Exceptions\UnauthorizedException;

class Client
{
    private HttpClient $http;

    private string $apiEndpoint = 'https://api.bark.com';

    private ?string $username;
    private ?string $password;

    private ?string $accessToken;
    private ?Carbon $accessTokenExpires;

    private ?string $companyId;

    private ?string $webhookSecret;

    private array $httpOptions = [
//        'curl' => [
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
//        ],
        'proxy' => 'http://localhost:8080',
        'verify' => false,
//        'version' => 1.1,
    ];

    /**
     * @param string $client_id
     * @param string $secret
     * @param string $apiEndpoint
     */
    public function __construct(
        string $client_id,
        string $secret,
        string $apiEndpoint = 'https://api.bark.com'
    ) {
        $this->client_id = $client_id;
        $this->secret = $secret;

        $this->apiEndpoint = $apiEndpoint;

        $this->prepareHttpClient();
    }

    /**
     * @param bool $force
     * @return bool
     * @throws Exception
     */
    public function auth(bool $force = false): bool
    {
        if (
            !empty($this->accessToken) &&
            $this->accessTokenExpires->isAfter(Carbon::now()) &&
            !$force
        ) {
            return false;
        }

        $this->accessToken = null;
        $this->accessTokenExpires = null;

        $formParams = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->secret
        ];

        $response = $this->http->post($this->apiEndpoint . '/oauth/token', [
            ...$this->httpOptions,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => $formParams
        ]);

        if ($response->getStatusCode() != 200) {
            throw new Exception('Bark authentication failed.');
        }

        $json = json_decode($response->getBody()->getContents());

        $this->accessToken = $json->access_token;
        $this->accessTokenExpires = Carbon::now()->addSeconds($json->expires_in);

        return true;
    }

    public function countBarks(
        ?string $categoryId = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $distanceMi = null,
        ?string $cityId = null,
        ?string $sinceDate = null
    ) {
        $json = $this->requestBarks(
            '/seller/barks',
            1,
            $categoryId,
            $latitude,
            $longitude,
            $distanceMi,
            $cityId,
            $sinceDate,
        );

        return $json->data->total;
    }

    /**
     * Returns a list of currently active Barks (leads) that match the logged in seller’s service area
     *
     * @return Generator
     * @throws Exception
     */
    public function listBarks(
        ?string $categoryId = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $distanceMi = null,
        ?string $cityId = null,
        ?string $sinceDate = null
    ): Generator {

        $page = 1;

        while (true) {
            $json = $this->requestBarks(
                '/seller/barks',
                $page,
                $categoryId,
                $latitude,
                $longitude,
                $distanceMi,
                $cityId,
                $sinceDate,
            );

            if ($json->data->total == 0) {
                return;
            }

            foreach ($json->data->items as $bark) {
                yield new Bark(
                    $bark,
                    $this
                );
            }

            $page++;
        }
    }

    /**
     * Returns a list of currently active Barks (leads), regardless of whether
     * they are in the logged in user's service areas or not.
     *
     * @return Generator
     * @throws Exception
     */
    public function searchBarks(
        ?string $categoryId = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $distanceMi = null,
        ?string $cityId = null,
        ?string $sinceDate = null
    ): Generator {

        $page = 1;

        while (true) {
            $json = $this->requestBarks(
                '/seller/barks',
                $page,
                $categoryId,
                $latitude,
                $longitude,
                $distanceMi,
                $cityId,
                $sinceDate,
            );

            if ($json->data->total == 0) {
                return;
            }

            foreach ($json->data->items as $bark) {
                yield new Bark(
                    $bark,
                    $this
                );
            }

            $page++;
        }
    }

    private function requestBarks(
        string $endpoint,
        int $page,
        ?string $categoryId = null,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $distanceMi = null,
        ?string $cityId = null,
        ?string $sinceDate = null,
    ) {
        $this->auth();

        if (
            !empty($sinceDate) &&
            !in_array($sinceDate, [
                '1h',
                'today',
                'yesterday',
                '3d',
                '7d',
                '2w'
            ])
        ) {
            throw new InvalidSinceDateException();
        }

        try {
            $response = $this->http->get($this->apiEndpoint . $endpoint, [
                ...$this->httpOptions,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/vnd.bark.pub_v1+json',
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Connection' => 'close',
                ],
                'query' => [
                    'category_id' => $categoryId,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'distance_mi' => $distanceMi,
                    'city_id' => $cityId,
                    'since_date' => $sinceDate,
                    'page' => $page,
                ]
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            throw $ex;
        }

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Gets the latest information about any particular Bark.
     *
     * @param int $id
     * @return Bark
     * @throws Exception
     */
    public function showBark(int $id): Bark
    {
        $this->auth();

        try {
            $response = $this->http->get($this->apiEndpoint . '/seller/bark/' . $id, [
                ...$this->httpOptions,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/vnd.bark.pub_v1+json',
                    'Authorization' => 'Bearer ' . $this->accessToken
                ]
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            if ($ex->getCode() == 404) {
                throw new NotFoundException('Bark ' . $id . ' does not exist.', $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        return new Bark(
            $json->data,
            $this
        );
    }

    /**
     * Mark the Bark as "not interested" for the logged in seller.
     *
     * @param int $id
     * @return bool
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function notInterestedInBark(int $id): bool
    {
        $this->auth();

        try {
            $response = $this->http->post($this->apiEndpoint . '/seller/bark/' . $id . '/pass', [
                ...$this->httpOptions,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/vnd.bark.pub_v1+json',
                    'Authorization' => 'Bearer ' . $this->accessToken
                ]
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            if ($ex->getCode() == 404) {
                throw new NotFoundException('Bark ' . $id . ' does not exist.', $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        return $json->status;
    }

    /**
     * Get a list of Barks that the seller purchased, with the most recent purchases first.
     *
     * @return Generator
     * @throws InvalidSinceDateException
     * @throws UnauthorizedException
     */
    public function listPurchasedBarks(): Generator
    {
        $this->auth();

        $page = 1;

        while (true) {
            $json = $this->requestBarks(
                '/seller/barks/purchased',
                $page
            );

            if ($json->data->total == 0) {
                return;
            }

            foreach ($json->data->items as $purchase) {
                yield new Purchase(
                    $purchase,
                    $this
                );
            }

            $page++;
        }
    }

    public function showPurchasedBark($id)
    {
        // /seller/bark/purchased/{bark_id}
    }

    /**
     * Purchases a Bark on behalf of the seller. The buyer will be notified by email that the seller is interested
     * in their business, and the credits required will be debited from the seller's account. If the seller has one
     * click enabled on their account, $oneClick will send their one-click message.
     *
     * @param int $id
     * @param bool $oneClick
     * @return false|Buyer
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function purchaseBark(int $id, bool $oneClick = false)
    {
        $this->auth();
echo($this->apiEndpoint . '/seller/bark/' . $id . '/purchase' . ($oneClick ? '/one-click' : '') . "\n");
        try {
            $response = $this->http->post($this->apiEndpoint . '/seller/bark/' . $id . '/purchase' . ($oneClick ? '/one-click' : ''), [
                ...$this->httpOptions,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/vnd.bark.pub_v1+json',
                    'Authorization' => 'Bearer ' . $this->accessToken
                ]
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            if ($ex->getCode() == 404) {
                throw new NotFoundException('Bark ' . $id . ' does not exist.', $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        if (!$json->status) {
            return false;
        }

        return new Buyer(
            $json->buyer ?? $json->buyerInfo,
            $this
        );
    }

    public function setBarkQuote(int $id, Quote $quote)
    {
        $this->auth();

        try {
            $response = $this->http->post($this->apiEndpoint . '/seller/bark/' . $id . '/set-quote', [
                ...$this->httpOptions,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/vnd.bark.pub_v1+json',
                    'Authorization' => 'Bearer ' . $this->accessToken
                ],
                'form_params' => [
                    'quote' => $quote->value,
                    'type' => $quote->type,
                    'detail' => $quote->detail,
                ]
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            if ($ex->getCode() == 404) {
                throw new NotFoundException('Bark ' . $id . ' does not exist.', $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        return $json->status;
    }

    /**
     * Updates a purchased bark's status.
     *
     * @param int $id
     * @param int $status_id
     * @return mixed
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function setBarkStatus(int $id, int $status_id)
    {
        $this->auth();

        try {
            $response = $this->http->post($this->apiEndpoint . '/seller/bark/' . $id . '/set-status', [
                ...$this->httpOptions,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/vnd.bark.pub_v1+json',
                    'Authorization' => 'Bearer ' . $this->accessToken
                ],
                'form_params' => [
                    'status_id' => $status_id,
                ]
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            if ($ex->getCode() == 404) {
                throw new NotFoundException('Bark ' . $id . ' does not exist.', $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        return $json->status;
    }

    /**
     * returns a list of major cities in Bark's active countries, to make it easier to filter
     * listBarks and searchBarks, rather than using raw latitude and longitude.
     *
     * @return Generator
     * @throws Exception
     */
    public function listCities(): array
    {
        $this->auth();

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/vnd.bark.pub_v1+json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        try {
            $response = $this->http->get($this->apiEndpoint . '/lookups/cities', [
                ...$this->httpOptions,
                'headers' => $headers
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        if (count($json->data) == 0) {
            return [];
        }

        $cities = [];

        foreach ($json->data as $city) {
            $cities[] = new City(
                $city,
                $this
            );
        }

        return $cities;
    }

    /**
     * Returns a list of all the quote types currently supported by Bark, which can be used when
     * creating a Quote for use with setBarkQuote.
     *
     * @return Generator
     * @throws Exception
     */
    public function listQuoteTypes(): array
    {
        $this->auth();

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/vnd.bark.pub_v1+json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        try {
            $response = $this->http->get($this->apiEndpoint . '/lookups/quote-types', [
                ...$this->httpOptions,
                'headers' => $headers
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        if (count($json->data) == 0) {
            return [];
        }

        $quoteTypes = [];

        foreach ($json->data as $quoteType) {
            $quoteTypes[] = new QuoteType(
                $quoteType,
                $this
            );
        }

        return $quoteTypes;
    }

    /**
     * Returns a list of all the status types currently supported by Bark, which can be used
     * with setBarkStatus.
     *
     * @return Generator
     * @throws Exception
     */
    public function listStatusTypes(): array
    {
        $this->auth();

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/vnd.bark.pub_v1+json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        try {
            $response = $this->http->get($this->apiEndpoint . '/lookups/status-types', [
                ...$this->httpOptions,
                'headers' => $headers
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        if (count($json->data) == 0) {
            return [];
        }

        $statusTypes = [];

        foreach ($json->data as $statusType) {
            $statusTypes[] = new StatusType(
                $statusType,
                $this
            );
        }

        return $statusTypes;
    }

    /**
     * @return Generator
     * @throws Exception
     */
    public function listCategories(): array
    {
        $this->auth();


        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/vnd.bark.pub_v1+json',
            'Authorization' => 'Bearer ' . $this->accessToken
        ];

        try {
            $response = $this->http->get($this->apiEndpoint . '/lookups/categories', [
                ...$this->httpOptions,
                'headers' => $headers
            ]);
        } catch (ClientException $ex) {
            if ($ex->getCode() == 401) {
                $this->accessToken = null;
                $this->accessTokenExpires = null;

                throw new UnauthorizedException($ex->getMessage(), $ex->getCode());
            }

            throw $ex;
        }

        $json = json_decode($response->getBody()->getContents());

        if (count($json->data) == 0) {
            return [];
        }

        $categories = [];

        foreach ($json->data as $category) {
            $categories[] = new Category(
                $category,
                $this
            );
        }

        return $categories;
    }




    private function prepareHttpClient()
    {
        $this->http = new HttpClient([
            'timeout' => 10
        ]);
    }
}
