<?php

function hasPermission(string $permission): bool
{
    if (!isset($_SESSION['user_authenticated']) || !$_SESSION['user_authenticated']) {
        return false;
    }

    if (isset($_SESSION['user_roles']) && in_array('admin', $_SESSION['user_roles'])) {
        return true;
    }

    return in_array($permission, $_SESSION['user_permissions'] ?? []);
}