<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas as LogModel;
use Illuminate\Http\Request;

class LogAktivitas extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $logs = LogModel::with('user')
            ->when($search, function ($query, $search) {
                return $query->where('aktivitas', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.log_aktivitas.index', compact('logs', 'search'));
    }
}