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
		$users = $this->findAll();
		
		return array_filter($users, function ($el) use ($roles) {
		    $userRoles = $el->getRoles();
		    return array_intersect($roles, $userRoles) == $roles;
		});
	}
}