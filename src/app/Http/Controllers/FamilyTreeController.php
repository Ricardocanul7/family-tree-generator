<?php

namespace App\Http\Controllers;

use App\Models\Person;

class FamilyTreeController extends Controller
{
    public function index()
    {
        $people = Person::with('parents')->withCount('children')->get();
        $rootPeople = $people->filter(fn ($p) => $p->parents->isEmpty());

        if ($rootPeople->isEmpty()) {
            $rootPeople = $people;
        }

        return view('family-tree.index', [
            'people' => $people,
            'rootPeople' => $rootPeople,
        ]);
    }

    public function show(Person $person)
    {
        return view('family-tree.tree', [
            'rootPerson' => $person,
        ]);
    }

    public function treeData(Person $person)
    {
        $tree = $this->buildTree($person);
        return response()->json($tree);
    }

    private function buildTree(Person $person, int $depth = 0, int $maxDepth = 10): ?array
    {
        if ($depth > $maxDepth) {
            return null;
        }

        $children = $person->children()
            ->withCount('children')
            ->get()
            ->sortBy('birth_date');

        $childNodes = [];
        foreach ($children as $child) {
            $childNode = $this->buildTree($child, $depth + 1, $maxDepth);
            if ($childNode !== null) {
                $childNodes[] = $childNode;
            }
        }

        return [
            'id' => $person->id,
            'name' => $person->full_name,
            'first_name' => $person->first_name,
            'last_name' => $person->last_name,
            'photo' => $person->photo_url,
            'birth_date' => $person->birth_date?->format('d/m/Y'),
            'death_date' => $person->death_date?->format('d/m/Y'),
            'gender' => $person->gender,
            'biography' => $person->biography,
            'children_count' => $person->children_count,
            'children' => $childNodes,
        ];
    }

    public function fullTree()
    {
        $roots = Person::whereDoesntHave('parents')->withCount('children')->get();

        if ($roots->isEmpty()) {
            $firstPerson = Person::withCount('children')->first();
            if (!$firstPerson) {
                return response()->json(null);
            }
            $tree = $this->buildTree($firstPerson);
            return response()->json($tree);
        }

        $trees = [];
        foreach ($roots as $root) {
            $tree = $this->buildTree($root);
            if ($tree) {
                $trees[] = $tree;
            }
        }

        if (count($trees) === 1) {
            return response()->json($trees[0]);
        }

        return response()->json([
            'id' => 0,
            'name' => __('Families'),
            'children' => $trees,
        ]);
    }
}
