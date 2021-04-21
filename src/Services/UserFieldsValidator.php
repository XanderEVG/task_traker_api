<?php
namespace App\Services;


use App\Repository\UserRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserFieldsValidator
 * @package App\Services
 */
class UserFieldsValidator
{
    /**
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    private Collection $constraints;

    /**
     * UserFieldsValidator constructor.
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
        $this->constraints = new Assert\Collection([
            'username' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 180])
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 4])
            ],
            'roles' => [
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
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