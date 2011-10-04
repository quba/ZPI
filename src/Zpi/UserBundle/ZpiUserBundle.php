<?php

namespace Zpi\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ZpiUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
