<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nickname',
        'phone',
        'gender',
        'department',
        'photo',
        'level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'level','department',
        'password',
        'remember_token','updated_at',
        'pivot',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_self',
        'is_followed',
        'is_crush',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'date:F Y',
        'email_verified_at' => 'datetime',
    ];


    protected $with = [
        'levels', 'departments',
    ];




    // =============================
    // CUSTOM ATTRIBUTES
    // =============================

    /**
     * Check if the user is the authenticated one.
     *
     * @return bool
     */
    public function getIsSelfAttribute(): bool
    {
        return auth()->check() && $this->id === auth()->id();
    }

    /**
     * Check if the user followed by the authenticated user.
     *
     * @return bool|null
     */
    public function getIsFollowedAttribute()
    {
        if ($this->is_self) {
            return null;
        }

        return $this->followers()->whereKey(auth()->id())->exists();
    }


    /**
     * Check if the user followed by the authenticated user.
     *
     * @return bool|null
     */
    public function getIsCrushAttribute()
    {
        if ($this->is_self) {
            return null;
        }

        return $this->crushers()->whereKey(auth()->id())->exists();
    }

    // =============================
    // RELATIONSHIPS
    // =============================

    //Users Level
    public function levels(): belongsTo
    {
        return $this->belongsTo(Level::class, 'level');
    }
    //Users department
    public function departments(): belongsTo
    {
        return $this->belongsTo(Department::class, 'department');
    }


    /**
     * Get the followers of a user.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'connections', 'following_id', 'follower_id')
            ->withPivot('created_at');
    }

    /**
     * Get the  people following a user.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'connections', 'follower_id', 'following_id')
            ->withPivot('created_at');
    }
//Posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the comments for a user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the posts liked by the user.
     */
    public function likedPosts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'likable')->withPivot('created_at');
    }

    /**
     * Get the comments liked by the user.
     */
    public function likedComments(): MorphToMany
    {
        return $this->morphedByMany(Comment::class, 'likable')->withPivot('created_at');
    }

    /**
     * Get the posts bookmarked by the user.
     */
    public function bookmarks(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'bookmarks', 'user_id', 'post_id')
            ->withPivot('created_at');
    }

    /**
     * Get the  people crushing on a user.
     */
    public function crushers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'crushes', 'crushing_id', 'crusher_id')
            ->withPivot('created_at');
    }

    /**
     * Get the  people a user is crushing on .
     */
    public function crushing(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'crushes', 'crusher_id', 'crushing_id')
            ->withPivot('created_at');
    }


}
