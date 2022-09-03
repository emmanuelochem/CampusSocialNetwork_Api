<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'body',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'id',
        'user_id',
        //'post_id',
        'pivot',
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_own_comment',
        'is_liked',
        'is_edited',
        'timestamp',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'user:id,nickname,photo'
    ];

    /**
     * The number of relationships that should always be loaded.
     *
     * @var array
     */
    protected $withCount = [
        'likers as likes_count',
    ];

    // =============================
    // OVERRIDE DEFAULTS
    // =============================



    // =============================
    // CUSTOM ATTRIBUTES
    // =============================

    /**
     * Check if the comment is owned by the auth user.
     *
     * @return bool
     */
    public function getIsOwnCommentAttribute(): bool
    {
        return $this->user_id === auth()->id();
    }

    /**
     * Check if the comment's body attribute has been updated.
     *
     * @return bool
     */
    public function getIsEditedAttribute(): bool
    {
        return $this->created_at < $this->updated_at;
    }

    /**
     * Check if the comment is liked by the auth user.
     *
     * @return bool
     */
    public function getIsLikedAttribute(): bool
    {
        return $this->likers()->whereKey(auth()->id())->exists();
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

        return $this->created_at->format('M d, y');
    }

    // =============================
    // RELATIONSHIPS
    // =============================

    /**
     * Get the user that owns the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that contains the current comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the users who liked the comment.
     */
    public function likers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'likable')->withPivot('created_at');
    }
}
