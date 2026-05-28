<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmChannelSetting extends Model
{
    use HasFactory;

    protected $table = 'crm_channel_settings';

    protected $fillable = ['channel', 'enabled', 'config'];

    protected $casts = [
        'enabled' => 'boolean',
        'config'  => 'array',
    ];

    public static array $CHANNELS = [
        'email'    => ['label' => 'Email',    'icon' => 'envelope'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'whatsapp'],
        'sms'      => ['label' => 'SMS',      'icon' => 'phone'],
        'push'     => ['label' => 'Push',     'icon' => 'bell'],
    ];
}
