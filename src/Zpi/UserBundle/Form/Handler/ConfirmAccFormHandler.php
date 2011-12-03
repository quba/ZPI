<?php

namespace Zpi\UserBundle\Form\Handler;

use FOS\UserBundle\Form\Handler\ResettingFormHandler as BaseHandler;
use FOS\UserBundle\Model\UserInterface;
use Zpi\UserBundle\Form\Model\ConfirmAcc;

class ConfirmAccFormHandler extends BaseHandler
{
    public function process(UserInterface $user)
    {
        $this->form->setData(new ConfirmAcc($user));

        if ('POST' == $this->request->getMethod()) {
            $this->form->bindRequest($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($user);

                return true;
            }
        }

        return false;
    }

    protected function onSuccess(UserInterface $user)
    {
        $user->setPlainPassword($this->getNewPassword());
        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $this->userManager->updateUser($user);
    }
}