<?php

namespace Zeropingheroes\LancacheAutofill\Models;

use Illuminate\Database\Eloquent\Model;

class SteamAccount extends Model
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
    protected $fillable = ['username'];
}