<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sexo extends Model {
    use HasFactory;
    protected $table = 'Sexo';
    protected $primaryKey = 'id_sexo';
    public $timestamps = false;
    protected $fillable = ['sexo'];
}