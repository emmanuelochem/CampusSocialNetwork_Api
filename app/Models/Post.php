<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'image',
        'caption',
        'audience',
        'allowcomment',
        'user_id',1
    ];

    protected $hidden = [
       // 'id',
        'user_id',
        'pivot',
        'created_at',
        'updated_at',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'user:id,nickname,photo'
    ];

    protected $withCount = [
        'likers as likes_count',
        'comments as comments_count',
    ];


    protected $appends = [
        'is_own_post',
        'is_liked',
        'is_edited',
        'is_bookmarked',
        'timestamp',
    ];
    // =============================
    // CUSTOM ATTRIBUTES
    // =============================

    /**
     * Check if the post is owned by the auth user.
     *
     * @return bool
     */
    public function getIsOwnPostAttribute(): bool
    {
        return $this->user_id === auth()->id();
    }

    /**
     * Check if the post is liked by the auth user.
     *
     * @return bool
     */
    public function getIsLikedAttribute(): bool
    {
        return $this->likers()->whereKey(auth()->id())->exists();
    }

    /**
     * Check if the post's body attribute has been updated.
     *
     * @return bool
     */
    public function getIsEditedAttribute(): bool
    {
        return $this->created_at < $this->updated_at;
    }

    /**
     * Check if the post is bookmarked by the user.
     *
     * @return bool
     */
    public function getIsBookmarkedAttribute(): bool
    {
        return $this->bookmarkers()->whereKey(auth()->id())->exists();
    }

    /**
     * Get the time difference between now and date of creation.
     *
     * @return string
     */
    public function getTimestampAttribute(): string
    {
        if (now()->diffInSeconds($this->created_at) <= 59) {
            return 'Just now';
        }

        $minutes = now()->diffInMinutes($this->created_at);

        if ($minutes === 1) {
            return 'A minute ago';
        }

        if ($minutes >= 2 && $minutes <= 59) {
            return "{$minutes} minutes ago";
        }

        $hours = now()->diffInHours($this->created_at);

        if ($hours === 1) {
            return '1 hour ago';
        }

        if ($hours <= 23) {
            return "{$hours} hours ago";
        }

        if (now()->diffInDays($this->created_at) === 1) {
            return "Yesterday at {$this->created_at->format('g:i A')}";
        }

        return $this->created_at->format('F d, Y (g:i A)');
    }








    // =============================
    // RELATIONSHIPS
    // =============================

    /**
     * Get the user that owns the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments under the current post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the users who liked the post.
     */
    public function likers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'likable')->withPivot('created_at');
    }

    /**
     * Get the users who bookmarked the post.
     */
    public function bookmarkers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bookmarks', 'post_id', 'user_id')
            ->withPivot('created_at');
    }

}
