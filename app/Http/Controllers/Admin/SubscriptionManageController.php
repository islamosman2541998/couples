<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubscriptionManageController extends Controller
{
    public function index(Request $request)
    {
        $query = Subscription::with(['user', 'game'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('game')) {
            $query->where('game_id', $request->game);
        }

        $subscriptions = $query->paginate(20);

        $stats = [
            'total'    => Subscription::count(),
            'pending'  => Subscription::where('status', 'pending')->count(),
            'approved' => Subscription::where('status', 'approved')->count(),
            'rejected' => Subscription::where('status', 'rejected')->count(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'stats'));
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'game']);
        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function receipt(Subscription $subscription)
    {
        abort_unless(Storage::disk('local')->exists($subscription->receipt_image), 404);
        return Storage::disk('local')->response($subscription->receipt_image, null, ['Cache-Control' => 'private, no-store']);
    }

    public function approve(Request $request, Subscription $subscription)
    {
        $subscription->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);
        return back()->with('success', 'تم قبول الاشتراك بنجاح');
    }

    public function reject(Request $request, Subscription $subscription)
    {
        $subscription->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);
        return back()->with('success', 'تم رفض الاشتراك');
    }
}
