<?php
namespace Zpi\ConferenceBundle\Validator;

use Symfony\Component\Validator\ExecutionContext;
use Zpi\ConferenceBundle\Entity\Registration;
use Zpi\PaperBundle\Entity\Paper;

class RegistrationValidator
{
	
	static public function isEndDateValid(Registration $registration,
			ExecutionContext $context)
	{
                $startDate = $registration->getStartDate();
                $endDate = $registration->getEndDate();
                
		if(empty($endDate) && empty($startDate))
                    return;
            
		if($registration->getEndDate() > $registration->getConference()->getBookingendDate() ||
		   $registration->getEndDate() < $registration->getConference()->getBookingstartDate())
		{
			$propertyPath = $context->getPropertyPath() . '.endDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('Leave date should be between conference booking start and booking end date.',
								 array(), null);
		}
		else if($registration->getStartDate() >= $registration->getEndDate())
		{						
			$propertyPath = $context->getPropertyPath() . '.endDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('Leave date should be after arrival date.', array(), null);
		}
		
		
	}
	
	static public function isStartDateValid(Registration $registration,
			ExecutionContext $context)
	{
                $startDate = $registration->getStartDate();
                $endDate = $registration->getEndDate();
                
		if(empty($endDate) && empty($startDate))
                    return;
            
		if($registration->getStartDate() > $registration->getConference()->getBookingendDate() ||
		   $registration->getStartDate() < $registration->getConference()->getBookingstartDate())
		{
			$propertyPath = $context->getPropertyPath() . '.startDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('Arrival date should be between conference booking start and booking end date.',
								 array(), null);
		}
	}
	
	static public function arePapersValid(Registration $registration,
			ExecutionContext $context)
	{
		if($registration->getType() == Registration::TYPE_FULL_PARTICIPATION and 
		   count($registration->getPapers()) == 0)
		{
			$propertyPath = $context->getPropertyPath() . '.papers';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('If you\'ve choosen full participation, you should choose at least one paper.',
								 array(), null);
		}
	}
    
    static public function isTypeValid(Registration $registration,
            ExecutionContext $context)
    {
        if($registration->getType() == Registration::TYPE_LIMITED_PARTICIPATION and 
		   count($registration->getPapers()) != 0)
		{
			$propertyPath = $context->getPropertyPath() . '.type';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('You have to choose a full participation type in order to have papers printed.',
								 array(), null);
		}
    }
    
    static public function arePaymentTypesValid(Registration $registration,
            ExecutionContext $context)
    {
        $papers = $registration->getPapers();
        $fullExists = false;
        
        foreach($papers as $paper)
        {
            if($paper->getPaymentType() == Paper::PAYMENT_TYPE_FULL)
                $fullExists = true;
        }
        if(count($papers) == 0)
        {
            $fullExists = true;
        }
        if(!$fullExists)
        {
            $propertyPath = $context->getPropertyPath() . '.papers';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('At least one paper should have full payment type.',
								 array(), null);
        }
    }

}
