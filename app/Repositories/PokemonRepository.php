<?php

namespace App\Repositories;

use App\Jobs\CargaPokemonesJob;
use App\Models\Pokemon;
use App\Models\Region;
use App\Models\TipoPokemon;
use App\Services\PokemonService;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class PokemonRepository
{
    public function registrarPokemon($request)
    {
        try {
            $region = Region::where('reg_nombre', $request->region)->first();

            $pokemon = new Pokemon();
            $pokemon->nombre = $request->nombre;
            $pokemon->region_id = $region->id;
            $pokemon->save();
            return response()->json(["pokemon" => $pokemon], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function actualizarPokemon($request)
    {
        try {
            $pokemon = Pokemon::find($request->id);
            $pokemon->nombre = $request->nombre;
            $pokemon->save();
            return response()->json(["pokemon" => $pokemon], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::info([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ]);

            return response()->json([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    //Primer Punto a Desarrollar
    public function listarPokemones($request)
    {
        try {
            $pokemon = Pokemon::whereHas('region', function ($q) use ($request) {
                $q->where('reg_nombre', $request->reg_nombre);
            })->get();
    
            return response()->json(["pokemon" => $pokemon], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::info([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__,
            ]);
            return response()->json([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__,
            ], Response::HTTP_BAD_REQUEST);
        }
    
    
        // try {
        //     $pokemon = Pokemon::whereIn('id', [3, 4, 5, 6, 7])->get();
        //     return response()->json(["pokemon" => $pokemon], Response::HTTP_OK);
        // } catch (Exception $e) {
        //     Log::info([
        //         "error" => $e->getMessage(),
        //         "linea" => $e->getLine(),
        //         "file" => $e->getFile(),
        //         "metodo" => __METHOD__
        //     ]);

        //     return response()->json([
        //         "error" => $e->getMessage(),
        //         "linea" => $e->getLine(),
        //         "file" => $e->getFile(),
        //         "metodo" => __METHOD__
        //     ], Response::HTTP_BAD_REQUEST);
        // }
    }

    //Segundo Punto a Desarrollar
    public function listarPokemonesPorTipo($request)
    {
        try {
            $pokemones = Pokemon::where(function ($query) use ($request) {
                $query->whereHas('tipoUno', function (Builder $query) use ($request) {
                    $query->where('tip_nombre', $request->tip_nombre);
                })->orWhereHas('tipoDos', function (Builder $query) use ($request) {
                    $query->where('tip_nombre', $request->tip_nombre);
                });
            })->with(['tipoUno:id,tip_nombre', 'tipoDos:id,tip_nombre'])->get();
    
            return response()->json(["pokemones" => $pokemones], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ]);
            return response()->json([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    // Tercer Punto a Desarrollar
    public function buscarPokemonPorNombre($request)
    {
        try {
            $nombreIngresado = $request->nombre;

            $pokemon = Pokemon::whereRaw('SOUNDEX(nombre) = SOUNDEX(?)', [$nombreIngresado])
                ->with('region:id,reg_nombre', 'tipoUno:id,tip_nombre', 'tipoDos:id,tip_nombre')
                ->first();

            return response()->json(["pokemon" => $pokemon], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ]);
            return response()->json([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    
    public function eliminarPokemon($request)
    {
        try {
            $pokemon = Pokemon::find($request->id);
            $pokemon->delete();

            return response()->json(["pokemon" => $pokemon], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::info([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ]);

            return response()->json([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cargarPokemones()
    {
        try {
            for ($i = 1; $i <= 9; $i++) {
              //$this->cargaPokemonPorRegion($i);
              CargaPokemonesJob::dispatch($i);
            }

            return response()->json(["ok"], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::info([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ]);

            return response()->json([
                "error" => $e->getMessage(),
                "linea" => $e->getLine(),
                "file" => $e->getFile(),
                "metodo" => __METHOD__
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cargaPokemonPorRegion($id)
    {
        $pokemonServiceRegion = new PokemonService;
        $pokemones = $pokemonServiceRegion->CargarRegiones($id);
        $region = new Region();
        $region->reg_nombre = $pokemones['body']['main_region']['name'];
        $region->save();
        foreach ($pokemones['body']['pokemon_species'] as $pokemon) {

           // Log::info(["pokemon a revisar "=> $pokemon]);

            $idPokedex = str_replace('https://pokeapi.co/api/v2/pokemon-species/','', $pokemon['url']);
            Log::info(["id pokedex"=> $idPokedex]);
            // $idPokedex = intval($idPokedex);
            $poke = Pokemon::where('nombre', $pokemon['name'])->first();

            // // Requisito 4
            // if ($poke) {
            //     // Actualizar el valor de numero_pokedex con idpokedex
            //     $poke->numero_pokedex = $idPokedex;
            //     $poke->save();
            // }
            
            $pokemonServiceTipo = new PokemonService;
            $pokemonTipo = $pokemonServiceTipo->CargarPokemonIndividual($idPokedex);

            //Log::info([" poke x tipo"=> $pokemonTipo['body']['types'][0]['type']['name']]);
         
            $tipoUno = TipoPokemon::where('tip_nombre', $pokemonTipo['body']['types'][0]['type']['name'])->first();
            if(!$tipoUno){
                $tipoUno = new TipoPokemon();
                $tipoUno->tip_nombre = $pokemonTipo['body']['types'][0]['type']['name'];
                $tipoUno->save();
            }
            if(isset($pokemonTipo['body']['types'][1])){
                //Log::info([" poke x tipo"=> $pokemonTipo['body']['types'][1]['type']['name']]);

                $tipoDos = TipoPokemon::where('tip_nombre', $pokemonTipo['body']['types'][1]['type']['name'])->first();
                if(!$tipoDos){
                    $tipoDos = new TipoPokemon();
                    $tipoDos->tip_nombre = $pokemonTipo['body']['types'][1]['type']['name'];
                    $tipoDos->save();
                }
            }

            $poke = new Pokemon();
            $poke->nombre = $pokemon['name'];
            $poke->region_id = $region->id;
            $poke->tipo_uno_id =$tipoUno->id;
            $poke->tipo_dos_id = isset($pokemonTipo['body']['types'][1]) ? $tipoDos->id : null;
            $poke->numero_pokedex = $idPokedex;
            $poke->save();
        }
    }
}
