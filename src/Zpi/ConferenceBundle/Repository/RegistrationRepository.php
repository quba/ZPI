<?php

namespace Zpi\ConferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RegistrationRepository extends EntityRepository
{
	public function find($id)
	{
		return $this->getEntityManager()->getRepository('ZpiConferenceBundle:Conference')->find($id);
	}
	
}
