<?php
declare( strict_types = 1 );

namespace App\Controller;

use App\Service\ImageTransformOptions;
use App\Service\SimpleHttpResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends AbstractController
{
    protected const AUTHORIZATION_HEADER = 'Authorization';

    /**
     * @throws GuzzleException
     */
    public function imageAction( string $name, Request $request ) : Response
    {
        // TODO check auth via proper Symfony Security Provider,
        // check the token validity vs the token list in database etc.
        $authHeader = $request->headers->get( self::AUTHORIZATION_HEADER );
        if ( $authHeader !== 'Bearer SOME_TOKEN' ) {
            return new SimpleHttpResponse( Response::HTTP_FORBIDDEN );
        }

        $url = $_ENV['REMOTE_URL'] . $name;
        $client = new Client();
        $res = $client->request( 'GET', $url, [
            'http_errors' => false,
            // 'headers' => [ self::AUTHORIZATION_HEADER, $authHeader ], // pass it to the target server
        ] );

        if ( $res->getStatusCode() === Response::HTTP_NOT_FOUND ) {
            return new SimpleHttpResponse( Response::HTTP_NOT_FOUND );
        }

        if ( $res->getStatusCode() !== Response::HTTP_OK ) {
            return new SimpleHttpResponse( Response::HTTP_BAD_GATEWAY );
        }

        //ImageTransformOptions::fromRequest( $request )
        return new Response( (string) $res->getBody() );
    }
}