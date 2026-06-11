<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityLogger
{
    public function log(string $action, ?Document $document, Request $request): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $request->user()?->id,
            'document_id' => $document?->id,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
        ]);
    }
}
