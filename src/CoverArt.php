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
    public const TYPE_RELEASE = 'release';
    public const TYPE_RELEASE_GROUP = 'release-group';

    private const URL = 'https://coverartarchive.org';

    /**
     * Sets the MBID type
     * @var string
     */
    private $type = 'release';

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
     * @param string $type
     * @param string $mbid
     * @param ClientInterface $client
     * @throws GuzzleException
     * @throws Exception
     */
    public function __construct(string $type, string $mbid, ClientInterface $client)
    {
        $this->client = $client;

        $this
            ->setType($type)
            ->setMBID($mbid)
            ->retrieveImages();
    }

    /**
     * @param string $type
     * @return $this
     * @throws Exception
     */
    private function setType(string $type): self {
        if (false === in_array($type, [self::TYPE_RELEASE, self::TYPE_RELEASE_GROUP], true)) {
            throw new Exception('Invalid type provided. Please provide `CoverArt::TYPE_RELEASE` or `CoverArt::TYPE_RELEASE_GROUP`.');
        }
        $this->type = $type;

        return $this;
    }

    /**
     * Sets the MusicBrainzID
     *
     * @param string $mbid
     * @return CoverArt
     */
    private function setMBID(string $mbid): self
    {
        if (!self::isValidMBID($mbid)) {
            throw new InvalidArgumentException('Invalid Music Brainz ID');
        }

        $this->mbid = $mbid;

        return $this;
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
        $response = $this->call("/{$this->type}/{$this->mbid}");

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
