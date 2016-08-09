<?php

namespace Novuscom\CMFBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AliasValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {

        //print_r($value); exit;
        //foreach ($value as $v) {
            /*
            if (!is_numeric($v)) {
                $this->context->addViolation($constraint->message, array('%string%' => $v));
            }
            */
        //}
        //exit;
    }
}