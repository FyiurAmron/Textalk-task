<?php
declare( strict_types = 1 );

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends AbstractController
{
    public static function indexAction() : Response
    {
        // NOTE: in a regular app, Twig would be used instead;
        // providing simple HTML is better here due to performance reasons
        $title = $_ENV['APP_NAME'] . ' ' . $_ENV['APP_VERSION'];
        $dateStr = \date( DATE_ATOM );
        $html = <<<HTML
<!DOCTYPE html>
<title>$title</title>
$title<br />
$dateStr
</html>
HTML;
    return new Response( $html );
}
}