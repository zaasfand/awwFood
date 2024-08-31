<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UploadedFood;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FoodController extends Controller
{
    /**
     * List food items (not accepted or accepted by the authenticated user).
     */
    public function index(Request $request)
    {
        $foods = UploadedFood::where(function ($query) use ($request) {
            $query->where('is_accepted', false)
                ->orWhere('accepted_by', $request->user()->id);
        })
        ->get()
        ->map(function ($food) {
            $food->image_url = url(Storage::url($food->image)); // Add full image URL
            return $food;
        });

        return response()->json($foods, 200);
    }


    /**
     * Store a new food item with image upload.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'food_items' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image file
            'description' => 'required|string',
            'location' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle the image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/food_images');
            $imageName = basename($imagePath);
        } else {
            return response()->json(['message' => 'Image not found'], 400);
        }

        $food = UploadedFood::create([
            'uploaded_by' => $request->user()->id,
            'food_items' => $request->food_items,
            'image' => $imagePath, // Store the full path in the database
            'description' => $request->description,
            'location' => $request->location,
        ]);

        return response()->json(['message' => 'Food item uploaded successfully', 'food' => $food], 201);
    }


    /**
     * Accept an order.
     */
    public function accept($id, Request $request)
    {
        $food = UploadedFood::find($id);

        if (!$food) {
            return response()->json(['message' => 'Food item not found'], 404);
        }

        if ($food->is_accepted && $food->accepted_by !== $request->user()->id) {
            return response()->json(['message' => 'Food item already accepted by another user'], 400);
        }

        $food->update([
            'is_accepted' => true,
            'accepted_by' => $request->user()->id,
        ]);

        return response()->json(['message' => 'Food item accepted successfully', 'food' => $food], 200);
    }
}
