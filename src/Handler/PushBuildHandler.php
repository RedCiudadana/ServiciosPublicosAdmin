<?php

namespace App\Handler;

class PushBuildHandler
{
    private $buildNotifications = [];

    public function addBuildNotification($message)
    {
        $this->buildNotifications[] = $message;
    }

    public function getBuildNotifications()
    {
        return $this->buildNotifications;
    }
}
