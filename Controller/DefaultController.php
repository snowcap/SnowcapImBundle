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

        if(strpos($path,"http/") === 0) {
            if(!$im->cacheExists($format,$path)) {
                $new_path = str_replace("http/",$this->get("kernel")->getRootDir() . '/../web/cache/im/' . $format . '/http/',$path);
                $file = file_get_contents( str_replace('http/','http://',$path));
                @mkdir(dirname($new_path),0755,true);
                file_put_contents($new_path,$file);
                $im->mogrify($format, $new_path);
            }
        } else {
            $im->convert($format, $path);
        }

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
}
