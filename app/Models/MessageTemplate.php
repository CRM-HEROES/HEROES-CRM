<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'body',
        'subject',
        'attachments',
        'order',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        $attributes['attachments'] = array_map(function (array $attachment) {
            unset($attachment['path']);

            return $attachment;
        }, $this->attachments ?? []);

        return $attributes;
    }
}
