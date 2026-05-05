<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DuesTypeModel;
use Illuminate\Support\Facades\Validator;

class DuesTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $duesTypes = DuesTypeModel::all();
        return response()->json([
            'message' => 'Success retrieve all dues types',
            'data' => $duesTypes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dues_type_name' => 'required|string|max:255',
            'dues_type_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $duesType = DuesTypeModel::create($request->all());

        return response()->json([
            'message' => 'Dues type created successfully',
            'data' => $duesType
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DuesTypeModel $duesType)
    {
        return response()->json([
            'message' => 'Success retrieve dues type detail',
            'data' => $duesType
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DuesTypeModel $duesType)
    {
        $validator = Validator::make($request->all(), [
            'dues_type_name' => 'sometimes|required|string|max:255',
            'dues_type_amount' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $duesType->update($request->all());

        return response()->json([
            'message' => 'Dues type updated successfully',
            'data' => $duesType
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DuesTypeModel $duesType)
    {
        $duesType->delete();

        return response()->json([
            'message' => 'Dues type deleted successfully'
        ]);
    }
}
