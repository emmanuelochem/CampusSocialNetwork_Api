<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    protected $profile;

    //protected $cloudinary;

    /**
     * Create a new notification instance.
     *
     * @param \App\Repositories\ProfileRepository  $profile
     * @return void
     */
    public function __construct(ProfileRepository $profile)
    {
        $this->profile = $profile;
       // $this->cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
    }

    /**
     * Get the user's profile info.
     *
     * @param \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInfo(User $user)
    {

        $data = $user->loadCount(['followers', 'following', 'posts', 'comments', 'crushers']);
        return response()->success('Profile retrived', $data);
    }

    /**
     * Follow a user.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\User  $user
    // * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function follow(Request $request, User $user)
    {
        $this->authorize('follow', $user);

        $follower = $request->user();

        DB::transaction(function() use ($follower, $user) {
            $follower->following()->sync([$user->id]);
            //  $user->notify(new NotifyUponAction($follower, Notification::FOLLOWED, "/{$follower->username}"));
        });


        return response(['status'=> 'success', 'message'=> 'User followed'], 200);
    }

    /**
     * Unfollow a user.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\User  $user
    //* @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unfollow(Request $request, User $user)
    {
        $this->authorize('unfollow', $user);

        $request->user()->following()->detach($user);

        return response(['status'=> 'success', 'message'=> 'User unfollowed'], 200);
    }

    /**
     * Crush on a user.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\User  $user
    // * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function crush(Request $request, User $user)
    {
        $this->authorize('crush', $user);
        $follower = $request->user();
        DB::transaction(function() use ($follower, $user) {
            $follower->crushing()->sync([$user->id]);
            //  $user->notify(new NotifyUponAction($follower, Notification::FOLLOWED, "/{$follower->username}"));
        });
        return response(['status'=> 'success', 'message'=> 'Crush added'], 200);
    }



    /**
     * Uncrush a user.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\User  $user
    //* @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function uncrush(Request $request, User $user)
    {
        $this->authorize('uncrush', $user);

        $request->user()->crushing()->detach($user);

        return response(['status'=> 'success', 'message'=> 'Crush removed'], 200);
    }




    /**
     * Get user's own posts.
     *
     * @param \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPosts(User $user)
    {
        $response = $this->profile->get($user, 'posts');

        return response()->json($response);
    }

    /**
     * Get user's own comments.
     *
     * @param \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComments(User $user)
    {
        $response = $this->profile->get($user, 'comments');

        return response()->json($response);
    }

    /**
     * Get posts liked by user.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLikedPosts(Request $request)
    {
        $response = $this->profile->getPostsOrComments($request, 'likedPosts');

        return response()->json($response);
    }

    /**
     * Get comments liked by user.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLikedComments(Request $request)
    {
        $response = $this->profile->getPostsOrComments($request, 'likedComments');

        return response()->json($response);
    }

    /**
     * Get posts bookmarked by user.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookmarks(Request $request)
    {
        $response = $this->profile->getPostsOrComments($request, 'bookmarks');

        return response()->json($response);
    }

    /**
     * Get user's followers.
     *
     * @param \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFollowers(User $user)
    {
        $response = $this->profile->getUserConnections($user, 'followers');

        return response()->json($response);
    }

    /**
     * Get other users followed by user.
     *
     * @param \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFollowedUsers(User $user)
    {
        $response = $this->profile->getUserConnections($user, 'following');

        return response()->json($response);
    }





//
//    /**
//     * Upload a profile photo.
//     *
//     * @param \App\Http\Requests\UserRequest  $request
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function uploadProfilePhoto(UserRequest $request)
//    {
//        $image = $this->cloudinary->uploadApi()->upload(
//            $request->file('image')->getRealPath(),
//            [
//                'folder' => 'social',
//                'eager' => [
//                    'width' => 200,
//                    'height' => 200,
//                    'crop' => 'fill',
//                    'aspect_ratio' => 1.0,
//                    'radius' => 'max',
//                ]
//            ]
//        );
//
//        return response()->json([
//            'data' => $image['public_id'],
//        ]);
//    }
//
//    /**
//     * Update auth user's profile.
//     *
//     * @param \App\Http\Requests\UserRequest  $request
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function update(UserRequest $request)
//    {
//        if (empty($request->input('image_url')) && !is_null($request->user()->image_url)) {
//            $this->cloudinary->uploadApi()->destroy($request->user()->image_url);
//        }
//
//        $body = $request->only(['name', 'bio', 'image_url']);
//        $birthDate = Carbon::parse($request->input('birth_date'));
//
//        $request->user()->update(array_merge($body, [
//            'birth_date' => $birthDate
//        ]));
//
//        return response()->success();
//    }



//**
// try{
//        if(!Auth::user()->hasPermissionTo('user_access'))
//        {
//            return response()->json([ "message" => 'You are not authorised to view Users'], 401);
//        }
//        return response()->json(User::with('employee','company','department','gradelevel')->get(), 200);
//    } catch (\Exception $e) {
//        Log::error($e);
//        $message = $e->getMessage();
//        return response()->json(['error' => 1, 'message' => $message]);
//        }*//
}
