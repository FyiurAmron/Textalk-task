<?php
declare( strict_types = 1 );

namespace App\Controller;

use App\Service\Image;
use App\Service\ImageOptions;
use App\Service\SimpleHttpResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends AbstractController
{
    protected const AUTHORIZATION_HEADER = 'Authorization';
    protected const CONTENT_TYPE_HEADER = 'Content-Type';
    protected const X_CACHE_HEADER = 'X-Cache';
    protected const CACHE_HIT = 'HIT';
    protected const CACHE_MISS = 'MISS';

    /**
     * @throws GuzzleException
     * @throws \ImagickException
     */
    public function imageAction( string $name, Request $request ) : Response
    {
        // TODO check auth via proper Symfony Security Provider,
        // check the token validity vs the token list in database etc.
        $authHeader = $request->headers->get( self::AUTHORIZATION_HEADER );
        if ( $authHeader !== 'Bearer SOME_TOKEN' ) {
            return new SimpleHttpResponse( Response::HTTP_FORBIDDEN );
        }

        $imageTransformOptions = ImageOptions::fromRequest( $request );

        $cacheKey = $name . '?' . $imageTransformOptions;
        /*
        * NOTE: a proper cache in this situation should probably be done by HTTP stack
        * (e.g. Varnish cache, httpd rewrites etc.), not a PHP backend
        * (both due to performance and maintenance reasons); if for whatever reason we should do it
        * in PHP, the actual cache would be a PSR-16- and/or PSR-6-compliant class.
        * Still, I've implemented a simple cache with no invalidation as a proof of concept
        * for cases where a full-fledged cache server is not viable.
        */
        $memcached = new \Memcached();
        $memcached->addServer( 'memcached', 11211 );
        /** @var Image $cached */
        $cached = $memcached->get( $cacheKey );
        if ( $memcached->getResultCode() === \Memcached::RES_SUCCESS ) {
            $response = new Response( $cached->blob, headers : [
                self::CONTENT_TYPE_HEADER => $cached->imageOptions->mimeType,
                self::X_CACHE_HEADER => self::CACHE_HIT,
            ] );

            return $response;
        }

        $url = $_ENV['REMOTE_URL'] . $name;
        $client = new Client();
        $res = $client->request( 'GET', $url, [
            'http_errors' => false,
            'headers' => [
                self::AUTHORIZATION_HEADER => $authHeader // pass it to the target server
            ],
        ] );

        if ( $res->getStatusCode() === Response::HTTP_NOT_FOUND ) {
            return new SimpleHttpResponse( Response::HTTP_NOT_FOUND );
        }

        if ( $res->getStatusCode() !== Response::HTTP_OK ) {
            return new SimpleHttpResponse( Response::HTTP_BAD_GATEWAY );
        }

        $image = Image::fromHttpResponse( $res );

        $newImage = $image->createThumbnail( $imageTransformOptions );

        $memcached->set( $cacheKey, $newImage );

        $response = new Response( $newImage->blob, headers : [
            self::CONTENT_TYPE_HEADER => $newImage->imageOptions->mimeType,
            self::X_CACHE_HEADER => self::CACHE_MISS,
        ] );

        // NOTE since the MIME type won't match the actual extension, some weird browser behaviour
        // may happen (e.g. Chrome warnings etc.)

        return $response;
    }
}