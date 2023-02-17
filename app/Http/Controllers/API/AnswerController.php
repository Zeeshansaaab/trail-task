<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Quiz;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\AnswerResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($quiz_id): JsonResponse
    {
        try{
            $answers = Answer::where('quiz_id', $quiz_id)->get();

            return response()->json([
                'success' => JsonResponse::HTTP_OK,
                'errors' => null,
                'data' => AnswerResource::collection($answers),
            ], JsonResponse::HTTP_OK);
        } catch(ModelNotFoundException $e){
            return response()->json([
                'success' => JsonResponse::HTTP_NOT_FOUND,
                'errors' => "Qustion not found",
                'data' => null,
            ], JsonResponse::HTTP_NOT_FOUND);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $quiz_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:answers,title,null,id,quiz_id,' . $quiz_id 
        ], [
            'title.unique' => 'Title is already taken against Question id: ' . $quiz_id
        ]);
        if($validator->fails()){
            return response()->json([
                'success'   => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                'errors'    => $validator->errors()->first(),
                'data'      => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        try{
            $quiz = Quiz::findOrFail($quiz_id);
            $answer = new Answer();
            $answer->title = $request->title;
            $answer->quiz_id = $quiz->id;
            $answer->save();

            return response()->json([
                'success'   => JsonResponse::HTTP_OK,
                'errors'    => null,
                'data'      => new AnswerResource($answer),
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
    public function update(Request $request, string $quiz_id, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => "required|string|unique:answers,title,$id,id,quiz_id,$quiz_id"
        ], [
            'title.unique' => 'Title is already taken against question id: ' . $quiz_id
        ]);
        if($validator->fails()){
            return response()->json([
                'success'   => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                'errors'    => $validator->errors()->first(),
                'data'      => null,
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        try{
            $quiz = Quiz::findOrFail($quiz_id);
            $answer = $quiz->answers()->findOrFail($id);
            $answer->title = $request->title;
            $answer->save();
            return response()->json([
                'success'   => JsonResponse::HTTP_OK,
                'errors'    => null,
                'data'      =>  new AnswerResource($answer),
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Answer $answer): JsonResponse
    {
        try{
            $answer->delete();
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

    public function rightAnswer(Request $request, $quiz_id): JsonResponse
    {
        try{
            $quiz = Quiz::with('answers')->findOrFail($quiz_id);
            $answer = $quiz->answers()->findOrFail($request->answer_id);
            $answer->update([
                'is_correct' => true
            ]);
            return response()->json([
                'success'   => JsonResponse::HTTP_OK,
                'errors'    => null,
                'data'      =>  new AnswerResource(Answer::find($request->answer_id)),
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

}
