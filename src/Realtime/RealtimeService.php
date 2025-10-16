<?php

namespace LaravelUltra\Realtime;

use Illuminate\Support\Facades\Broadcast;

class RealtimeService
{
    protected $config;
    protected $enabled;

    public function __construct($config)
    {
        $this->config = $config;
        $this->enabled = $config['enabled'] ?? false;
    }

    public function broadcastTableUpdate($tableId, $data, $action = 'update')
    {
        if (!$this->enabled) return;

        Broadcast::channel("ultra-table-{$tableId}", function () use ($data, $action) {
            return [
                'action' => $action,
                'data' => $data,
                'timestamp' => now(),
            ];
        });
    }

    public function enableCollaboration($tableId, $users = [])
    {
        if (!$this->enabled) return;

        return new CollaborationSession($tableId, $users, $this->config);
    }

    public function getPresenceChannel($channelName)
    {
        return "ultra-presence-{$channelName}";
    }

    public function notifyUsers($userIds, $message, $data = [])
    {
        if (!$this->enabled) return;

        foreach ($userIds as $userId) {
            Broadcast::channel("ultra-user-{$userId}", [
                'message' => $message,
                'data' => $data,
                'timestamp' => now(),
            ]);
        }
    }
}