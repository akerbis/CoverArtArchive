<?php

/**
 * Cover Art Archive PHP library
 *
 * @license MIT
 */

namespace CoverArtArchive;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

/**
 * Connect to the Cover Art Archive web service
 *
 * https://musicbrainz.org/doc/Cover_Art_Archive/API
 *
 * @link https://github.com/dehy/CoverArtArchive
 */
class CoverArt
{
    private const URL = 'https://coverartarchive.org';

    /**
     * Stores an array of CoverArtImage objects
     * @var CoverArtImage[]
     */
    public $images = [];

    /**
     * The CoverArtImage object for the front image
     * @var CoverArtImage
     */
    public $front;

    /**
     * The CoverArtImage object for the back image
     * @var CoverArtImage
     */
    public $back;

    /**
     * The Music Brainz Id of the album
     * @var string
     */
    private $mbid;

    /**
     * The Guzzle client used to make cURL requests
     *
     * @var ClientInterface
     */
    private $client;

    /**
     * Retrieves an array of images based on a
     * Music Brainz ID
     *
     * @param string $mbid
     * @param ClientInterface $client
     * @throws Exception|GuzzleException
     */
    public function __construct(string $mbid, ClientInterface $client)
    {
        $this->client = $client;

        $this->setMBID($mbid);
        $this->retrieveImages();
    }

    /**
     * Sets the MusicBrainzID
     *
     * @param string $mbid
     */
    public function setMBID(string $mbid): void
    {
        if (!self::isValidMBID($mbid)) {
            throw new InvalidArgumentException('Invalid Music Brainz ID');
        }

        $this->mbid = $mbid;
    }

    /**
     * Checks to see if a supplied MBID is a valid UUID
     *
     * @param  string $mbid
     *
     * @return bool
     */
    public static function isValidMBID(string $mbid): bool
    {
        return preg_match("/^({)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)})$/i", $mbid);
    }

    /**
     * Retrieves an array of images based on a
     * Music Brainz ID
     *
     * @return CoverArt
     * @throws Exception
     * @throws GuzzleException
     */
    public function retrieveImages(): CoverArt
    {
        $response = $this->call('/release/' . $this->getMBID());

        if (isset($response['images'])) {
            foreach ($response['images'] as $image) {
                $img = new CoverArtImage($image);
                if ($image['front']) {
                    $this->front = $img;
                }
                if ($image['back']) {
                    $this->back = $img;
                }
                $this->images[] = $img;
            }
        }

        return $this;
    }

    /**
     * Perform a cURL request based on a supplied path
     *
     * @param string $path
     *
     * @return array
     * @throws GuzzleException
     * @throws Exception
     */
    private function call(string $path): array
    {
        $response = $this->client->request('GET', self::URL.$path, array(
            'headers' => array(
                'Accept: application/json'
            )
        ));
        if ($response->getStatusCode() != 200) {
            throw new Exception("Bad response from server");
        }

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Returns the MBID
     *
     * @return string
     */
    public function getMBID(): string
    {
        return $this->mbid;
    }

    /**
     * Returns an array of images
     *
     * @return CoverArtImage[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * Returns the front image
     *
     * @return CoverArtImage
     * @throws Exception
     */
    public function getFrontImage(): CoverArtImage
    {
        if (null == $this->front) {
            throw new Exception('No front image was found');
        }

        return $this->front;
    }

    /**
     * Returns the back image
     *
     * @return CoverArtImage
     * @throws Exception
     */
    public function getBackImage(): CoverArtImage
    {
        if (null == $this->back) {
            throw new Exception('No back image was found');
        }

        return $this->back;
    }
}
