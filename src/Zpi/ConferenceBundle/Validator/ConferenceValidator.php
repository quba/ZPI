<?php
namespace Zpi\ConferenceBundle\Validator;

use Symfony\Component\Validator\ExecutionContext;
use Zpi\ConferenceBundle\Entity\Conference;

/**
 * Zawiera statyczne metody validujące klasę: Conference
 *
 * @author gecaj
 */
class ConferenceValidator {
	static public function isEndDateValid(Conference $conference,
			ExecutionContext $context) {
		
		if ($conference->getStartDate() >= $conference->getEndDate()) {
			$propertyPath = $context->getPropertyPath() . '.endDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.end_date', array(), null);
		}
		
	}
	
    static public function isAbstractDeadlineValid(Conference $conference,
			ExecutionContext $context) {
		
        if ($conference->getAbstractDeadline() >= $conference->getStartDate())
        {
            $propertyPath = $context->getPropertyPath() . '.abstractDeadline';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.deadline', array(), null);
        }
		else if ($conference->getAbstractDeadline() >= $conference->getPaperDeadline()
            ||
            $conference->getAbstractDeadline() >= $conference->getCorrectedPaperDeadline()
            ||
            $conference->getAbstractDeadline() >= $conference->getConfirmationDeadline()) 
        {
            
			$propertyPath = $context->getPropertyPath() . '.abstractDeadline';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.abstract_deadline', array(), null);
		}
		
	}
    static public function isPaperDeadlineValid(Conference $conference,
			ExecutionContext $context) {
		
        if ($conference->getPaperDeadline() >= $conference->getStartDate())
        {
            $propertyPath = $context->getPropertyPath() . '.paperDeadline';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.deadline', array(), null);
        }
		else if ($conference->getPaperDeadline() >= $conference->getCorrectedPaperDeadline()
            ||
            $conference->getPaperDeadline() >= $conference->getConfirmationDeadline())
        {
            
			$propertyPath = $context->getPropertyPath() . '.paperDeadline';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.paper_deadline', array(), null);
		}		
	}
    static public function isCorrectedPaperDeadlineValid(Conference $conference,
			ExecutionContext $context) {
		
        if ($conference->getCorrectedPaperDeadline() >= $conference->getStartDate())
        {
            $propertyPath = $context->getPropertyPath() . '.correctedPaperDeadline';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.deadline', array(), null);
        }
		else if ($conference->getCorrectedPaperDeadline() >= $conference->getConfirmationDeadline()) 
        {
            
			$propertyPath = $context->getPropertyPath() . '.correctedPaperDeadline';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.corrected_deadline', array(), null);
		}		
	}
    static public function isConfirmationDeadlineValid(Conference $conference,
			ExecutionContext $context) {
		
        if ($conference->getConfirmationDeadline() >= $conference->getStartDate())
        {
            $propertyPath = $context->getPropertyPath() . '.confirmationDeadline';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.deadline', array(), null);
        }
				
	}
    static public function isbookingStartDateValid(Conference $conference,
			ExecutionContext $context) {
		
		if ($conference->getBookingstartDate() > $conference->getStartDate()) {
			$propertyPath = $context->getPropertyPath() . '.bookingstartDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.bookingstart_date', array(), null);
		}
		
	}
    static public function isbookingEndDateValid(Conference $conference,
			ExecutionContext $context) {
		
		if ($conference->getBookingendDate() < $conference->getEndDate()) {
			$propertyPath = $context->getPropertyPath() . '.bookingendDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('conf.violation.bookingend_date', array(), null);
		}
		
	}
}
