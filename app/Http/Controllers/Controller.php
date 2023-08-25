<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Carbon\Carbon;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use Intervention\Image\ImageManagerStatic as Image;

// use Image;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function custom_authorize($permission){
        if(!Auth::user()->hasPermission($permission)){
            abort(403, 'THIS ACTION IS UNAUTHORIZED.');
        }
    }
    
    public function save_image($file, $dir){
        try {
            Storage::makeDirectory($dir.'/'.date('F').date('Y'));
            $base_name = Str::random(20);

            // imagen normal
            $filename = $base_name.'.'.$file->getClientOriginalExtension();
            $image_resize = Image::make($file->getRealPath())->orientate();
            // return $image_resize;
            $image_resize->resize(1000, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $path =  "$dir/".date('F').date('Y').'/'.$filename;

            // return $path;
            $image_resize->save(public_path('../storage/app/public/'.$path));
            $imagen = $path;
            // return $imagen;

            // imagen mediana
            $filename_medium = $base_name.'-medium.'.$file->getClientOriginalExtension();
            $image_resize = Image::make($file->getRealPath())->orientate();
            $image_resize->resize(512, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $path_medium = "$dir/".date('F').date('Y').'/'.$filename_medium;
            $image_resize->save(public_path('../storage/app/public/'.$path_medium));

            // imagen pequeña
            $filename_small = $base_name.'-small.'.$file->getClientOriginalExtension();
            $image_resize = Image::make($file->getRealPath())->orientate();
            $image_resize->resize(256, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $path_small = "$dir/".date('F').date('Y').'/'.$filename_small;
            $image_resize->save(public_path('../storage/app/public/'.$path_small));

            // imagen cuadrada
            $filename_small = $base_name.'-cropped.'.$file->getClientOriginalExtension();
            $image_resize = Image::make($file->getRealPath())->orientate();
            $image_resize->resize(null, 256, function ($constraint) {
                $constraint->aspectRatio();
            });
            $image_resize->resizeCanvas(256, 256);
            $path_small = "$dir/".date('F').date('Y').'/'.$filename_small;
            $image_resize->save(public_path('../storage/app/public/'.$path_small));

            return $imagen;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
