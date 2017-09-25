<?php

namespace Hypersistence\Auth;

use Hypersistence\Auth\HypersistenceAuthenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;

trait HypersistenceUser
{
    use HypersistenceAuthenticatable, Authorizable, CanResetPassword;
}