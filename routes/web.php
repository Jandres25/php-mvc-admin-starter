<?php

/**
 * Application Routes
 *
 * Format: ['method', 'path', 'controller', 'middleware']
 * Middleware: 'auth', 'guest', 'perm:NAME'
 */

return [
    // Auth routes (guest-only)
    ['method' => 'GET',  'path' => '/login',           'controller' => 'Auth\Auth@showLoginForm',         'middleware' => ['guest']],
    ['method' => 'POST', 'path' => '/login',           'controller' => 'Auth\Auth@login',                 'middleware' => ['guest']],
    ['method' => 'GET',  'path' => '/forgot-password', 'controller' => 'Auth\Auth@showForgotPasswordForm', 'middleware' => ['guest']],
    ['method' => 'POST', 'path' => '/forgot-password', 'controller' => 'Auth\Auth@requestPasswordReset',  'middleware' => ['guest']],
    ['method' => 'GET',  'path' => '/reset-password',  'controller' => 'Auth\Auth@showResetPasswordForm',  'middleware' => []],
    ['method' => 'POST', 'path' => '/reset-password',  'controller' => 'Auth\Auth@resetPassword',          'middleware' => []],
    ['method' => 'GET',  'path' => '/logout',          'controller' => 'Auth\Auth@logout',                 'middleware' => []],

    // Dashboard
    ['method' => 'GET', 'path' => '/',          'controller' => 'Dashboard\Dashboard@index', 'middleware' => ['auth']],
    ['method' => 'GET', 'path' => '/dashboard', 'controller' => 'Dashboard\Dashboard@index', 'middleware' => ['auth']],

    // Users
    ['method' => 'GET',  'path' => '/users',              'controller' => 'Users\User@index',             'middleware' => ['auth', 'perm:users']],
    ['method' => 'GET',  'path' => '/users/create',       'controller' => 'Users\User@create',            'middleware' => ['auth', 'perm:users']],
    ['method' => 'POST', 'path' => '/users',              'controller' => 'Users\User@store',             'middleware' => ['auth', 'perm:users']],
    ['method' => 'GET',  'path' => '/users/(\d+)',        'controller' => 'Users\User@show',              'middleware' => ['auth', 'perm:users']],
    ['method' => 'GET',  'path' => '/users/(\d+)/edit',  'controller' => 'Users\User@edit',              'middleware' => ['auth', 'perm:users']],
    ['method' => 'POST', 'path' => '/users/update',      'controller' => 'Users\User@updateAction',      'middleware' => ['auth', 'perm:users']],
    ['method' => 'GET',  'path' => '/profile',           'controller' => 'Users\User@profile',           'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/profile',           'controller' => 'Users\User@processUpdateProfile', 'middleware' => ['auth']],

    // Users AJAX
    ['method' => 'POST', 'path' => '/users/check-email',    'controller' => 'Users\User@checkEmail',        'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/users/check-document', 'controller' => 'Users\User@checkDocument',     'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/users/toggle-status',  'controller' => 'Users\User@toggleStatusAjax',  'middleware' => ['auth', 'perm:users']],
    ['method' => 'POST', 'path' => '/users/change-password','controller' => 'Users\User@ajaxChangePassword', 'middleware' => ['auth']],

    // Permissions
    ['method' => 'GET', 'path' => '/permissions',       'controller' => 'Permissions\Permission@pageIndex', 'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'GET', 'path' => '/permissions/(\d+)', 'controller' => 'Permissions\Permission@detail',    'middleware' => ['auth', 'perm:permissions']],

    // Permissions AJAX
    ['method' => 'POST', 'path' => '/permissions/create',           'controller' => 'Permissions\Permission@create',          'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/update',           'controller' => 'Permissions\Permission@update',          'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/toggle-status',    'controller' => 'Permissions\Permission@toggleStatus',    'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/assign-user',      'controller' => 'Permissions\Permission@assignUser',      'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'POST', 'path' => '/permissions/revoke-user',      'controller' => 'Permissions\Permission@revokeUser',      'middleware' => ['auth', 'perm:permissions']],
    ['method' => 'GET',  'path' => '/permissions/get-users-without','controller' => 'Permissions\Permission@getUsersWithout', 'middleware' => ['auth', 'perm:permissions']],

];
