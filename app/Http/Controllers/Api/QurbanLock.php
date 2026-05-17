<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Services\QurbanService;
use Illuminate\Http\Request;

class QurbanLock extends Controller
{
    public function __construct(private QurbanService $service) {}

    public function status(string $slug)
    {
        $project = $this->service->get($slug);

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        return response()->json([
            'locked'      => $this->service->isLocked($project),
            'days_left'   => $this->service->daysLeft($project),
            'is_paid'     => $project['is_paid'],
            'client_name' => $project['client_name'],
        ]);
    }

    public function unlock(Request $request, string $slug)
    {
        $request->validate(['token' => 'required|string']);

        if ($request->token !== config('app.admin_unlock_token')) {
            return response()->json(['success' => false, 'message' => 'Token tidak valid'], 403);
        }

        $project = $this->service->get($slug);

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        $project['is_paid'] = true;
        $project['paid_at'] = now()->toISOString();

        $this->service->save($slug, $project);

        return response()->json([
            'success' => true,
            'message' => "{$project['client_name']} berhasil di-unlock",
        ]);
    }
}