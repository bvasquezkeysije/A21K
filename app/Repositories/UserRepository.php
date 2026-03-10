<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository
{
    public function queryAll(): Builder
    {
        return User::query()->with(['roles:id,name', 'person:id,first_name,last_name'])->latest();
    }
}
