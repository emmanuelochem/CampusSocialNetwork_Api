<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ping', function(){
    return ['pong'=>true];
});

//Authentication
Route::controller(AuthController::class)
->prefix('auth')
->group(function() {
    Route::post('/verify-phone', 'sendPhoneOtp');
    Route::post('/verify-phone-otp', 'verifyPhoneOtp');
    Route::post('/register', 'register');
    Route::post('/login', 'sendLoginOtp');
    Route::post('/verify-login', 'verifyLoginOtp');
});


//Unguarded
Route::controller(AuthController::class)
    ->prefix('unprotected')
    ->group(function() {
        Route::get('/faculties', 'getFaculties');
        Route::get('/departments', 'getDepartments');
        Route::get('department/{id}/levels', 'getLevels');
    });


//User Route
Route::controller(UserController::class)
    ->middleware('auth:api')
    ->prefix('users')
    ->group(function () {
        Route::get('/ping','serverPing');
        Route::get('/search', 'search')->name('search');
        Route::get('/random', 'getRandom')->name('get.random');
    });


//Posts
Route::controller(PostController::class)
    ->middleware('auth:api')
    ->prefix('posts')
    ->group(function() {
        Route::post('create', 'storePost');
        Route::get('feed', 'indexPost');


        Route::prefix('explore')
            ->group(function() {
                Route::get('/', 'exploreIndex');
                Route::get('{post}', 'exploreTray');
            });

        Route::prefix('{post}')
            ->group(function() {
                Route::post('like', 'likePost')->can('like', 'post')->name('like');
                Route::delete('dislike', 'dislikePost')->can('dislike', 'post')->name('dislike');
                Route::post('bookmark', 'bookmarkPost')->can('bookmark', 'post')->name('bookmark');
                Route::delete('unbookmark', 'unbookmarkPost')->can('unbookmark', 'post')->name('unbookmark');

                Route::prefix('comments')
                    ->group(function() {
                        Route::get('/', 'allComment');
                        Route::post('create', 'createComment');
                        Route::prefix('{comment}')
                            ->group(function() {
                                Route::post('like', 'likeComment')->can('like', 'comment')->name('like');
                                Route::delete('unlike', 'unlikeComment')->can('unlike', 'comment')->name('unlike');
                                Route::post('update', 'updateComment')->can('update', 'comment')->name('update');
                                Route::delete('delete', 'deleteComment')->can('delete', 'comment')->name('delete');
                            });
                    });
            });
    });


//Profile
Route::controller(ProfileController::class)
    ->middleware('auth:api')
    ->prefix('profile')
    ->middleware('auth:api')
    ->group(function() {
        Route::get('likes/posts', 'getLikedPosts')->name('likes.posts');
        Route::get('likes/comments', 'getLikedComments')->name('likes.comments');
        Route::get('bookmarks', 'getBookmarks')->name('bookmarks');

        Route::post('upload/profile-photo', 'uploadProfilePhoto')->name('upload.profile-photo');
        Route::put('update', 'update')->name('update');

        Route::prefix('{user}')
            ->name('get.')
            ->group(function() {
            Route::get('/', 'getInfo')->name('info');
            Route::post('follow', 'follow')->can('follow', 'user')->name('follow');
            Route::delete('unfollow', 'unfollow')->can('unfollow', 'user')->name('unfollow');
            Route::post('crush', 'crush')->can('crush', 'user')->name('crush');
            Route::delete('uncrush', 'uncrush')->can('uncrush', 'user')->name('uncrush');
            Route::get('posts', 'getPosts')->name('posts');
            Route::get('comments', 'getComments')->name('comments');
            Route::get('followers', 'getFollowers')->name('followers');
            Route::get('following', 'getFollowedUsers')->name('following');
            Route::get('crushers', 'getFollowers')->name('crushers');
            Route::get('crushing', 'getFollowedUsers')->name('crushing');
        });
    });


    Route::middleware('auth:api')->group(function(){
        Broadcast::routes();
    });


    //Profile
Route::controller(ChatController::class)
->middleware('auth:api')
->prefix('chats')
->middleware('auth:api')
->group(function() {
    Route::get('/', 'chats');
    Route::post('find', 'findUsersById');
    Route::post('user/find', 'findChatUser');
    Route::prefix('{chat}')
        ->group(function() {
            Route::get('/', 'messages');
            Route::post('send', 'sendMessage');
            Route::prefix('messages')
                ->group(function() {
                    Route::prefix('{message}')
                        ->group(function() {
                            Route::delete('delete', 'deleteMessage');
//                    Route::post('like', 'messages')->name('messages');
//                    Route::post('unlike', 'messages')->name('messages');
                        });
                });
    });
});

//  User::with([
//    'permissions' => function ($query) {
//      $query->select('permission_tag:tag_set_id', 'permission_name');
//      $query->orderBy('permission_name');
//    },
//  ]);


//Route::controller(NotificationController::class)
//    ->prefix('notifications')
//    ->name('notifications.')
//    ->group(function() {
//        Route::get('/', 'index')->name('index');
//        Route::get('/count', 'getCount')->name('count');
//        Route::put('/peek', 'peek')->name('peek');
//        Route::put('/{notification}/read', 'read')->name('read');
//        Route::put('/read/all', 'readAll')->name('read.all');
//    });
//
//Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
