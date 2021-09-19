<?php
declare( strict_types = 1 );

namespace App\Service;

use GuzzleHttp\Psr7\Response;

class Image
{
    /**
     * @throws \ImagickException
     */
    public function __construct(
        public string $blob,
        public ?ImageOptions $imageOptions = null )
    {
        if ( $imageOptions === null ) {
            $im = new \Imagick();
            $im->readImageBlob( $this->blob );
            $this->imageOptions =
                new ImageOptions(
                    $im->getImageHeight(),
                    $im->getImageWidth(),
                    mimeType : $im->getImageMimeType()
                );
        }
    }

    /**
     * @throws \ImagickException
     */
    public static function fromHttpResponse( Response $response ) : self
    {
        $blob = (string) $response->getBody();
        return new self( $blob ); // NOTE: we ignore the MIME type returned by server, as it may be wrong
    }

    /**
     * @param ImageOptions $imageOptions
     *
     * @return Image
     * @throws \ImagickException
     */
    public function createThumbnail( ImageOptions $imageOptions ) : self
    {
        $im = new \Imagick();
        $im->readImageBlob( $this->blob );
        if ( $imageOptions->width !== null || $imageOptions->height !== null ) {
            $im->thumbnailImage(
                $imageOptions->width ?? $im->getImageWidth(),
                $imageOptions->height ?? $im->getImageHeight()
            );
        }
        // NOTE: it would be quite easy to add an aspect-ratio-locked resize option above FWIW
        if ( $imageOptions->extension !== null ) {
            $im->setImageFormat( $imageOptions->extension );
        }

        return new Image( $im->getImageBlob(), $imageOptions );
    }
}