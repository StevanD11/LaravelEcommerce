<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Korpa;
use App\Models\Product;
use App\Models\BojaVelicine;
use Illuminate\Support\Facades\DB;


class KorpaController extends Controller
{

    public function dodaj(Request $request)
    {
        if (auth('sanctum')->check()) {

            $user_id = auth('sanctum')->user()->id;
            $product_id = $request->product_id;
            $velicina = $request->velicina;
            $boja = $request->boja;
            $kolicina = $request->kolicina;

            if (Korpa::where('user_id', $user_id)->where('product_id', $product_id)->where('boja', $boja)->where('velicina', $velicina)->exists()) {
                return response()->json([
                    'status' => 205,
                    'message' => 'Izabrani model u željenoj boji i veličini je već unet u korpu!'
                ]);
            } else {

                $a = BojaVelicine::with('product')->where('product_id', $product_id)->where('boja', $boja)->where('velicina', $velicina)->first();
                if (!$a) {
                    $product = Product::find($product_id);
                    $bv = BojaVelicine::where('product_id', $product_id)->where('boja', $boja)->get();
                    $string_niz = '';
                    foreach ($bv as $b) {
                        $string_niz .= $b->velicina . ' ';
                    }
                    return response()->json([
                        'status' => 203,
                        'message' => $product->name . ' u boji ' . $boja . ' dostupan je u veličinama ' . $string_niz . '!'
                    ]);
                }


                if ($a->stanje == 0) {
                    return response()->json([
                        'status' => 203,
                        'message' => $a->product->name . ' u boji ' . $a->boja . ' i velicini ' . $a->velicina . ' trenutno nije dostupan!'
                    ]);
                }

                $stanje = $a->stanje;
                if ($stanje < $kolicina) {
                    return response()->json([
                        'status' => 203,
                        'message' => 'Maksimalan broj porudžbine za ovaj model iznosi: ' . $stanje
                    ]);
                }

                $elementKorpa = new Korpa();
                $elementKorpa->user_id = $user_id;
                $elementKorpa->product_id = $product_id;
                $elementKorpa->velicina = $velicina;
                $elementKorpa->boja = $boja;
                $elementKorpa->kolicina = $kolicina;
                $elementKorpa->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Proizvod uspešno ubačen u korpu!'
                ]);
            }
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Morate biti ulogovani da biste ubacili proizvod u korpu!'
            ]);
        }
    }


    public function getKorpa()
    {

        if (auth('sanctum')->check()) {
            $user_id = auth('sanctum')->user()->id;

            $products = DB::table('products')
                ->join('korpas', 'products.id', '=', 'korpas.product_id')
                ->select('products.*', 'korpas.*')
                ->where('korpas.user_id', '=', $user_id)
                ->get();

            return response()->json([
                'status' => 200,
                'products' => $products
            ]);
        } else {
        }
    }


    public function obrisi($id)
    {
        if (auth('sanctum')->check()) {
            $user_id = auth('sanctum')->user()->id;
            Korpa::where('user_id', $user_id)->where('id', $id)->first()->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Uspešno obrisan proizvod iz korpe!'
            ]);
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Morate biti ulogovani da biste obrisali proizvod iz korpe!'
            ]);
        }
    }
}
