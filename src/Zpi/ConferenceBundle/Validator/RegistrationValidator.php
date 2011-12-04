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
                $lastDay = new \DateTime(date('Y-m-d', $registration->getConference()->getBookingendDate()->getTimestamp()));
                // ostatnia możliwa data wyjazdu jest dzień po ostatnim dniu akomodacji przez konferencję
                
                $lastDay->add(new \DateInterval('P1D'));
                
                
		if(empty($endDate) && empty($startDate))
                    return; 
		if($registration->getEndDate() > $lastDay ||
		   $registration->getEndDate() < $registration->getConference()->getBookingstartDate())
		{
			$propertyPath = $context->getPropertyPath() . '.endDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('Leave date should be between conference start and day after last possible accomodation day.',
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
            
		if($startDate > $registration->getConference()->getBookingendDate() ||
		   $startDate < $registration->getConference()->getBookingstartDate())
		{
			$propertyPath = $context->getPropertyPath() . '.startDate';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('Arrival date should be between conference booking start and conference last accomodation date.'
                    . 'arrival: ' . date('d-m-Y', $startDate->getTimestamp()),
								 array(), null);
		}
	}
	
	static public function arePapersValid(Registration $registration,
			ExecutionContext $context)
	{
	    //TODO Musiałem tego walidatora zdeaktywować
	    // (wywalał chyba dlatego, że miałem oprócz scedowanej pracy jedną która była niezaakceptowana
	    //  - nie widoczna w tabelce)
		if($registration->getType() == Registration::TYPE_FULL_PARTICIPATION
		    && count($registration->getPapers()) == 0 && false
		   )
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
    
    /*
     *  Sprawdzenie czy wszystkie zaakceptowane prace przypisane
     *  do danej rejestracji sa odpowiednio oplacane (przynajmniej jedna jako full)
     */
    static public function arePaymentTypesValid(Registration $registration,
            ExecutionContext $context)
    {
        $papers = $registration->getPapers();
        $acceptedPapers = array();
        $fullExists = false;
        
        foreach($papers as $paper)
        {           
            if($paper->isAccepted())
                $acceptedPapers[] = $paper;
        }
        
        foreach($acceptedPapers as $paper)
        {
            if($paper->getPaymentType() == Paper::PAYMENT_TYPE_FULL)
                $fullExists = true;
        }
        
        // jeżeli rejestracja nie ma paperow... to nie można zwracać tu błędu
        if(count($papers) == 0)
        {
            $fullExists = true;
        }
        
        //TODO Ten walidator jeszcze mnie wkurzał, w przypadku zaznaczenia wszystkich prac jako scedowane wywala,
        //     a nie powinien
        if(!$fullExists)
        {
            $propertyPath = $context->getPropertyPath() . '.papers';
			$context->setPropertyPath($propertyPath);
			$context->addViolation('At least one accepted paper should have full payment type.',
								 array(), null);
        }
    }

}
