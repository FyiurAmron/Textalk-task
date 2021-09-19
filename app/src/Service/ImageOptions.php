<?php
declare( strict_types = 1 );

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class ImageOptions
{
    public const MAX_IMAGE_SIZE = 4000 * 4000; // arbitrary ATM

    /**
     * see https://wiki.php.net/rfc/nullable-casting why this fn is still needed
     *
     * @param mixed $i
     * @return int|null
     */
    public static function toNullableInt( mixed $i ) : ?int
    {
        return ( $i === null ) ? null : (int) $i;
    }

    public static function fromRequest( Request $request ) : ImageOptions
    {
        $query = $request->query;
        return new self(
            self::toNullableInt( $query->get( 'w' ) ),
            self::toNullableInt( $query->get( 'h' ) ),
            $query->get( 'ext' ),
        );
    }

    public function __construct(
        public ?int $width,
        public ?int $height,
        public ?string $extension = null,
        public ?string $mimeType = null
    )
    {
        if ( $width === 0 || $height === 0 ) {
            throw new \OutOfRangeException( "non-zero w/h required, got [$width,$height] instead" );
        }

        $pxTotal = $width * $height;
        if ( $pxTotal > self::MAX_IMAGE_SIZE ) {
            throw new \OutOfRangeException(
                "resulting image size [$width,$height]=$pxTotal > MAX_IMAGE_SIZE (" . self::MAX_IMAGE_SIZE . ")" );
        }
    }

    public function __toString() : string
    {
        $params = [
            'w' => $this->width,
            'h' => $this->height,
            'ext' => $this->extension,
        ];

        return \http_build_query( $params );
    }


}