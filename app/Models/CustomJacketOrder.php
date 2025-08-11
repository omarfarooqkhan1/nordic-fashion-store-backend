<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomJacketOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'color',
        'size',
        'material',
        'lining',
        'monogram',
        'notes',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
