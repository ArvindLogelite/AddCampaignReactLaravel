<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Images;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ReactController extends Controller
{
    public function store(Request $request)
    {
        $validator = validator::make($request->all(), [
            'category' => 'required',
            'campaign' => 'required',
            'image1' => 'image', 'mimes:jpg,png,jpeg,gif,svg',
            'image2' => 'image', 'mimes:jpg,png,jpeg,gif,svg',
            'image3' => 'image', 'mimes:jpg,png,jpeg,gif,svg',
            'image4' => 'image', 'mimes:jpg,png,jpeg,gif,svg',
            'image5' => 'image', 'mimes:jpg,png,jpeg,gif,svg',
            'image6' => 'image', 'mimes:jpg,png,jpeg,gif,svg',
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

        // Iterate over image columns
        foreach (range(1, 6) as $index) {
            $columnName = "image{$index}";
            $imageUrl = $image->$columnName;

            // Extract the filename from the URL
            $filename = basename($imageUrl);

            // Construct the full file path
            $fullPath = public_path('images/' . $filename);

            // Check if file exists and delete
            if (file_exists($fullPath) && is_file($fullPath)) {
                unlink($fullPath);
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

        $campaign->status = $request->status;
        $campaign->save();

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
        // dd($request->category);
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'category' => 'required',
            'campaign' => 'required',
            'image1' => 'mimes:jpg,png,jpeg,gif,svg',
            // 'image2' => 'image','mimes:jpg,png,jpeg,gif,svg',
            // 'image3' => 'mimes:jpg,png,jpeg,gif,svg',
            // 'image4' => 'mimes:jpg,png,jpeg,gif,svg',
            // 'image5' => 'mimes:jpg,png,jpeg,gif,svg',
            // 'image6' => 'mimes:jpg,png,jpeg,gif,svg',
        ]);

        // Check if validation fails
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
                    $img = $request->file($imageColumn);
                    $imgExt = $img->getClientOriginalExtension();
                    $imageName = time() . '_' . $index . '.' . $imgExt; // Add $index to ensure unique names
                    $img->move(public_path('images'), $imageName);
                    $image->$imageColumn = asset('images/' . $imageName); // Assign to correct column
                } else {
                    $image->$imageColumn = $request->$imageColumn;
                }
            }
            $category->save();
            $image->save();
            // Return success response
            return response()->json([
                'status' => 200,
                'message' => 'Data updated successfully'
            ], 200);
        }
    }
}
