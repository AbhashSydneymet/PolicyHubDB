<?php
// auth/RoleManager.php
class RoleManager {
    private static $permissions = [
        'admin' => [
            'policy.view',
            'policy.create',
            'policy.edit',
            'policy.delete',
            'policy.approve'
        ],
        'editor' => [
            'policy.view',
            'policy.create',
            'policy.edit'
        ],
        'viewer' => [
            'policy.view'
        ]
    ];

    public static function hasPermission($permission) {
        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['roles'])) {
            return false;
        }

        foreach ($_SESSION['user']['roles'] as $role) {
            if (isset(self::$permissions[$role]) && in_array($permission, self::$permissions[$role])) {
                return true;
            }
        }

        return false;
    }

    public static function requirePermission($permission) {
        if (!self::hasPermission($permission)) {
            header('Location: index.php?error=unauthorized');
            exit();
        }
    }
}
?>
