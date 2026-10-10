<?php

namespace App\Http\Controllers;

use App\Agents\DocsAgent;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AskController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $question = $request->validate(['question' => ['required', 'string', 'max:500']])['question'];

        $matches = Document::search($question);

        if ($matches->isEmpty()) {
            return response()->json(['message' => 'Nothing similar enough in the ingested documents.'], 404);
        }

        return response()->json([
            'answer' => (new DocsAgent($matches))->prompt($question)->text,
            'sources' => $matches->map(fn (Document $match) => [
                'source' => $match->source,
                'distance' => round((float) $match->distance, 4),
            ])->values(),
        ]);
    }
}
