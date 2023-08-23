<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Http\Resources\V1\AlbumResource;
use App\Models\Album;
use Illuminate\Http\Response;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return AlbumResource::collection(Album::paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAlbumRequest $request
     * @return Response
     */
    public function store(StoreAlbumRequest $request)
    {
        $album = Album::create($request->all());

        return new AlbumResource($album);
    }

    /**
     * Display the specified resource.
     *
     * @param Album $album
     * @return Response
     */
    public function show(Album $album)
    {
        return new AlbumResource($album);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAlbumRequest $request
     * @param Album $album
     * @return Response
     */
    public function update(UpdateAlbumRequest $request, Album $album)
    {
        $album->update($request->all());

        return $album;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Album $album
     * @return Response
     */
    public function destroy(Album $album)
    {
        $album->delete();

        return response('', 204);
    }
}
