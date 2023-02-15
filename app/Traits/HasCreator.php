<?php

namespace App\Traits;

use App\Models\User;

trait HasCreator
{
    /**
     * Get the user that created the model.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, $this->getCreatorColumn());
    }

    /**
     * Get the name of the "creator" column.
     */
    public function getCreatorColumn(): string
    {
        return $this->creatorColumn ?? 'user_id';
    }
}