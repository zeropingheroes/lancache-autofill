<?php

namespace Zeropingheroes\LancacheAutofill\Models;

use Illuminate\Database\Eloquent\Model;

class SteamQueueItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'steam_queue';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the Steam app associated with the queue item.
     */
    public function app()
    {
        return $this->belongsTo('Zeropingheroes\LancacheAutofill\Models\SteamApp');
    }
}