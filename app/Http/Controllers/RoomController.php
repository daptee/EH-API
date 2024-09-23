<?php

namespace App\Http\Controllers;

use App\Models\RoomImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{

    public $model = RoomImage::class;
    public $s = "imagen";
    public $sp = "imagenes";
    public $ss = "imagen/es";
    public $v = "a"; 
    public $pr = "la"; 
    public $prp = "las";

    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required',
            'images' => 'required|array',
            'images.*.image' => [
                'required',
                'file',
                'max: 2000',
                function ($attribute, $value, $fail) {
                    $imageInfo = getimagesize($value);
                    if ($imageInfo) {
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];

                        if ($width <= $height) {
                            $fail("La imagen {$attribute} debe ser de formato horizontal.");
                        }

                        if ($width > 1600) {
                            $fail("La imagen {$attribute} no debe superar los 1600 píxeles de ancho.");
                        }
                    } else {
                        $fail("El archivo {$attribute} debe ser una imagen válida.");
                    }
                }
            ],
            'images.*.principal' => 'required|boolean',
        ], [
            'images.*.image.max' => "Cada imagen debe ser menor a 2 MB.",
        ]);

        try {
            foreach ($request->images as $image) {
                $response_save_image = $this->save_image_public_folder($image['image'], "rooms/$request->room_number/images/");
                if($response_save_image['status'] == 200){
                    $room_images = new $this->model();
                    $room_images->room_number = $request->room_number;
                    $room_images->url = $response_save_image['path'];
                    $room_images->principal_image = $image['principal'];
                    $room_images->save();
                }else{
                    Log::debug(["error" => "Error al guardar imagen", "message" => $response_save_image['message'], "room_number" => $request->room_number]);
                }
            }
        } catch (Exception $error) {
            Log::debug("Error al guardar imagenes: " . $error->getMessage() . ' line: ' . $error->getLine());
            return response(["message" => "Error al guardar imagenes", "error" => $error->getMessage()], 500);
        }
       
        return response()->json(['message' => 'Imagenes de habitacion guardadas exitosamente.'], 200);
    }

    public function save_image_public_folder($file, $path_to_save)
    {
        try {
            $fileName = Str::random(5) . time() . '.' . $file->extension();
            $file->move(public_path($path_to_save), $fileName);
            $path = "/" . $path_to_save . $fileName;
            return ["status" => 200, "path" => $path];
        } catch (Exception $error) {
            return ["status" => 500, "message" => $error->getMessage()];
        }
    }

    public function room_images($room_number)
    {
        $room_images = $this->model::where('room_number', $room_number)->get();

        return response()->json(['room_images' => $room_images], 200);
    }

    public function all_images_rooms()
    {
        $rooms_images = $this->model::get();

        return response()->json(['rooms_images' => $rooms_images], 200);
    }    

    public function room_images_principal()
    {
        $room_images = $this->model::where('principal_image', 1)->get();

        return response()->json(['room_images' => $room_images], 200);
    }

    public function room_images_delete($image_id)
    {
        $room_image = $this->model::find($image_id);
        
        if(!$room_image)
            return response()->json(['message' => 'ID image invalido.'], 400);
        
        $room_image->delete();
    
        return response()->json(['message' => 'Imagen eliminada con exito.'], 200);
    }
}
