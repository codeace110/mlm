<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class GenealogyController extends Controller
{
    public function index()
    {
        $genealogy = User::with('sponsor')->get()->map(function ($user) {
            $user->level = $this->calculateLevel($user);
            return $user;
        });
        return view('admin.genealogy.index', compact('genealogy'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('referral_code', 'LIKE', "%{$query}%")
            ->with('sponsor')
            ->paginate(20);

        return view('admin.genealogy.index', compact('users', 'query'));
    }

    public function network($userId)
    {
        $user = User::with(['sponsor', 'downlines'])->findOrFail($userId);
        $network = $this->buildNetworkTree($user);

        return view('admin.genealogy.network', compact('user', 'network'));
    }

    private function buildNetworkTree($user, $depth = 0, $maxDepth = 3)
    {
        if ($depth >= $maxDepth) {
            return null;
        }

        $tree = [
            'user' => $user,
            'level' => $depth,
            'children' => []
        ];

        foreach ($user->downlines as $downline) {
            $childTree = $this->buildNetworkTree($downline, $depth + 1, $maxDepth);
            if ($childTree) {
                $tree['children'][] = $childTree;
            }
        }

        return $tree;
    }

    private function calculateLevel($user, $level = 0)
    {
        if (!$user->sponsor) {
            return $level;
        }
        return $this->calculateLevel($user->sponsor, $level + 1);
    }
}