<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{



    /**
     * Search user(s) by name or username.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function explore(Request $request)
    {
        $data = [];
        if ($request->isPresent('query')) {
            $value = $request->query('query');
            $user = $request->user();
            $data = DB::table('posts')
                ->where('audience','==','everyone')
                ->limit(100)->select('id','nickname','photo','phone')->get();
        }

        return response()->json([
            'success' => true,
            'message'=> 'Users retrieved',
            'data' => $data
        ]);
    }




    public function serverPing(Request $request) {
        $user = $request->user();
        $data = $this->userData($user);
        return response(['status'=> 'connected', 'message'=> 'User connected'], 200);
    }
    public function userAppData(Request $request) {
        $user = $request->user();
        $data = $this->userData($user);
        return response(['status'=> 'success', 'message'=> 'User personalized data retrieved', 'data' => $data], 200);
    }

    function userData(User $user) {
        //$user = User::where('id', $userId)->first();
        $data['profile'] = $user;
        return $data;
    }



    /**
     * Search user(s) by name or username.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $data = [];
        if ($request->isPresent('query')) {
            $value = $request->query('query');
            $user = $request->user();
            $data = DB::table('users')
                ->where('id','!=',$user->id)
                ->where(function ($query) use($value){
                $query->where('nickname','like', "%$value%")
                    ->orWhere('phone', 'like', "%$value%");
            })->limit(100)->select('id','nickname','photo','phone')->get();
        }

        return response()->json([
            'success' => true,
            'message'=> 'Users retrieved',
            'data' => $data
        ]);
    }



    /**
     * Get 3 randomly suggested users that the user is not yet following.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRandom(Request $request)
    {
        $data = cache()->remember('user-suggestions', 60, function() use ($request) {
            $exceptIds = $request->user()->following()->pluck('id')->merge(auth()->id())->toArray();
            return User::whereNotIn('id', [$exceptIds])
                ->inRandomOrder()
                ->limit(3)
                ->get();
        });

        return response()->json(compact('data'));
    }



    /**
     * Get paginated list of user models.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $id = auth()->id();

        $data = User::when(!$request->isPresent('query'), function ($query) use ($id) {
            return (
            $query->whereKeyNot($id)->whereDoesntHave('followers', function ($q) use ($id) {
                return $q->whereKey($id);
            })
            );
        })
            ->when($request->isPresent('query'), function ($q) use ($request) {
                return $q->searchUser($request->query('query'));
            })
            ->when($request->isPresent('month'), function ($q) use ($request) {
                return $q->whereMonth('birth_date', $request->query('month'));
            })
            ->when($request->isPresent('year'), function ($q) use ($request) {
                return $q->whereYear('birth_date', $request->query('year'));
            })
            ->when($request->isPresent('gender'), function ($q) use ($request) {
                return $q->where('gender', $request->query('gender'));
            })
            ->orderBy('name')
            ->withPaginated(20, config('response.user'));

        return response()->json($data);
    }



}
