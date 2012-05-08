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

        if(strpos($path,"http/") === 0 || strpos($path,"https/") === 0) {
            $protocol = substr($path,0,strpos($path,"/"));
            if(!$im->cacheExists($format,$path)) {
                $new_path = str_replace($protocol . "/",$this->get("kernel")->getRootDir() . '/../web/cache/im/' . $format . '/' . $protocol . '/',$path);

                @mkdir(dirname($new_path),0755,true);

                $fp = fopen($new_path, 'w');

                $ch = curl_init(str_replace($protocol . '/', $protocol . '://',$path));
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);

                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

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
