<?php

namespace App\Constants;

class RouteConstants
{
    // SECURITY
    const ROUTE_LOGIN = 'app.login';
    const ROUTE_LOGOUT = 'app.logout';
    const ROUTE_REGISTER = 'app.register';
    const ROUTE_VERIFY_EMAIL = 'app.verify.email';
    const ROUTE_FORGOT_PASSWORD_REQUEST = 'app.forgot.password.request';
    const ROUTE_CHECK_EMAIL = 'app.check.email';
    const ROUTE_RESET_PASSWORD = 'app.reset.password';

    // FRONT
    const ROUTE_HOME = 'app.home';

    // BACK
    const ROUTE_TYPES = 'app.types';
    const ROUTE_TYPES_SHOW = 'app.types.show';
    const ROUTE_TYPES_CREATE = 'app.types.create';
    const ROUTE_TYPES_EDIT = 'app.types.edit';
    const ROUTE_TYPES_DELETE = 'app.types.delete';
}