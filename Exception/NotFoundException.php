<?php
namespace Snowcap\ImBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * NotFoundException
 */
class NotFoundException extends NotFoundHttpException implements ExceptionInterface
{
}
