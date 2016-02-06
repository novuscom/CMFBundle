<?php

namespace Novuscom\Bundle\CMFBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


class SectionCode extends Constraint
{
	public $message = 'Раздел с таким кодом уже существует в данной ветке';
	public function validatedBy()
	{
		return 'section_code_validator';
	}

	public function getTargets()
	{
		return array(self::PROPERTY_CONSTRAINT, self::CLASS_CONSTRAINT);
	}
}