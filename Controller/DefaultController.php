<?php

namespace Snowcap\ImBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * Main action: renders the image cache and returns it to the browser
     */
    public function indexAction($format,$path)
    {
        /** @var $im \Snowcap\ImBundle\Manager */
        $im = $this->get("snowcap_im.manager");

        $im->convert($format, $path, array("resize" => "125x"));

        if(!$im->cacheExists($format,$path)) {
            throw new \Exception(sprintf("Caching of image failed for %s in %s format", $path, $format));
        } else {
            $extension = pathinfo($path, PATHINFO_EXTENSION);;
            $contentType = $this->getRequest()->getMimeType($extension);
            if (empty($contentType)) {
                $contentType = 'image/'.$extension;
            }
            return new Response($im->getCacheContent($format,$path), 200, array('Content-Type' => $contentType));
        }
    }

    /**
     * @Template()
     */
    public function testAction()
    {
        return array();
    }
}
