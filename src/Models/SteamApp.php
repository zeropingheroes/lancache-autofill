<?php

namespace Zeropingheroes\LancacheAutofill\Models;

use Illuminate\Database\Eloquent\Model;

class SteamApp extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name'];

    /**
     * Get the comments for the blog post.
     */
    public function queueItems()
    {
        return $this->hasMany('Zeropingheroes\LancacheAutofill\Models\SteamQueueItem');
    }
}