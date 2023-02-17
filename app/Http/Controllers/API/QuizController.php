<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Quiz;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $quizes = Quiz::when(request()->withAnswers, function($query){
            $query->with('answers');
        })->when(request()->status, function($query){
            $query->whereStatus(request()->status);
        })->get();
        return response()->json([
            'success' => JsonResponse::HTTP_OK,
            'errors' => null,
            'data' => QuizResource::collection($quizes),
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:quizzes',
            'description' => 'required|string|',
            'status'   => 'required',
        ]);
        if($validator->fails()){
            return response()->json([
                'success'   => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                'errors'    => $validator->errors()->first(),
                'data'      => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        try{
            $quiz = Quiz::create([
                'title'        => $request->title,
                'description'  => $request->description,
                'status'       => $request->status,
            ]);
            return response()->json([
                'success'   => JsonResponse::HTTP_OK,
                'errors'    => null,
                'data'      => (new QuizResource($quiz)),
            ], JsonResponse::HTTP_OK);
        } catch(Exception $e){
            return response()->json([
                'success' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'errors' => $e->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => "required|string|unique:quizzes,title,$id",
            'description' => 'required|string|',
        ]);
        if($validator->fails()){
            return response()->json([
                'success'   => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                'errors'    => $validator->errors()->first(),
                'data'      => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        try{
            $quiz = Quiz::findOrFail($id);
            $quiz->update([
                'title'        => $request->title,
                'description'  => $request->description,
                'status'       => $request->status ? $request->status : $quiz->status,
            ]);
            return response()->json([
                'success'   => JsonResponse::HTTP_OK,
                'errors'    => null,
                'data'      => (new QuizResource($quiz)),
            ], JsonResponse::HTTP_OK);
        } catch(Exception $e){
            return response()->json([
                'success' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'errors' => $e->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try{
            $quiz = Quiz::findOrFail($id)->delete();
            return response()->json([
                'success'   => JsonResponse::HTTP_OK,
                'errors'    => null,
                'data'      => null,
            ], JsonResponse::HTTP_OK);
        } catch(ModelNotFoundException $e){
            return response()->json([
                'success' => JsonResponse::HTTP_NOT_FOUND,
                'errors' => "Row does not exist",
                'data' => null,
            ], JsonResponse::HTTP_NOT_FOUND);
        } catch(Exception $e){
            return response()->json([
                'success' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'errors' => $e->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function mandatory(Request $request): JsonResponse
    {
        try{
            $quiz = Quiz::findOrFail($request->quiz_id);
            $quiz->update([
                'is_mandatory' => true
            ]);
            return response()->json([
                'success'   => JsonResponse::HTTP_OK,
                'errors'    => null,
                'data'      => (new QuizResource(Quiz::find($request->quiz_id))),
            ], JsonResponse::HTTP_OK);
        } catch(ModelNotFoundException $e){
            return response()->json([
                'success' => JsonResponse::HTTP_NOT_FOUND,
                'errors' => "Row does not exist",
                'data' => null,
            ], JsonResponse::HTTP_NOT_FOUND);
        } catch(Exception $e){
            return response()->json([
                'success' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'errors' => $e->getMessage(),
                'data' => null,
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function submit(Request $request){
        $correctAnswers = Answer::whereIn('id', $request->answers)->where('is_correct', true)->count();
        return response()->json([
            'success'   => JsonResponse::HTTP_OK,
            'errors'    => null,
            'data'      => [
                'correct_answers' => $correctAnswers
            ],
        ], JsonResponse::HTTP_OK);
    }
}
