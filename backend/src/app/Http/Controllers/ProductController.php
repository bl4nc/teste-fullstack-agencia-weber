<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    public function insertProduct(Request $req)
    {
        $this->validate($req, [
            'product_name' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'numeric',
            'category' => 'required',
            'status' => 'required',
        ]);
        $category = Category::find($req->category);
        if (!$category) {
            return response()->json(array(
                "success" => false,
                "message" => "A categoria informada não existe.",
            ), 422);
        }
        if (!$req->hasFile('picture') && $req->file('picture')->isValid()) {
            return response()->json(array(
                "success" => false,
                "message" => "A foto do produto é obrigatória.",
            ), 422);
        };
        $picture = $req->file('picture');
        $valid_formats = ['png', 'jpg', 'jpeg'];
        if (in_array($picture->extension(), $valid_formats)) {
            response()->json(array(
                "success" => false,
                "message" => "O formato da foto é invalido.",
            ), 422);
        }
        $path =  __DIR__ . "/../../../storage/uploads/";
        $newFileName = Str::uuid()  . "." . $picture->extension();
        try {
            $picture->move($path, $newFileName);
        } catch (\Throwable $th) {
            return response()->json(array(
                "success" => false,
                "message" => "Erro para mover a foto do produto.",
                "erro" => $th->getMessage(),
            ), 500);
        }

        $product = new Product(array(
            "product_id" => Str::uuid(),
            "product_name" => $req->product_name,
            "product_description" => $req->product_description,
            "quantity" => $req->quantity ?? 0,
            "price" => $req->price,
            "category" => $req->category,
            "status" => $req->status,
            "picture" => $newFileName,
        ));

        try {
            $product->save();
            return response()->json(array(
                "success" => true,
                "message" => "Produto inserido!",
            ));
        } catch (\Throwable $th) {
            return response()->json(array(
                "success" => false,
                "message" => "Erro para inserir produto",
                "erro" => $th->getMessage()
            ), 500);;
        }
    }

    public function deleteProduct(string $product_id)
    {
        $product = Product::find($product_id);
        if ($product) {
            try {
                $picture_path =  __DIR__ . "/../../../storage/uploads/";
                try {
                    @unlink($picture_path . $product->picture);
                } catch (\Throwable $th) {
                    return response()->json(array(
                        "success" => false,
                        "message" => "Erro para deletar o anexo",
                        "erro" => $th->getMessage()
                    ), 500);
                }
                $product->delete();
                return response()->json(array(
                    "success" => true,
                    "message" => "Categoria deletada!"
                ));
            } catch (\Throwable $th) {
                return response()->json(array(
                    "success" => false,
                    "message" => "Erro para deletar categoria",
                    "erro" => $th->getMessage()
                ), 500);
            }
        } else {
            return response()->json(array(
                "success" => false,
                "message" => "Categoria não existe."
            ), 413);
        }
    }

    public function updateProduct(Request $req, string $product_id)
    {
        $product = Product::find($product_id);
        if ($product) {
            $this->validate($req, [
                'price' => 'numeric',
                'quantity' => 'numeric',
            ]);
            if ($req->category) {
                $category = Category::find($req->category);
                if (!$category) {
                    return response()->json(array(
                        "success" => false,
                        "message" => "A categoria informada não existe.",
                    ), 422);
                }
            }
            if ($req->hasFile('picture') && $req->file('picture')->isValid()) {
                $picture = $req->file('picture');
                $valid_formats = ['png', 'jpg', 'jpeg'];
                if (in_array($picture->extension(), $valid_formats)) {
                    response()->json(array(
                        "success" => false,
                        "message" => "O formato da foto é invalido.",
                    ), 422);
                }
                $path =  __DIR__ . "/../../../storage/uploads/";
                $newFileName = Str::uuid()  . "." . $picture->extension();
                try {
                    $picture->move($path, $newFileName);
                    @unlink($path . $product->picture);
                } catch (\Throwable $th) {
                    return response()->json(array(
                        "success" => false,
                        "message" => "Erro para mover a foto do produto.",
                        "erro" => $th->getMessage(),
                    ), 500);
                }
            };
            print_r($req->product_name);
            $product->product_name = $req->product_name ?? $product->product_name;
            $product->product_description = $req->product_description ?? $product->product_description;
            $product->quantity = $req->quantity ?? $product->quantity;
            $product->price = $req->price ?? $product->price;
            $product->category = $req->category ?? $product->category;
            $product->status = $req->status ?? $product->status;
            $product->picture = $newFileName ?? $product->picture;
            try {
                $product->save();
                return response()->json(array(
                    "success" => true,
                    "message" => "Produto atualizado!",
                ));
            } catch (\Throwable $th) {
                return response()->json(array(
                    "success" => false,
                    "message" => "Erro para atualizar produto",
                    "erro" => $th->getMessage()
                ), 500);;
            }
        } else {
            return response()->json(array(
                "success" => false,
                "message" => "Produto não existe."
            ), 413);
        }
    }

    public function selectProduct(string $product_id)
    {
        $product = Product::find($product_id);
        if ($product) {
            return response()->json(array(
                "success" => true,
                "message" => "Produto encontrada.",
                "produto" => $product
            ));
        } else {
            return response()->json(array(
                "success" => false,
                "message" => "Produto não existe."
            ), 413);
        }
    }

    public function selectAllProducts()
    {
        $product = Product::all();
        try {
            return response()->json(array(
                "success" => true,
                "produtos" => $product
            ));
        } catch (\Throwable $th) {
            return response()->json(array(
                "success" => false,
                "message" => "Erro para encontrar produtos no BD",
                "erro" => $th->getMessage()
            ), 500);
        }
    }
}
