<?php

declare(strict_types=1);

namespace App\Common\Auth;

/**
 * Доступные роли пользователя.
 *
 * @package App\Auth
 */
final class UserRoles
{
    /**
     * Пользователь системы.
     */
    public const ROLE_USER = 'ROLE_USER';

    /**
     * Администратор системы.
     */
    public const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * Возвращает список доступных ролей пользователей системы.
     *
     * @return array
     */
    public static function rolesList(): array
    {
        return [
            'ROLE_USER',
            'ROLE_ADMIN',
        ];
    }

    /**
     * Возвращает список доступных ролей пользователей системы c с описанием.
     *
     * @return array
     */
    public static function rolesListWithDesc(): array
    {
        return [
            'ROLE_USER' => 'Пользователь',
            'ROLE_ADMIN' => 'Администратор',
        ];
    }
}
