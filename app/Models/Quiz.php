<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'is_mandatory', 'status'];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];
    
    public function answers(){
        return $this->hasMany(Answer::class);
    }
}
