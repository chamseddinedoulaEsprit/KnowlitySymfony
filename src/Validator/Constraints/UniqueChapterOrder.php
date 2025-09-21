<?php
// src/Validator/Constraints/UniqueChapterOrder.php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueChapterOrder extends Constraint
{
    public string $message = 'L\'ordre "{{ value }}" est déjà utilisé pour un autre chapitre dans ce cours.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}