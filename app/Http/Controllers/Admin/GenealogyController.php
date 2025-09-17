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
            $binaryTree = \App\Models\BinaryTree::where('user_id', $user->id)->first();
            $user->total_left_volume = $binaryTree ? $binaryTree->total_left_volume : 0;
            $user->total_right_volume = $binaryTree ? $binaryTree->total_right_volume : 0;
            $user->left_consumed = $binaryTree ? $binaryTree->left_consumed : 0;
            $user->right_consumed = $binaryTree ? $binaryTree->right_consumed : 0;
            $user->level_index = $binaryTree ? $binaryTree->level_index : 1;
            $user->effective_left = $binaryTree ? ($binaryTree->total_left_volume - $binaryTree->left_consumed) : 0;
            $user->effective_right = $binaryTree ? ($binaryTree->total_right_volume - $binaryTree->right_consumed) : 0;
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
            ->paginate(20)
            ->through(function ($user) {
                $binaryTree = \App\Models\BinaryTree::where('user_id', $user->id)->first();
                $user->total_left_volume = $binaryTree ? $binaryTree->total_left_volume : 0;
                $user->total_right_volume = $binaryTree ? $binaryTree->total_right_volume : 0;
                $user->left_consumed = $binaryTree ? $binaryTree->left_consumed : 0;
                $user->right_consumed = $binaryTree ? $binaryTree->right_consumed : 0;
                $user->level_index = $binaryTree ? $binaryTree->level_index : 1;
                $user->effective_left = $binaryTree ? ($binaryTree->total_left_volume - $binaryTree->left_consumed) : 0;
                $user->effective_right = $binaryTree ? ($binaryTree->total_right_volume - $binaryTree->right_consumed) : 0;
                return $user;
            });

        return view('admin.genealogy.index', compact('users', 'query'));
    }

    public function network($userId)
    {
        $user = User::findOrFail($userId);
        $binaryTree = \App\Models\BinaryTree::where('user_id', $userId)->first();
        $treeData = $this->buildD3Tree($binaryTree, $user);

        return view('admin.genealogy.network', compact('user', 'treeData'));
    }

    private function buildD3Tree($binaryTree, $user, $depth = 0, $maxDepth = 5)
    {
        if ($depth >= $maxDepth || !$binaryTree) {
            return null;
        }

        $node = [
            'name' => $user->name,
            'id' => $user->id,
            'level' => $depth + 1,
            'total_left_volume' => $binaryTree->total_left_volume ?? 0,
            'total_right_volume' => $binaryTree->total_right_volume ?? 0,
            'left_consumed' => $binaryTree->left_consumed ?? 0,
            'right_consumed' => $binaryTree->right_consumed ?? 0,
            'effective_left' => ($binaryTree->total_left_volume ?? 0) - ($binaryTree->left_consumed ?? 0),
            'effective_right' => ($binaryTree->total_right_volume ?? 0) - ($binaryTree->right_consumed ?? 0),
            'level_index' => $binaryTree->level_index ?? 1,
            'profile_image' => $user->profile_image,
            'children' => []
        ];

        if ($binaryTree->left_child_id) {
            $leftUser = User::find($binaryTree->left_child_id);
            if ($leftUser) {
                $leftBinaryTree = \App\Models\BinaryTree::where('user_id', $binaryTree->left_child_id)->first();
                $leftChild = $this->buildD3Tree($leftBinaryTree, $leftUser, $depth + 1, $maxDepth);
                if ($leftChild) {
                    $node['children'][] = $leftChild;
                }
            }
        }

        if ($binaryTree->right_child_id) {
            $rightUser = User::find($binaryTree->right_child_id);
            if ($rightUser) {
                $rightBinaryTree = \App\Models\BinaryTree::where('user_id', $binaryTree->right_child_id)->first();
                $rightChild = $this->buildD3Tree($rightBinaryTree, $rightUser, $depth + 1, $maxDepth);
                if ($rightChild) {
                    $node['children'][] = $rightChild;
                }
            }
        }

        return $node;
    }

    private function calculateLevel($user, $level = 1)
    {
        if (!$user->sponsor) {
            return $level;
        }
        return $this->calculateLevel($user->sponsor, $level + 1);
    }
}