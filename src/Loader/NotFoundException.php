<?php

namespace DElfimov\Translate\Loader;

use Psr\Container\NotFoundExceptionInterface;
use DElfimov\Translate\Loader\ContainerException;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
