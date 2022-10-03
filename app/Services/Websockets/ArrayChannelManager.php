<?php
 
namespace App\Services\Websockets;

use App\Services\Websockets\Channels\Channel;
use App\Services\Websockets\Channels\PresenceChannel;
use App\Services\Websockets\Channels\PrivateChannel;
use BeyondCode\LaravelWebSockets\WebSockets\Channels\ChannelManagers\ArrayChannelManager as BaseArrayChannelManager;
use Illuminate\Support\Str;
use App\Models\User;
use Ratchet\ConnectionInterface;
class ArrayChannelManager extends BaseArrayChannelManager
{
    protected function determineChannelClass(string $channelName): string
    {
        if (Str::startsWith($channelName, 'private-')) {
            return PrivateChannel::class;
        }

        if (Str::startsWith($channelName, 'presence-')) {
            return PresenceChannel::class;
        }

        return Channel::class;
    }



    public function removeFromAllChannels(ConnectionInterface $connection)
    {
        User::where('id', 6)->update(['online_status' => 'offline']);
    }

} 