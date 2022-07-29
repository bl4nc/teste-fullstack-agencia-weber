<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Faker\Core\Uuid;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    public function insertCategory(Request $req)
    {
        $this->validate($req, [
            'category_name' => 'required',
        ]);
        $category = new Category();
        $category->category_id = Str::uuid();
        $category->category_name = $req->category_name;
        try {
            $category->save();
            return response()->json(array(
                "success" => true,
                "message" => "Categoria inserida!",
            ));
        } catch (\Throwable $th) {
            return response()->json(array(
                "success" => false,
                "message" => "Erro para inserir categoria",
                "erro" => $th->getMessage()
            ), 500);;
        }
    }

    public function deleteCategory(string $category_id)
    {
        $category = Category::find($category_id);
        if ($category) {
            try {
                $category->delete();
                return response()->json(array(
                    "success" => true,
                    "message" => "Categoria deletada!"
                ));
            } catch (\Throwable $th) {
                return response()->json(array(
                    "success" => false,
                    "message" => "Erro para deletar categoria",
                    "erro" => $th->getMessage()
                ), 500);;
            }
        } else {
            return response()->json(array(
                "success" => false,
                "message" => "Categoria não existe."
            ), 413);
        }
    }

    public function updateCategory(Request $req, string $category_id)
    {
        $this->validate($req, [
            'category_name' => 'required',
        ]);
        $category = Category::find($category_id);
        if ($category) {
            $category->category_name = $req->category_name;
            try {
                $category->save();
                return response()->json(array(
                    "success" => true,
                    "message" => "Categoria atualizada!",
                ));
            } catch (\Throwable $th) {
                return response()->json(array(
                    "success" => false,
                    "message" => "Erro para atualizar categoria",
                    "erro" => $th->getMessage()
                ), 500);
            }
        } else {
            return response()->json(array(
                "success" => false,
                "message" => "Categoria não encontrada.",
            ), 413);
        }
    }

    public function selectCategory(string $category_id)
    {
        $category = Category::find($category_id);
        if ($category) {
            return response()->json(array(
                "success" => true,
                "message" => "Categoria encontrada.",
                "categoria" => $category
            ));
        } else {
            return response()->json(array(
                "success" => false,
                "message" => "Categoria não existe."
            ), 413);
        }
    }

    public function selectAllCategory()
    {
        $caterioes = Category::all();
        try {
            return response()->json(array(
                "success" => true,
                "message" => "Categoria encontrada.",
                "categorias" => $caterioes
            ));
        } catch (\Throwable $th) {
            return response()->json(array(
                "success" => false,
                "message" => "Erro para encontrar categorias no BD",
                "erro" => $th->getMessage()
            ), 500);;
        }
    }
}
