<?php
namespace Zpi\ConferenceBundle\Validator;

use Symfony\Component\Validator\ExecutionContext;
use Zpi\ConferenceBundle\Entity\Conference;

/**
 * Zawiera statyczne metody validujące klasę: Conference
 *
 * @author lyzkov
 */
class ConferenceValidator {
	static public function isEndDateValid(Conference $conference,
			ExecutionContext $context) {
		
		if ($conference->getStartDate() >= $conference->getEndDate()) {
			$propertyPath = $context->getPropertyPath() . '.endDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.endDate', array(), null);
		}
		
	}
	static public function isDeadlineValid(Conference $conference,
			ExecutionContext $context) {
		
		if ($conference->getDeadline() > $conference->getStartDate()) {
			$propertyPath = $context->getPropertyPath() . '.deadline';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.deadline', array(), null);
		}
		
	}
}