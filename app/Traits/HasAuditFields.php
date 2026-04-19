<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAuditFields
{
    /**
     * Boot the trait to handle audit fields.
     */
    protected static function bootHasAuditFields()
    {
        static::creating(function ($model) {
            if (!$model->created_by) {
                $model->created_by = Auth::check() ? Auth::user()->email : 'SYSTEM';
            }
            if (!$model->updated_by) {
                $model->updated_by = Auth::check() ? Auth::user()->email : 'SYSTEM';
            }
        });

        static::updating(function ($model) {
            // Only update updated_by if it wasn't manually set
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = Auth::check() ? Auth::user()->email : 'SYSTEM';
            }
        });
    }
}
