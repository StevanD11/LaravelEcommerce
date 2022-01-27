<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\PatikeBoje;
use App\Models\PatikeVelicine;
use App\Models\BojaVelicine;


class ProductController extends Controller
{

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'image|mimes:jpg,jpeg,png'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'message' => 'Greška prilikom čuvanja slike!' . $validator->errors()
            ]);
        } else {

            $check = Product::where('name', $request->input('name'))->where('description', $request->input('description'))
                ->where('price', $request->input('price'))
                ->where('pol', $request->input('pol'))
                ->first();

            if (!$check) {
                $product = new Product;
                $product->name = $request->input('name');
                $product->description = $request->input('description');
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $file->move('uploads/product/', $filename);
                    $product->image = 'uploads/product/' . $filename;
                }
                $product->price = $request->input('price');
                $product->pol = $request->input('pol');
                $product->save();

                try {
                    DB::transaction(function () use ($product, $request) {

                        $bojaInput = $request->input('boja');
                        $boja = new PatikeBoje;
                        $boja->boja = $bojaInput;
                        $boja->product()->associate($product);
                        $boja->save();


                        $v = $request->input('velicina');
                        $velicina = new PatikeVelicine;
                        $velicina->velicina = $v;
                        $velicina->product()->associate($product);
                        $velicina->save();


                        $boja_velicine = new BojaVelicine;
                        $boja_velicine->boja = $bojaInput;
                        $boja_velicine->velicina = $v;
                        $boja_velicine->stanje = $request->input('stanje');
                        $boja_velicine->product()->associate($product);
                        $boja_velicine->save();
                    });

                    return response()->json([
                        'status' => 200,
                        'message' => 'Uspesno sačuvan novi model i sve unete informacije!'
                    ]);
                } catch (\Exception $exception) {
                    return response()->json([
                        'status' => 500,
                        'message' => $exception->getMessage()
                    ]);
                }
            } else {

                $red_provera = BojaVelicine::where('product_id', $check->id)
                    ->where('boja', $request->input('boja'))->where('velicina', $request->input('velicina'))->where('stanje', $request->input('stanje'))->first();

                if ($red_provera) {
                    return response()->json([
                        'status' => 505,
                        'message' => 'Model sa identičnim podacima postoji u bazi!'
                    ]);
                }


                try {
                    DB::transaction(function () use ($check, $request) {
                        $checkBoja = PatikeBoje::where('product_id', $check->id)->where('boja', $request->input('boja'))->first();
                        if (!$checkBoja) {
                            $bojaInput = $request->input('boja');
                            $boja = new PatikeBoje;
                            $boja->boja = $bojaInput;
                            $boja->product()->associate($check);
                            $boja->save();
                        }


                        $checkVelicina = PatikeVelicine::where('product_id', $check->id)->where('velicina', $request->input('velicina'))->first();
                        if (!$checkVelicina) {
                            $v = $request->input('velicina');
                            $velicina = new PatikeVelicine;
                            $velicina->velicina = $v;
                            $velicina->product()->associate($check);
                            $velicina->save();
                        }

                        $checkBV = BojaVelicine::where('product_id', $check->id)->where('velicina', $request->input('velicina'))->where('boja', $request->input('boja'))
                            ->where('stanje', $request->input('stanje'))->first();
                        if (!$checkBV) {
                            $boja_velicine = new BojaVelicine;
                            $boja_velicine->boja = $request->input('boja');
                            $boja_velicine->velicina = $request->input('velicina');
                            $boja_velicine->stanje = $request->input('stanje');
                            $boja_velicine->product()->associate($check);
                            $boja_velicine->save();
                        }
                    });

                    return response()->json([
                        'status' => 200,
                        'message' => 'Uspešno uneti podaci za model ' . $check->name
                    ]);
                } catch (\Exception $exception) {
                    return response()->json([
                        'status' => 500,
                        'message' => $exception->getMessage()
                    ]);
                }
            }
        }
    }


    public function getAll()
    {
        $products = Product::with('velicine')->with('boje')->with('bojaVelicine')->get();

        return response()->json([
            'status' => 200,
            'products' => $products
        ]);
    }

    public function getAllAdminView()
    {
        $products = DB::table('boja_velicines')
            ->join('products', 'boja_velicines.product_id', '=', 'products.id')
            ->select('boja_velicines.*', 'products.name', 'products.description', 'products.image', 'products.price', 'products.pol')
            ->orderBy('boja_velicines.product_id')
            ->get();

        return response()->json([
            'status' => 200,
            'products' => $products
        ]);
    }

    public function edit($id)
    {
        $product = BojaVelicine::with('product')->find($id);

        Product::with('velicine')->with('boje')->with('bojaVelicine')->find($id);
        if ($product) {
            return response()->json([
                'status' => 200,
                'product' => $product
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Traženi proizvod ne postoji!'
            ]);
        }
    }



    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required',
            'stanje' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 500,
                'message' => 'Greška pri izmeni proizvoda!' . $validator->errors()
            ]);
        } else {

            $boja_velicine = BojaVelicine::find($id);
            $boja_stara = $boja_velicine->boja;
            $velicina_stara = $boja_velicine->velicina;
            $product = Product::find($boja_velicine->product_id);
            $boja = $request->input('boja');
            $velicina = $request->input('velicina');
            $stanje = $request->input('stanje');


            if ($product) {
                $izmena = false;
                if (($product->name != $request->input('name')) ||  ($product->price != $request->input('price'))
                    || ($product->pol != $request->input('pol'))
                ) {
                    $product->name = $request->input('name');
                    $product->description = $request->input('description');
                    if ($request->hasFile('image')) {
                        $path = $product->image;
                        if (File::exists($path)) {
                            File::delete($path);
                        }
                        $file = $request->file('image');
                        $extension = $file->getClientOriginalExtension();
                        $filename = time() . '.' . $extension;
                        $file->move('uploads/product/', $filename);
                        $product->image = 'uploads/product/' . $filename;
                    }
                    $product->price = $request->input('price');
                    $product->pol = $request->input('pol');
                    $product->save();
                    $izmena = true;
                }

                $red_postoji = BojaVelicine::where('product_id', $product->id)
                    ->where('boja', $boja)->where('velicina', $velicina)->where('stanje', $stanje)->first();

                if ($izmena && $red_postoji) {
                    return response()->json([
                        'status' => 501,
                        'message' => 'Ažurirane informacije o modelu(model, opis, slika, cena, pol)! Identičan red (boja/veličina/stanje) postoji u bazi! '
                    ]);
                }

                if (!$izmena && $red_postoji) {
                    return response()->json([
                        'status' => 505,
                        'message' => 'Model sa identičnim podacima postoji u bazi!'
                    ]);
                }

                try {
                    DB::transaction(function () use ($product, $request, $boja_velicine, $boja_stara, $velicina_stara) {


                        PatikeBoje::where('product_id', $boja_velicine->product_id)
                            ->where('boja', $boja_stara)
                            ->delete();


                        PatikeBoje::create([
                            'product_id' => $product->id,
                            'boja' => $request->input('boja')
                        ]);


                        PatikeVelicine::where('product_id', $boja_velicine->product_id)
                            ->where('velicina', $velicina_stara)
                            ->delete();


                        PatikeVelicine::create([
                            'product_id' => $product->id,
                            'velicina' => $request->input('velicina')
                        ]);

                        DB::table('boja_velicines')->where('id', $boja_velicine->id)->update([
                            'boja' => $request->input('boja'),
                            'velicina' => $request->input('velicina'),
                            'stanje' => $request->input('stanje'),
                        ]);
                    });
                } catch (\Exception $exception) {
                    return response()->json([
                        'status' => 500,
                        'message' => $exception->getMessage()
                    ]);
                }

                return response()->json([
                    'status' => 200,
                    'message' => 'Podaci o modelu su uspešno izmenjeni!'
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Traženi model ne postoji!'
                ]);
            }
        }
    }

    public function delete($id)
    {
        $product = BojaVelicine::find($id);
        if ($product) {
            BojaVelicine::destroy($id);
            return response()->json([
                'status' => 200,
                'message' => 'Podaci o modelu uspešno obrisani!'
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Traženi model ne postoji!'
            ]);
        }
    }


    public function search($kriterijum, $key)
    {

        if ($kriterijum == 'cena') {

            if ($key === 'izaberi') {
                return response()->json([
                    'status' => 404,
                    'message' => "Potrebno je da odaberete poredak za pretragu!"
                ]);
            }

            if ($key == 'asc') {
                $products = Product::with('velicine')->with('boje')->with('bojaVelicine')->orderBy('price')->get();
                if (count($products) >= 1) {
                    return response()->json([
                        'status' => 200,
                        'products' => $products
                    ]);
                } else {
                    return response()->json([
                        'status' => 404,
                        'message' => 'Trenutno nema dostupnih proizvoda!'
                    ]);
                }
            } else if ($key == 'desc') {
                $products = Product::with('velicine')->with('boje')->with('bojaVelicine')->orderByDesc('price')->get();
                if (count($products) >= 1) {
                    return response()->json([
                        'status' => 200,
                        'products' => $products
                    ]);
                } else {
                    return response()->json([
                        'status' => 404,
                        'message' => 'Trenutno nema dostupnih proizvoda!'
                    ]);
                }
            }
        } else if ($kriterijum == 'pol') {

            if ($key === 'izaberi') {
                return response()->json([
                    'status' => 404,
                    'message' => "Potrebno je da odaberete pol za pretragu!"
                ]);
            }

            if ($key == 'muski') {
                $products = Product::with('velicine')->with('boje')->with('bojaVelicine')->where('pol', 'muski')->get();
                if (count($products) >= 1) {
                    return response()->json([
                        'status' => 200,
                        'products' => $products
                    ]);
                } else {
                    return response()->json([
                        'status' => 404,
                        'message' => 'Trenutno nema dostupnih proizvoda za muškarce!'
                    ]);
                }
            } else if ($key == 'zenski') {
                $products = Product::with('velicine')->with('boje')->with('bojaVelicine')->where('pol', 'zenski')->get();
                if (count($products) >= 1) {
                    return response()->json([
                        'status' => 200,
                        'products' => $products
                    ]);
                } else {
                    return response()->json([
                        'status' => 404,
                        'message' => 'Trenutno nema dostupnih proizvoda za žene!'
                    ]);
                }
            }
        } else if ($kriterijum === 'velicina') {

            if ($key === 'izaberi') {
                return response()->json([
                    'status' => 404,
                    'message' => "Potrebno je da odaberete veličinu za pretragu!"
                ]);
            }

            $products = DB::table('products')
                ->join('patike_velicines', 'products.id', '=', 'patike_velicines.product_id')
                ->select('products.*')
                ->where('patike_velicines.velicina', '=', $key)
                ->get();

            $nizID = array();
            for ($i = 0; $i < count($products); $i++) {
                array_push($nizID, $products[$i]->id);
            }

            $rezultat = Product::with('velicine')->with('boje')->with('bojaVelicine')->whereIn('id', $nizID)->get();


            if (count($products) >= 1) {
                return response()->json([
                    'status' => 200,
                    'products' => $rezultat
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => "Nema dostupnih modela veličine $key!"
                ]);
            }
        } else if ($kriterijum === 'boja') {

            if ($key === 'izaberi') {
                return response()->json([
                    'status' => 404,
                    'message' => "Potrebno je da odaberete boju za pretragu!"
                ]);
            }

            $products = DB::table('products')
                ->join('patike_bojes', 'products.id', '=', 'patike_bojes.product_id')
                ->select('products.*')
                ->where('patike_bojes.boja', '=', $key)
                ->get();

            $nizID = array();
            for ($i = 0; $i < count($products); $i++) {
                array_push($nizID, $products[$i]->id);
            }

            $rezultat = Product::with('velicine')->with('boje')->with('bojaVelicine')->whereIn('id', $nizID)->get();


            if (count($products) >= 1) {
                return response()->json([
                    'status' => 200,
                    'products' => $rezultat
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => "Nema dostupnih modela u boji $key!"
                ]);
            }
        } else {

            $products = Product::with('velicine')->with('boje')->where('name', 'like', "%$key%")->get();
            if (count($products) >= 1) {
                return response()->json([
                    'status' => 200,
                    'products' => $products
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Nema dostupnih modela sa traženim nazivom!'
                ]);
            }
        }
    }




    public function velicineZaBoju(Request $request)
    {
        $boja = $request->input('boja');
        $id = $request->input('id');

        $redovi = BojaVelicine::where('product_id', $id)->where('boja', $boja)->get();
        $niz = array();
        foreach ($redovi as $red) {
            $niz[] = $red->velicina;
        }

        return response()->json([
            'status' => 200,
            'niz' => $niz
        ]);
    }
}
