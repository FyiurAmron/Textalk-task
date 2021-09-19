<?php
declare( strict_types = 1 );

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;

class SimpleHttpResponse extends Response
{
    /**
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     */
    public function __construct( int $status = 200 )
    {
        parent::__construct( status : $status );
        $this->setContent( $status . ' ' . $this->statusText );
    }
}