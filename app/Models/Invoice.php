<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public $guarded = ["id", "_token", "_method"];

    protected $casts = [
        'paid' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }
}
