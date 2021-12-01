<?php declare(strict_types=1);
/**
 * Created 2021-11-27
 * Author Dmitry Kushneriov
 */

namespace App\Api;

class Routes
{
    public const LOGIN = 'api_auth_login';

    public const USERS_COLLECTION = 'api_users_get_collection';
    public const USER_ITEM = 'api_users_get_item';
    public const CRETE_USER = 'api_users_post_collection';
    public const UPDATE_USER = 'api_users_put_item';
    public const DELETE_USER = 'api_users_delete_item';
}