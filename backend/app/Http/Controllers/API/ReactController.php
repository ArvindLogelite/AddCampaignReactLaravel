<?php

namespace App\Http\Controllers\API;

use App\Models\Images;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;


class ReactController extends Controller
{
    public function store(Request $request)
    {
        $validator = validator::make($request->all(), [
            'category' => 'required',
            'campaign' => 'required',
            'image1' => 'image', 'mimes:jpg,png,jpeg',
            'image2' => 'image', 'mimes:jpg,png,jpeg',
            'image3' => 'image', 'mimes:jpg,png,jpeg',
            'image4' => 'image', 'mimes:jpg,png,jpeg',
            'image5' => 'image', 'mimes:jpg,png,jpeg',
            'image6' => 'image', 'mimes:jpg,png,jpeg',
        ],[
            'image1.image' => 'Invalid! Image should be in jpg,png,jpeg',
            'image2.image' => 'Invalid! Image should be in jpg,png,jpeg',
            'image3.image' => 'Invalid! Image should be in jpg,png,jpeg',
            'image4.image' => 'Invalid! Image should be in jpg,png,jpeg',
            'image5.image' => 'Invalid! Image should be in jpg,png,jpeg',
            'image6.image' => 'Invalid! Image should be in jpg,png,jpeg',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages()
            ], 422);
        } else {

            $category = new Category;
            $image = new Images;
            $category->category = $request->category;
            $category->campaign = $request->campaign;

            $imageAll = ['image1', 'image2', 'image3', 'image4', 'image5', 'image6'];
            foreach ($imageAll as $index => $imageColumn) {
                if ($request->hasFile($imageColumn)) {
                    $img = $request->file($imageColumn);
                    $imgExt = $img->getClientOriginalExtension();
                    $imageName = time() . '_' . $index . '.' . $imgExt; // Add $index to ensure unique names
                    $img->move(public_path('images'), $imageName);
                    $image->$imageColumn = asset('images/' . $imageName); // Assign to correct column
                }
            }
            $category->save();
            $image->save();
            return response()->json([
                'status' => 200,
                'message' => 'Data inserted successfully'
            ], 200);
        }
    }


    public function destroy($id)
    {
        // Find the image record
        $image = Images::find($id);
        $category = Category::find($id);


        if (!$image || !$category) {
            return response()->json([
                'message' => 'Data not found',
                'status' => false
            ], 404);
        }

        // Delete the image record from the database
        $image->delete();
        $category->delete();

        foreach (range(1, 6) as $index) {
            $columnName = "image{$index}";
            $imageUrl = $image->$columnName;


            $filename = basename($imageUrl);
            $fullpath = public_path('images/' . $filename);

            if (file_exists($fullpath) && is_file($fullpath)) {
                unlink($fullpath);
            }
        }

        return response()->json([
            'message' => 'Campaign and its corespond Images deleted successfully',
            'status' => true
        ], 200);
    }



    public function updateStatus(Request $request, $id)
    {

        $request->validate([
            'status' => 'required',
        ]);

        $campaign = Category::findOrFail($id);
        $image = Images::findOrFail($id);

        $campaign->status = $request->status;
        $image->status = $request->status;
        $campaign->save();
        $image->save();

        return response()->json([
            'message' => 'Campaign status updated successfully',
            'status' => true
        ], 200);
    }


    public function showsingledata($id)
    {
        $campaign = Category::find($id);
        $images = Images::find($id);
        if (!$campaign && !$images) {
            return response()->json([
                'message' => 'No record Found',
                'status' => false
            ], 404);
        }
        return response()->json([
            'message' => 'Data successfully faound',
            'status' => true,
            'campaign' => $campaign,
            'image' => $images
        ], 200);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'category' => 'required',
            'campaign' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => $validator->messages()
            ], 422);
        } else {

            $category = Category::find($request->id);
            $image = Images::find($request->id);
            if (!$category || !$image) {
                return response()->json([
                    'message' => 'Data not found',
                    'status' => false
                ]);
            }
            $category->category = $request->category;
            $category->campaign = $request->campaign;

            $imageAll = ['image1', 'image2', 'image3', 'image4', 'image5', 'image6'];
            foreach ($imageAll as $index => $imageColumn) {
                if ($request->hasFile($imageColumn)) {

                    $validationRules = [
                        $imageColumn => 'image|mimes:jpeg,png|max:2048',
                    ];
                    $validator = Validator::make($request->only($imageColumn), $validationRules);
                    if ($validator->fails()) {
                        return response()->json([
                            'status' => 422,
                            'message' => $validator->messages()
                        ], 422);
                    }

                    $img = $request->file($imageColumn);
                    $imgExt = $img->getClientOriginalExtension();
                    $imageName = time() . '_' . $index . '.' . $imgExt;
                    $img->move(public_path('images'), $imageName);

                    // replace old image to new image
                    if ($image->$imageColumn) {
                        $basename = basename($image->$imageColumn);
                        File::delete('images/' . $basename);
                    }

                    $image->$imageColumn = asset('images/' . $imageName);
                } else {
                    if ($request->$imageColumn == null) {
                        $img = basename($image->$imageColumn);
                        File::delete('images/' . $img);
                        $image->$imageColumn = $request->$imageColumn;
                    }
                }
            }

            $category->save();
            $image->save();

            return response()->json([
                'status' => 200,
                'message' => 'Data updated successfully'
            ], 200);
        }
    }
}
