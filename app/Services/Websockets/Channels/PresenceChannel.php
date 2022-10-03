<?php

namespace App\Services\Websockets\Channels;

use BeyondCode\LaravelWebSockets\WebSockets\Channels\PresenceChannel as BasePresenceChannel;
use Illuminate\Support\Facades\Redis;
use stdClass;
use Ratchet\ConnectionInterface;
use App\Models\User;
class PresenceChannel extends BasePresenceChannel
{
    public function subscribe(ConnectionInterface $connection, stdClass $payload)
    {

        $channelData = json_decode($payload->channel_data, true);
        // The ID of the user connecting
        $userId = (string) $channelData['user_id'];
        User::where('id', $userId)->update(['online_status' => 'online']);
        parent::subscribe($connection, $payload);
        //Redis::publish('subscribe.' . $this->channelName, json_encode($payload));
    }

    public function unsubscribe(ConnectionInterface $connection)
    {
        if (isset($this->subscribedConnections[$connection->socketId])) {
            User::where('id', auth()->id())->update(['online_status' => 'offline']);
            //Redis::publish('unsubscribe.' . $this->channelName, json_encode([]));
        }
        parent::unsubscribe($connection);
    }

}