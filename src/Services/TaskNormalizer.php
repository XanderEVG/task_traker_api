<?php
namespace App\Services;

use App\Entity\Task;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class TaskNormalizer
 * @package App\Services
 */
class TaskNormalizer
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var array|\string[][]
     */
    private array $task_normalizer_context = [
        'attributes' => [
            'id',
            'caption',
            'description',
            'date',
            'performer' => ['id', 'username'],
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
     * @param \App\Entity\Task $user
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize(Task $user): array
    {
        return $this->serializer->normalize(
            $user,
            false,
            $this->task_normalizer_context
        );
    }
}