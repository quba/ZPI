<?php

namespace Zpi\ConferenceBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RegistrationRepository extends EntityRepository
{
	public function find($id) // fajnie gecaj ze napisales funkcję, która chyba już domyślnie istnieje // @quba
	{
		return $this->getEntityManager()->getRepository('ZpiConferenceBundle:Conference')->find($id);
	}
	
}
