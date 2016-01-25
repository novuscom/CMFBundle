<?php
namespace Novuscom\Bundle\CMFBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Alias extends Constraint
{
    public $message = 'The string "%string%" contains an illegal character: it can only contain letters or numbers.';
}