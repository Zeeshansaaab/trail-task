<?php

use App\Http\Controllers\API\AnswerController;
use App\Http\Controllers\API\QuizController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::apiResource('quizes', QuizController::class)->only(['index', 'store', 'destroy', 'update']);
Route::put('quiz/mandatory', [QuizController::class, 'mandatory']);
Route::post('quiz/submit', [QuizController::class, 'submit']);

Route::apiResource('quiz/{quiz_id}/answers', AnswerController::class)->only(['index', 'store', 'update']);
Route::delete('answers/{answer}', [AnswerController::class, 'destroy']);
Route::put('quiz/{quiz_id}/answer/right-answer', [AnswerController::class, 'rightAnswer']);
