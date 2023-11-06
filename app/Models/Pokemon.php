<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pokemon extends Model
{
    use HasFactory;
    protected $table = 'pokemon';

    public function region(){
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function tipoUno(){
        return $this->belongsTo(TipoPokemon::class, 'tipo_uno_id', 'id');
    }

    public function tipoDos(){
        return $this->belongsTo(TipoPokemon::class, 'tipo_dos_id', 'id');
    }

}


// relacion entre modelos 

// where has o o orwherehas 