<?php
declare( strict_types = 1 );

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class ImageTransformOptions
{
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

    public static function fromRequest( Request $request ) : ImageTransformOptions
    {
        $query = $request->query;
        return new self(
            self::toNullableInt( $query->get( 'w' ) ),
            self::toNullableInt( $query->get( 'h' ) ),
            $query->get( 'ext' ),
        );
    }

    public function __construct(
        protected ?int $width,
        protected ?int $height,
        protected ?string $extension
    )
    {
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