<?php

namespace App\Http\Controllers;

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
            response()->json(array(
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
                    unlink($picture_path . $product->picture);
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

    public function updateProduct()
    {
        $req_body = $this->getBody();
        $require_fields = ['sku', 'name'];
        $this->checkRequestFields($require_fields, $req_body);
        if (isset($req_body['picture'])) {
        }
        try {
            $dao = new ProductDAO;
            $old_product_data = $dao->SelectUniqueProduct($req_body['sku']);
            if (count($old_product_data)  === 0) $this->responseJson(500, array(
                "error" => true,
                "message" => "Invalid SKU."
            ));
        } catch (\Throwable $th) {
            $this->responseJson(500, array(
                "error" => true,
                "message" => $th->getMessage()
            ));
        }

        if (isset($req_body['category'])) {
            #Check categories
            foreach ($req_body['category'] as $code) {
                if (!intval($code)) $this->responseJson(500,  array(
                    "error" => true,
                    "message" => "Invalid code: " . $code
                ));
            }
            #Validate categories
            $categoryDAO = new CategoryDAO;
            $categories = implode(',', $req_body['category']);
            try {
                $select_data = $categoryDAO->SelectCategories($categories);
                if (count($select_data) != count($req_body['category'])) {
                    foreach ($req_body['category'] as $code) {
                        $find = false;
                        foreach ($select_data as $data) {
                            if ($data['code'] === $code) $find = true;
                        }
                        if (!$find) {
                            $this->responseJson(500, array(
                                "error" => true,
                                "message" => "Category code: " . $code . " not found."
                            ));
                        }
                    }
                }
            } catch (\Throwable $th) {
                if (!intval($code)) $this->responseJson(500, array(
                    "error" => true,
                    "message" => $th->getMessage()
                ));
            }
        }

        $picture = $this->getFiles()['picture'] ?? '';
        #Image Upload
        if ($picture != '') {
            #Verify if exist a old picture
            if (explode('/', $picture['type'])[0] != 'image') {
                $this->responseJson(500, array(
                    "error" => true,
                    "message" => 'Invalid image format.'
                ));
            }
            ###
            $folder = __DIR__ . "/../../uploads/";
            $file = Util::moveFile($picture, $folder);
            if (!$file['success']) {
                $this->responseJson(500, array(
                    "error" => true,
                    "message" => $file['err']
                ));
            }
            $picture = $file['file_name'];
        }
        $dao = new ProductDAO;
        $product = new ProductModel(
            $req_body['name'],
            $req_body['sku'],
            $req_body['price'] ?? $old_product_data[0]['price'],
            $req_body['description'] ?? $old_product_data[0]['description'],
            $req_body['quantity'] ?? $old_product_data[0]['quantity'],
            $categories ?? $old_product_data[0]['category'],
            $picture ?? $old_product_data[0]['picture']
        );
        try {
            $dao->updateProduct($product);
            $this->responseJson(200, array(
                "success" => true,
                "message" => "Updated product"
            ));
        } catch (\Throwable $th) {
            $this->responseJson(500, array(
                "error" => true,
                "message" => $th->getMessage()
            ));
        }
        #Remove old picture if insert a new picture.
        if ($picture != '' && !empty($old_product_data[0]['picture'])) {
            $folder = __DIR__ . "/../../uploads/";
            @unlink($folder . $old_product_data[0]['picture']);
        }
    }

    public function removePicture()
    {
        $req_body = $this->getBody();
        $require_fields = ['sku'];
        $this->checkRequestFields($require_fields, $req_body);
        try {
            $dao = new ProductDAO;
            $old_product_data = $dao->SelectUniqueProduct($req_body['sku']);
            if (count($old_product_data)  === 0) $this->responseJson(500, array(
                "error" => true,
                "message" => "Invalid SKU."
            ));
        } catch (\Throwable $th) {
            $this->responseJson(500, array(
                "error" => true,
                "message" => $th->getMessage()
            ));
        }
        if (empty($old_product_data[0]['picture'])) $this->responseJson(500, array(
            "error" => true,
            "message" => "Product has no image."
        ));
        $dao = new ProductDAO;
        try {
            $dao->removePicture($req_body['sku']);
        } catch (\Throwable $th) {
            $this->responseJson(500, array(
                "error" => true,
                "message" => $th->getMessage()
            ));
        }
        $folder = __DIR__ . "/../../uploads/";
        @unlink($folder . $old_product_data[0]['picture']);
        $this->responseJson(200, array(
            "success" => true,
            "message" => "Picture removed."
        ));
    }

    public function selectUniqueProduct()
    {
        $req_query = $this->getQuery();
        $require_fields = ['sku'];
        $this->checkRequestFields($require_fields, $req_query);
        $dao = new ProductDAO;
        try {
            $select_data = $dao->selectUniqueProduct($req_query['sku']);
            if (count($select_data) != 0) {
                $this->responseJson(200, array(
                    "success" => true,
                    "product" => $select_data[0]
                ));
            } else {
                $this->responseJson(200, array(
                    "success" => true,
                    "product" => []
                ));
            }
        } catch (\Throwable $th) {
            $this->responseJson(500, array(
                "error" => true,
                "message" => $th
            ));
        }
    }

    public function selectAllProducts()
    {
        $dao = new ProductDAO;
        try {
            $select_data = $dao->selectAllProducts();
            $products = [];
            foreach ($select_data as $data) {
                array_push($products, $data);
            }
            $this->responseJson(200, array(
                "success" => true,
                "products" => $products
            ));
        } catch (\Throwable $th) {
            $this->responseJson(500, array(
                "error" => true,
                "message" => $th->getMessage()
            ));
        }
    }

    public function selectProducts()
    {
        $req_query = $this->getQuery();
        $require_fields = ['sku'];

        $this->checkRequestFields($require_fields, $req_query);
        $skus = '';
        foreach ($req_query['sku'] as $sku) $skus .= "'" . $sku . "',";
        $skus = rtrim($skus, ",");

        $dao = new ProductDAO;
        try {
            $select_data = $dao->SelectProducts($skus);
            $products = [];
            foreach ($select_data as $data) {
                array_push($products, $data);
            }
            $this->responseJson(200, array(
                "success" => true,
                "products" => $products
            ));
        } catch (\Throwable $th) {
            $this->responseJson(500, array(
                "error" => true,
                "message" => $th->getMessage()
            ));
        }
    }
}
