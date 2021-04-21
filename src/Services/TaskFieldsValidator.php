<?php
namespace App\Services;

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TaskFieldsValidator
 * @package App\Services
 */
class TaskFieldsValidator
{
    /**
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    /**
     * @var \Symfony\Component\Validator\Constraints\Collection
     */
    private Collection $constraints;

    /**
     * TaskFieldsValidator constructor.
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
        $this->constraints = new Assert\Collection([
            'caption' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 4, 'max' => 255])
            ],
            'date' => [
                new Assert\DateTime("Y-m-d")
            ],
        ]);
    }

    /**
     * @param array $fields
     * @return array
     */
    public function validate(array $fields): array
    {
        $violations = $this->validator->validate(
            $fields,
            $this->constraints
        );

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getPropertyPath() . ': ' . $violation->getMessage();
        }
        return $errors;
    }
}