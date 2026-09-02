<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A contact / demo request submitted through the storefront's contact form.
 * This is the only persisted entity in the storefront (see ADR-001).
 */
class ContactRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'organization',
        'tier',
        'message',
    ];
}
