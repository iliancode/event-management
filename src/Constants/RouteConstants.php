<?php

namespace App\Constants;

class RouteConstants
{
    // -------------- SECURITY --------------
    const ROUTE_LOGIN = 'app.login';
    const ROUTE_LOGOUT = 'app.logout';
    const ROUTE_REGISTER = 'app.register';
    const ROUTE_VERIFY_EMAIL = 'app.verify.email';
    const ROUTE_FORGOT_PASSWORD_REQUEST = 'app.forgot.password.request';
    const ROUTE_CHECK_EMAIL = 'app.check.email';
    const ROUTE_RESET_PASSWORD = 'app.reset.password';

    // -------------- FRONT --------------
    // Home
    const ROUTE_HOME = 'app.home';
    // Profiles
    const ROUTE_PROFILES = 'app.profiles';
    const ROUTE_PROFILES_SHOW = 'app.profiles.show';
    const ROUTE_PROFILES_CREATE = 'app.profiles.create';
    const ROUTE_PROFILES_EDIT = 'app.profiles.edit';
    const ROUTE_PROFILES_DELETE = 'app.profiles.delete';
    // Events
    const ROUTE_EVENTS = 'app.events';
    const ROUTE_EVENTS_SHOW = 'app.events.show';
    const ROUTE_EVENTS_CREATE = 'app.events.create';
    const ROUTE_EVENTS_EDIT = 'app.events.edit';
    const ROUTE_EVENTS_DELETE = 'app.events.delete';
    const ROUTE_EVENTS_JOIN = 'app.events.join';
    const ROUTE_EVENTS_LEAVE = 'app.events.leave';

    // -------------- BACK ---------------
    // Types
    const ROUTE_TYPES = 'app.types';
    const ROUTE_TYPES_SHOW = 'app.types.show';
    const ROUTE_TYPES_CREATE = 'app.types.create';
    const ROUTE_TYPES_EDIT = 'app.types.edit';
    const ROUTE_TYPES_DELETE = 'app.types.delete';
}