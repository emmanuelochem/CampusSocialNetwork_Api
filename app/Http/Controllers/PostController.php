<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Requests\PostRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    /**
     * Get paginated news feed posts.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function indexPost(Request $request)
    {
        $ids = auth()->user()->following()->pluck('following_id')->merge(auth()->id());
        $posts = Post::whereHas('user', function ($query) use ($ids) {
            return $query->whereIn('id', $ids);
        })->latest()->withPaginated(2);
        return response()->json([
            'status' => 'success',
            'message' => 'Posts retrieved',
            'data' => $posts
        ], 200);
    }

    /**
     * Create a new post.
     *
     * @param PostRequest $request
     * @return JsonResponse
     */
    public function storePost(PostRequest $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit','256M');

        //foreach ($request->file('image') as $file) {
            $path =$request->file('image')->store('images', 's3');
            $s3 = Storage::disk('s3');
            $s3->setVisibility($path, 'public');
            $url = $s3->url($path);
//            PostFiles::create([
//             'filename' => basename($path),
//             'user' => 'id',
//            'type' => 'post',
//            'url' => Storage::disk('s3')->url($path)
//            ]);
       // }
        $user = $request->user();
        $post = Post::create([
            'image'=>  $url,
            'caption' => $request->caption,
            'audience' => $request->audience,
            'allowcomment' => $request->allowcomment,
            'user_id' => $user['id'],
            'is_deleted'=> false
        ]);

       // if post creation successful, notify friends, or entire school;
        $data = [
            'status'=> 'success',
            'message'=> 'Post published',
            'data'=>$post,
        ];
        return response()->json($data, 200);
    }


    /**
     * Get a specific post.
     *
     * @param Post $post
     * @return JsonResponse
     */
    public function showPost(Post $post)
    {
        $data = [
            'status'=> 'success',
            'message'=> 'Post retrieved',
            'data'=>$post,
        ];
        return response()->json($data, 200);
    }


    /**
     * Update an existing post.
     *
     * @param PostRequest $request
     * @param Post $post
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function updatePost(PostRequest $request, Post $post)
    {
        $this->authorize('update', $post);

        if ($post->caption === $request->input('caption')) {
            return response()->error('No changes made.', 401);
        }

        $post->update($request->only('caption'));
        //$updated = $post->fill($request->all())->save();

        return response()->success();
    }


    /**
     * Delete an existing post.
     *
     * @param Post $post
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroyPost(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->success('Post deleted');
    }


    /**
     * Add the post to the list of likes.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function likePost(Request $request, Post $post)
    {
        $this->authorize('like', $post);

        $liker = $request->user();

        DB::transaction(function() use ($liker, $post) {
            $liker->likedPosts()->attach($post);

            if ($post->user->isNot(auth()->user())) {
                //$post->user->notify(new NotifyUponAction($liker, Notification::LIKED_POST, "/post/{$post->slug}"));
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Post liked',
            'data' => $post->likers()->count(),
        ]);
    }


    /**
     * Remove the post from the list of likes.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function dislikePost(Request $request, Post $post)
    {
        $this->authorize('dislike', $post);

        $request->user()->likedPosts()->detach($post);

        return response()->json([
            'status' => 'success',
            'message' => 'Post unliked',
            'data' => $post->likers()->count(),
        ]);
    }


    /**
     * Add the post to the list of bookmarks.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function bookmarkPost(Request $request, Post $post)
    {
        $this->authorize('bookmark', $post);

        $request->user()->bookmarks()->attach($post);

        return response()->json([
            'status' => 'success',
            'message' => 'Post bookmarked',
        ]);
    }


    /**
     * Remove the post from the list of bookmarks.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unbookmarkPost(Request $request, Post $post)
    {
        $this->authorize('unbookmark', $post);

        $request->user()->bookmarks()->detach($post);

        return response()->json([
            'status' => 'success',
            'message' => 'Post unbookmarked',
        ]);
    }

    /**
     * Get all post comment order by likes count
     */
    public function allComment($post_id)
    {
        try {
            $post = Post::where('id', $post_id)->firstOrFail();
            $data = $post->comments()->orderByDesc('likes_count')->withPaginated(5);
            return response()->json($data);
        }
        catch (ModelNotFoundException $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No post found'
            ],404);
        }
    }


    /**
     * Store a new comment.
     */
    public function createComment(CommentRequest $request, Post $post)
    {
        //$post = Post::firstWhere('id', $request->id);
        $user = $request->user();
        $data = DB::transaction(function() use ($request, $user, $post) {
            $comment = $user->comments()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'body' => $request->input('body')
            ]);
            $comment->load('user');
            if ($post->user->isNot($user)) {
//                $post->user->notify(new NotifyUponAction(
//                    $user,
//                    Notification::COMMENTED_ON_POST,
//                    "/post/{$post->slug}"
//                ));
            }
            $comment = Comment::firstWhere('id', $comment->id);
            return $comment;
        });

        return response()->json(compact('data'), 201);
    }


    /**
     * Update a comment.
     */
    public function updateComment(CommentRequest $request, Post $post, Comment $comment)
    {
        $this->authorize('update', $comment);

        if ($comment->body === $request->input('body')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No changes was made',
            ],401);
        }

        $comment->update($request->only('body'));

        return response()->json(compact('comment'), 200);
    }


    /**
     * Delete an existing comment.
     */
    public function deleteComment(Request $request, Post $post, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment deleted'
        ],200);
    }


    /**
     * Add the comment to the list of likes.
     */
    public function likeComment(Request $request, Post $post, Comment $comment)
    {
        $this->authorize('like', $comment);

        $liker = $request->user();

        DB::transaction(function() use ($liker, $comment) {
            $liker->likedComments()->attach($comment);

            if ($comment->user->isNot(auth()->user())) {
//                $comment->user->notify(new NotifyUponAction(
//                    $liker,
//                    Notification::LIKED_COMMENT,
//                    "/post/{$comment->post->slug}"
//                ));
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Comment liked',
            'data' => $comment->likers()->count(),
        ]);
    }


    /**
     * Remove the comment from the list of likes.
     */
    public function unlikeComment(Request $request, Post $post, Comment $comment)
    {
        $this->authorize('unlike', $comment);

        $request->user()->likedComments()->detach($comment);

        return response()->json([
            'status' => 'success',
            'message' => 'Comment unliked',
            'data' => $comment->likers()->count(),
        ]);
    }



    /**
     * Explore feed.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exploreIndex(Request $request)
    {
        $posts = Post::where(function ($query) {
            //sort by comments
            //likes
            //views
            //location
            //filter by video
            return $query->where('audience', 'everyone');
        })->withPaginated(3);
        return response()->json([
            'success' => true,
            'message'=> 'Explore gallery retrieved',
            'data' => $posts
        ]);
    }


    /**
     * Get paginated news feed posts.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exploreTray(Request $request, Post $post)
    {
        $ids = Post::where('id','!=',$post->id)->get()->toArray();
        $posts = Post::where(function ($query) use ($post) {
            return $query->where('id', $post->id)->with('level');
        })->get()->toArray();
        $result = array_merge($posts, $ids);
        return response()->json([
            'status' => 'success',
            'message' => 'Explore item retrieved',
            'data' => $result
        ], 200);
    }

}
