<?php	
namespace Zpi\UserBundle\Repository;

use Zpi\UserBundle\Entity\User;

use Doctrine\ORM\EntityRepository;

	
/**
 * Repozytorium dla encji User.
 *
 * @author lyzkov
 */
class UserRepository extends EntityRepository
{
	/**
	 * Wyszukuje i zwraca wszystkich użytkowników z podanymi rolami.
	 * @param unknown_type $role
	 * @return multitype:User 
	 */
	public function findAllByRoles($roles)
	{
		
		//TODO Znaleźć lepszą metodę sprawdzania ról.
		$users = $this->findAll();
		$result = array();
		foreach ($users as $user)
		{
			foreach ($roles as $role)
			{
				if ($user->hasRole($role))
				{
					$result[] = $user;
				}
			}
		}
		
		return $result;
	}
}