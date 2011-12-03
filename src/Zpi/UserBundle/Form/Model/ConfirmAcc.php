<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zpi\UserBundle\Form\Model;

use FOS\UserBundle\Form\Model\ResetPassword as Base;

class ConfirmAcc extends Base
{
    /**
     * User whose password is changed
     *
     * @var UserInterface
     */
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}
