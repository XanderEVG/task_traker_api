<?php

namespace App\Controller;

use App\Common\Auth\UserRoles;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use App\Services\UserFieldsValidator;
use App\Services\UserNormalizer;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private UserRepository $user_repository;

    /**
     * @var UserFieldsValidator
     */
    protected UserFieldsValidator $validator;

    /**
     * @var UserNormalizer
     */
    protected UserNormalizer $normalizer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private UserPasswordEncoderInterface $encoder;


    /**
     * UserController constructor.
     * @param \App\Services\UserNormalizer $normalizer
     * @param UserFieldsValidator $validator
     * @param UserRepository $repository
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        UserNormalizer $normalizer,
        UserFieldsValidator $validator,
        UserRepository $repository,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->normalizer = $normalizer;
        $this->validator = $validator;
        $this->user_repository = $repository;
        $this->encoder = $encoder;
    }

    /**
     * Получение списка пользователей
     * @Route("/api/users", name="getUsers", methods={"GET"})
     */
    public function getUsers(): Response
    {
        $users = $this->user_repository->findAll();
        $responseData = [];
        foreach ($users as $user) {
            try {
                $responseData[] = $this->normalizer->normalize($user);
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
     * Получение конкретного пользователя
     * @Route("/api/users/{id}", name="getOneUser", methods={"GET"})
     */
    public function getOneUser(int $id): Response
    {
        $user = $this->user_repository->find($id);
        if (!$user) {
            return $this->json([
                'errors' => ["Пользователь не найден"],
            ])->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        try {
            $responseData[] = $this->normalizer->normalize($user);
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
     * Создание пользователя
     * @Route("/api/users", name="createUser", methods={"POST"})
     */
    public function createUser(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $username = filter_var($data['username'] ?? null, FILTER_SANITIZE_STRING);
        $password = filter_var($data['username'] ?? null, FILTER_SANITIZE_STRING);
        $roles = filter_var_array($data['roles'] ?? [], FILTER_SANITIZE_STRING);

        $errors = $this->validator->validate(
            [
                'username' => $username,
                'password' => $password,
                'roles' => $roles,
            ]
        );

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors],Response::HTTP_BAD_REQUEST);
        }

        if (count(array_diff($roles, UserRoles::rolesList())) > 0) {
            return $this->json(['errors' => ["Не корректные роли пользователя"]],Response::HTTP_BAD_REQUEST);
        }

        $manager = $this->getDoctrine()->getManager();
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $user->setRoles($roles);
        try {
            $manager->persist($user);
            $manager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['errors' => ["Имя пользователя занято"]],Response::HTTP_BAD_REQUEST);
        }

        try {
            $responseData[] = $this->normalizer->normalize($user);
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
     * Редактирование пользователя
     * @Route("/api/users/{id}", name="updateUser", methods={"PUT"})
     */
    public function updateUser(Request $request, int $id): Response
    {
        $data = json_decode($request->getContent(), true);
        $username = filter_var($data['username'] ?? null, FILTER_SANITIZE_STRING);
        $password = filter_var($data['username'] ?? null, FILTER_SANITIZE_STRING);
        $roles = filter_var_array($data['roles'] ?? [], FILTER_SANITIZE_STRING);

        $errors = $this->validator->validate(
            [
                'username' => $username,
                'password' => $password,
                'roles' => $roles,
            ]
        );

        if (count($errors) > 0) {
            return $this->json(['errors' => $errors],Response::HTTP_BAD_REQUEST);
        }

        if (count(array_diff($roles, UserRoles::rolesList())) > 0) {
            return $this->json(['errors' => ["Не корректные роли пользователя"]],Response::HTTP_BAD_REQUEST);
        }

        $manager = $this->getDoctrine()->getManager();
        $user = $this->user_repository->find($id);
        if (!$user) {
            return $this->json([
                'errors' => ["Пользователь не найден"],
            ])->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $user->setUsername($username);
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $user->setRoles($roles);
        try {
            $manager->persist($user);
            $manager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['errors' => ["Имя пользователя занято"]],Response::HTTP_BAD_REQUEST);
        }

        try {
            $responseData[] = $this->normalizer->normalize($user);
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
     * Удаление пользователя
     * @Route("/api/users/{id}", name="deleteUser", methods={"DELETE"})
     */
    public function deleteUser(int $id): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $this->user_repository->find($id);
        if (!$user) {
            return $this->json([
                'errors' => ["Пользователь не найден"],
            ])->setStatusCode(Response::HTTP_NOT_FOUND);
        }

        $manager->remove($user);
        $manager->flush();

        return $this->json([])->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}
