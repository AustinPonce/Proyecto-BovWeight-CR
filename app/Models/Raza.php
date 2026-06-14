<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Raza extends Model {
    use HasFactory;
    protected $table = 'Raza';
    protected $primaryKey = 'id_raza';
    public $timestamps = false;
    protected $fillable = ['raza'];
}