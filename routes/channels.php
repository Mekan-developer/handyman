<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
 * Public channel for the admin map view — broadcasts every master location update
 * scoped by city id. Anyone (including unauthenticated guest in dev) can listen.
 * In production, switch to private channel + admin auth gate.
 */
Broadcast::channel('masters-map.{cityId}', function () {
    return true;
});
