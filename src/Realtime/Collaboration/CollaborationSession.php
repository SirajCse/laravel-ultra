<?php

namespace LaravelUltra\Realtime\Collaboration;

class CollaborationSession
{
    protected $tableId;
    protected $users;
    protected $config;
    protected $isActive = false;

    public function __construct($tableId, $users, $config)
    {
        $this->tableId = $tableId;
        $this->users = $users;
        $this->config = $config;
    }

    public function start()
    {
        $this->isActive = true;

        // Initialize collaboration session
        $this->broadcastSessionStart();

        return $this;
    }

    public function addUser($userId)
    {
        $this->users[] = $userId;
        $this->broadcastUserJoined($userId);

        return $this;
    }

    public function enableLiveCursors()
    {
        // Enable real-time cursor tracking
        return $this;
    }

    public function enableComments()
    {
        // Enable row/cell commenting
        return $this;
    }

    public function broadcastChange($changeData)
    {
        if (!$this->isActive) return;

        broadcast()->channel("ultra-collab-{$this->tableId}", [
            'type' => 'change',
            'data' => $changeData,
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ]);
    }

    protected function broadcastSessionStart()
    {
        broadcast()->channel("ultra-collab-{$this->tableId}", [
            'type' => 'session_start',
            'users' => $this->users,
            'timestamp' => now(),
        ]);
    }

    protected function broadcastUserJoined($userId)
    {
        broadcast()->channel("ultra-collab-{$this->tableId}", [
            'type' => 'user_joined',
            'user_id' => $userId,
            'timestamp' => now(),
        ]);
    }
}