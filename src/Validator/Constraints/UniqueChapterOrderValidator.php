<?php
// src/Validator/Constraints/UniqueChapterOrderValidator.php
namespace App\Validator\Constraints;

use App\Entity\Chapitre;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueChapterOrderValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Chapitre) {
            throw new UnexpectedTypeException($value, Chapitre::class);
        }

        if (!$value->getCours()) {
            return; // Pas de cours associé, on ne valide pas
        }

        $cours = $value->getCours();
        $chapOrder = $value->getChapOrder();

        // Vérifiez si un autre chapitre dans le même cours a le même ordre
        foreach ($cours->getChapitres() as $chapitre) {
            if ($chapitre !== $value && $chapitre->getChapOrder() === $chapOrder) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $chapOrder)
                    ->atPath('chapOrder')
                    ->addViolation();
                return;
            }
        }
    }
}