<?php	
namespace Zpi\ConferenceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

	
/**
 * Formularz dla encji Conference
 *
 * @author lyzkov
 */
class ConferenceType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
			->add('name', 'text',
				array(
								'label'	=>	'conf.form.name'))
			->add('startDate', 'date',
				array(
								'label'	=>	'conf.form.start',
								'years'	=>	range(
			date('Y'),
			date('Y', strtotime('+2 years')))))
			->add('endDate', 'date',
				array(
								'label'	=>	'conf.form.end',
								'years'	=>	range(
			date('Y'),
			date('Y', strtotime('+2 years')))))
			->add('deadline', 'date',
				array(
								'label'	=>	'conf.form.deadline',
								'years'	=>	range(
			date('Y', strtotime('-1 years')),
			date('Y', strtotime('+2 years')))))
			->add('minPageSize', 'integer',
				array(
								'label'	=>	'conf.form.min_page_size'))
			->add('address', 'text',
				array(
								'label'	=>	'conf.form.address'))
			->add('city', 'text',
				array(
								'label'	=>	'conf.form.city'))
			->add('postalCode', 'text',
				array(
								'label'	=>	'conf.form.postal_code'))
			->add('description', 'textarea',
				array(
								'label'	=>	'conf.form.description'));
	}
	public function getName()
	{
		return 'conference';
	}
}
