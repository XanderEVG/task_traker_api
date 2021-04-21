<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Services\TaskFieldsValidator;
use App\Services\TaskNormalizer;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * Class TaskController
 * @package App\Controller
 */
class TaskController extends AbstractController
{
    /**
     * @var TaskRepository
     */
    private TaskRepository $task_repository;

    /**
     * @var UserRepository
     */
    private UserRepository $user_repository;

    /**
     * @var TaskNormalizer
     */
    private TaskNormalizer $normalizer;

    /**
     * @var TaskFieldsValidator
     */
    private TaskFieldsValidator $validator;

    /**
     * TaskController constructor.
     * @param TaskRepository $task_repository
     * @param UserRepository $user_repository
     * @param \App\Services\TaskNormalizer $normalizer
     * @param \App\Services\TaskFieldsValidator $validator
     */
    public function __construct(
        TaskRepository $task_repository,
        UserRepository $user_repository,
        TaskNormalizer $normalizer,
        TaskFieldsValidator $validator
    ) {
        $this->task_repository = $task_repository;
        $this->user_repository = $user_repository;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    /**
     * Получение списка задач
     * @Route("/api/tasks", name="getTasks", methods={"GET"})
     */
    public function getTasks(): Response
    {
        $tasks = $this->task_repository->findAll();
        $responseData = [];
        foreach ($tasks as $task) {
            try {
                $responseData[] = $this->normalizer->normalize($task);
            } catch (ExceptionInterface $e) {
                return $this->json([
                    'errors' => [$e->getMessage()],
                ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->json([
            'data' => $responseData,
        ]);
    }

    /**
     * Получение конкретной задачи
     * @Route("/api/tasks/{id}", name="getOneTask", methods={"GET"})
     */
    public function getOneTask(int $id): Response
    {
        $task = $this->task_repository->find($id);
        if (!$task) {
            return $this->json([
                'errors' => ["Задача не найдена"],
            ])->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        try {
            $responseData[] = $this->normalizer->normalize($task);
        } catch (ExceptionInterface $e) {
            return $this->json([
                'errors' => [$e->getMessage()],
            ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'data' => $responseData,
        ]);
    }

    /**
     * Создание задачи
     * @Route("/api/tasks", name="createTask", methods={"POST"})
     */
    public function createTask(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $caption = filter_var($data['caption'] ?? null, FILTER_SANITIZE_STRING);
        $description = filter_var($data['description'] ?? null, FILTER_SANITIZE_STRING);
        $date = filter_var($data['date'] ?? null, FILTER_SANITIZE_STRING);
        $performer_id = filter_var($data['performer'] ?? null, FILTER_SANITIZE_NUMBER_INT);

        if ($performer_id) {
            $performer_id = intval($performer_id);
            $user = $this->user_repository->find($performer_id);
            if (!$user) {
                return $this->json(['errors' => ["Исполнитель не найден"]],Response::HTTP_BAD_REQUEST);
            }
        } else {
            $user = null;
        }

        $errors = $this->validator->validate(
            [
                'caption' => $caption,
                'date' => $date,
            ]
        );

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors],Response::HTTP_BAD_REQUEST);
        }

        if ($date) {
            try {
                $date = new DateTime($date);
            } catch (Exception $e) {
                return $this->json(['errors' => ["Неверный формат даты"]],Response::HTTP_BAD_REQUEST);
            }
        }

        $manager = $this->getDoctrine()->getManager();
        $task = new Task();
        $task->setCaption($caption);
        $task->setDescription($description);
        $task->setDate($date);
        $task->setPerformer($user);
        $manager->persist($task);
        $manager->flush();

        try {
            $responseData[] = $this->normalizer->normalize($task);
        } catch (ExceptionInterface $e) {
            return $this->json([
                'errors' => [$e->getMessage()],
            ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'data' => $responseData,
        ])->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Редактирование задачи
     * @Route("/api/tasks/{id}", name="updateTask", methods={"PUT"})
     */
    public function updateTask(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);
        $caption = filter_var($data['caption'] ?? null, FILTER_SANITIZE_STRING);
        $description = filter_var($data['description'] ?? null, FILTER_SANITIZE_STRING);
        $date = filter_var($data['date'] ?? null, FILTER_SANITIZE_STRING);
        $performer_id = filter_var($data['performer'] ?? null, FILTER_SANITIZE_NUMBER_INT);

        if ($performer_id) {
            $performer_id = intval($performer_id);
            $user = $this->user_repository->find($performer_id);
            if (!$user) {
                return $this->json(['errors' => ["Исполнитель не найден"]],Response::HTTP_BAD_REQUEST);
            }
        } else {
            $user = null;
        }

        $errors = $this->validator->validate(
            [
                'caption' => $caption,
                'date' => $date,
            ]
        );

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors],Response::HTTP_BAD_REQUEST);
        }

        if ($date) {
            try {
                $date = new DateTime($date);
            } catch (Exception $e) {
                return $this->json(['errors' => ["Неверный формат даты"]],Response::HTTP_BAD_REQUEST);
            }
        }

        $manager = $this->getDoctrine()->getManager();
        $task = $this->task_repository->find($id);
        if (!$task) {
            return $this->json([
                'errors' => ["Задача не найдена"],
            ])->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $task->setCaption($caption);
        $task->setDescription($description);
        $task->setDate($date);
        $task->setPerformer($user);
        $manager->persist($task);
        $manager->flush();

        try {
            $responseData[] = $this->normalizer->normalize($task);
        } catch (ExceptionInterface $e) {
            return $this->json([
                'errors' => [$e->getMessage()],
            ])->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'data' => $responseData,
        ]);
    }

    /**
     * Удаление задачи
     * @Route("/api/tasks/{id}", name="deleteTask", methods={"DELETE"})
     */
    public function deleteTask(int $id): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $task = $this->task_repository->find($id);
        if (!$task) {
            return $this->json([
                'errors' => ["Задача не найдена"],
            ])->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $manager->remove($task);
        $manager->flush();

        return $this->json([])->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
