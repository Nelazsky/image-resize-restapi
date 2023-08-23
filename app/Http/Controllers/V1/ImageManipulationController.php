<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResizeImageRequest;
use App\Http\Resources\V1\ImageManipulationResource;
use App\Models\Album;
use App\Models\ImageManipulation;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageManipulationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return ImageManipulationResource::collection(ImageManipulation::paginate(20));
    }

    /**
     * @param Album $album
     * @return void
     */
    public function byAlbum(Album $album)
    {

    }

    /**
     * @param ResizeImageRequest $request
     * @return Response
     */
    public function resize(ResizeImageRequest $request)
    {
        $all = $request->all();

        /** @var UploadedFile|string $image */
        $image = $all['image'];
        unset($all['image']);
        $data = [
            'type' => ImageManipulation::TYPE_RESIZE,
            'data' => json_encode($all),
            'user_id' => null
        ];

        if (isset($all['album_id'])) {
            // TODO make validation

            $data['album_id'] = $all['album_id'];
        }

        $dir = 'images/' . Str::random() . '/';
        $absolutPath = public_path($dir);
        File::makeDirectory($absolutPath);

        // public/images/dash123sad/image.jpg
        // public/images/dash123sad/image-resized.jpg
        if ($image instanceof UploadedFile) {
            $data['image'] = $image->getClientOriginalName();
            //image.jpg -> image-resized.jpg
            $filename = pathinfo($data['name'], PATHINFO_FILENAME);
            $extension = $image->getClientOriginalExtension();
            $originalPath = $absolutPath . $data['name'];

            $image->move($absolutPath, $data['name']);
        } else {
            $data['name'] = pathinfo($image, PATHINFO_BASENAME);
            $filename = pathinfo($image, PATHINFO_FILENAME);
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            $originalPath = $absolutPath . $data['name'];

            copy($image, $originalPath);
        }
        $data['path'] = $dir . $data['name'];

        $w = $all['w'];
        $h = $all['h'] ?? false;

        list($width, $height, $image) = $this->getImageWidthAndHeight($w, $h, $originalPath);

        $resizedFilename = $filename . '-resized.' . $extension;

        $image->resize($width, $height)->save($absolutPath . $resizedFilename);
        $data['output_path'] = $dir . $resizedFilename;

        $imageManipulation = ImageManipulation::create($data);

        return new ImageManipulationResource($imageManipulation);

    }

    protected function getImageWidthAndHeight($w, $h, string $originalPath)
    {
        $image = Image::make($originalPath);
        $originalW = $image->width();
        $originalH = $image->height();

        if (str_ends_with($w, '%')) {
            $ratioW = (float)str_replace('%', '', $w);
            $ratioH = $h ? (float)str_replace('%', '', $h) : $ratioW;

            $resizedW = $originalW * $ratioW / 100;
            $resizedH = $originalH * $ratioH / 100;
        } else {
            $resizedW = (float)$w;
            $resizedH = $h ? (float)$h : $originalH * $resizedW / $originalW;
        }

        return [$resizedW, $resizedH, $image];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ResizeImageRequest $request
     * @return Response
     */
    public function store(ResizeImageRequest $request)
    {
        $image = ImageManipulation::create($request->all());

        return new ImageManipulationResource($image);
    }

    /**
     * Display the specified resource.
     *
     * @param ImageManipulation $imageManipulation
     * @return Response
     */
    public function show(ImageManipulationController $imageManipulation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ImageManipulation $imageManipulation
     * @return Response
     */
    public function destroy(ImageManipulationController $imageManipulation)
    {

    }
}
