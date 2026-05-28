<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('student.{id}', function ($user, $id) {
    if (auth()->guard('student')->check()) {
        return (int) auth()->guard('student')->user()->id === (int) $id;
    }

    return false;
});
