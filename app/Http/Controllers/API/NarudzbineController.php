<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BojaVelicine;
use App\Models\Korpa;
use App\Models\Narudzbine;
use App\Models\StavkaNarudzbine;
use CreateStavkaNarudzbinesTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;

class NarudzbineController extends Controller
{
    public function naruci(Request $request)
    {
        if (auth('sanctum')->check()) {

            $user_id = auth('sanctum')->user()->id;


            $validator = Validator::make($request->all(), [
                'ukupan_iznos' => 'numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Greška pri naručivanju!' . $validator->errors()
                ]);
            } else {


                $narudzbina = Narudzbine::create([
                    'user_id' => $user_id,
                    'ime' => $request->input('ime'),
                    'prezime' => $request->input('prezime'),
                    'email' => $request->input('email'),
                    'telefon' => $request->input('telefon'),
                    'grad' => $request->input('grad'),
                    'postanski_broj' => $request->input('postanski_broj'),
                    'adresa' => $request->input('adresa'),
                    'placanje' => $request->input('placanje'),
                    'ukupan_iznos' => $request->input('ukupan_iznos')
                ]);

                $modeli = Korpa::where('user_id', auth('sanctum')->user()->id)->get();
                try {
                    DB::transaction(function () use ($narudzbina, $modeli) {

                        foreach ($modeli as $m) {
                            $product = Product::where('id', $m->product_id)->first();
                            StavkaNarudzbine::create([
                                'narudzbina_id' => $narudzbina->id,
                                'product_id' => $m->product_id,
                                'boja' => $m->boja,
                                'velicina' => $m->velicina,
                                'kolicina' => $m->kolicina,
                                'cena' => $product->price,
                                'iznos' => $m->kolicina * $product->price
                            ]);

                            $boja_velicina_red = BojaVelicine::where('product_id', $m->product_id)->where('velicina', $m->velicina)->where('boja', $m->boja)->first();
                            $boja_velicina_red->stanje = $boja_velicina_red->stanje - $m->kolicina;

                            DB::table('boja_velicines')->where('product_id', $m->product_id)->where('velicina', $m->velicina)
                                ->where('boja', $m->boja)->update([
                                    'stanje' => $boja_velicina_red->stanje
                                ]);

                            $m->delete();
                        }
                    });
                } catch (\Exception $exception) {
                    return response()->json([
                        'status' => 500,
                        'message' => $exception->getMessage()
                    ]);
                }

                return response()->json([
                    'status' => 200,
                    'message' => 'Vaša narudžbina je uspešno sačuvana!'
                ]);
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Morate biti ulogovani da biste naručili!'
            ]);
        }
    }
}
