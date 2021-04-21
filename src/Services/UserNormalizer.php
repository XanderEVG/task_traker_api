<?php
namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Class UserNormalizer
 * @package App\Services
 */
class UserNormalizer
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var array|\string[][]
     */
    private array $user_normalizer_context = [
        'attributes' => [
            'id',
            'username',
            'roles',
        ]
    ];

    /**
     * UserNormalizer constructor.
     * @param \Symfony\Component\Serializer\SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer) {
        $this->serializer = $serializer;
    }

    /**
     * @param \App\Entity\User $user
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize(User $user): array
    {
        return $this->serializer->normalize(
            $user,
            false,
            $this->user_normalizer_context
        );
    }
}