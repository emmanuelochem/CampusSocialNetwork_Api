<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Events\OnlineStatus;
use App\Models\Chat;
use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Post;
use App\Notifications\NotifyUponAction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(){
    }



    public function getNewMessages(){
        $user = $request->user();
        $offlineMessages = Message::where('receiver_id', $user->id)
        ->where('receipt', '=', 'sent')
        ->with(['chat'])
        ->get();
        return response()->json([
            'status' => 'success',
            'message' => 'users retrieved',
            'data' => $offlineMessages
        ], 200);
    }

    public function findUsersById(Request $request)
    {
        $ids = $request->ids;
        $users = User::whereIn('id', $ids)->get();
        return response()->json([
            'status' => 'success',
            'message' => 'users retrieved',
            'data' => $users
        ], 200);
    }

    public function findChatUser(Request $request)
    {
        $id = $request->id;
        $users = User::where('id', $id)->with(['levels', 'departments'])->get();
        return response()->json([
            'status' => 'success',
            'message' => 'User retrieved',
            'data' => $users
        ], 200);
    } 

    //
    public function chats(Request $request) {
        $user = $request->user();
        $conversations = $user->chats()
            ->with(['users'=> function ($query) use ($user) {
                 return $query->where('id', '!=', $user->id)->with(['levels', 'departments'])->get();
             },
             'messages'=> function ($query) {
                return $query->latest()->first();
            }
            ])
            ->get();
            //$conversations['messages'] = $conversations::with('messages');
        return response()->json([
            'success' => true,
            'message'=> 'Conversations retrieved',
            'data' => $conversations
        ]);
    }

    public function messages(Request $request, Chat $conversation) {
        return response()->json([
            'success' => true,
            'message'=> 'Chat messages retrieved',
            'data' => $conversation->messages
        ]);
    }

    public function sendMessage(Chat $chat, Request $request) {
        $user = $request->user();
        $msg = new Message();
        $msg->message_uuid = $request->message_uuid;
        //$msg->sender_id = $user->id;
        $msg->sender_id = $request->sender_id;
        $msg->receiver_id = $request->receiver_id;
        $msg->content = $request->content;
        $msg->receipt = 'sent';
        $msg->is_deleted = 0;
        $msg->created_at = $request->created_at;
        $msg->updated_at = $request->updated_at;
        if(!$chat->messages()->save($msg)){
            return response()->json(['success' => false, 'message'=> 'Message not sent',]);
        }
//        $user = $request->user();
//        $user->notify(new NotifyUponAction(
//            $user,
//            Notification::COMMENTED_ON_POST,
//            "{$conversation->id}"
//        ));
        if($msg->receiver_id){
            Broadcast(new NewMessage(json_encode($msg), $user))->toOthers();
        }
        return response()->json([
            'success' => true,
            'message'=> 'Message sent',
            'data' => $msg
        ]);
    }


    public function deleteMessage(Conversation $conversation, Message $message) {
        //try{
            $conversation->messages()->delete($message);
        return response()->json([
            'success' => true,
            'message'=> 'Message deleted',
        ]);
//            }
//            catch (ModelNotFoundException $exception) {
//                return response()->json([
//                    'success' => true,
//                    'message'=> $exception->getMessage(),
//                ]);
//            }
    }
}
