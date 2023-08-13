<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{

    public function index()
    {
        $news = News::all();
        return response()->json($news, 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {

        $validasi = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|string'
        ]);


        if ($validasi->fails()) {
            return response()->json($validasi->errors());
        }


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('image'), $imageName);

            $news = new News();


            $news->title = $request->input('title');
            $news->image = $imageName;
            $news->description = $request->input('description');
            $news->save();
            activity()
                ->performedOn($news)
                ->causedBy(auth()->user())
                ->withProperties([
                    'title' => $news->title,
                    'description' => $news->description,
                    'image' =>$news->image
                ])
                ->log('News created');

            return response()->json([$news, 'message' => 'News created successfully'], 201);
        }


        return response()->json(['message' => 'News created failed'], 400);
    }



    public function show(string $id)
    {
        $news = News::find($id);

        return response()->json($news);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, $id)
    {



        $validasi = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string'
        ]);

        if ($validasi->fails()) {
            return response()->json($validasi->errors());
        }

        $news = News::findOrFail($id);
        $oldTitle = $news->title;
        $oldDescription = $news->description;

        $news->title = $request->input('title');
        $news->description = $request->input('description');
        // dd($news);

        if ($request->hasFile('image')) {
            $imagePath = public_path('image') . '/' . $news->image;
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('image'), $imageName);
            $news->image = $imageName;
        }

        $news->save();
        activity()
            ->performedOn($news)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_title' => $oldTitle,
                'old_description' => $oldDescription,
            ])
            ->log('News updated');

        return response()->json(['message' => 'News updated successfully'], 200);
    }

    public function destroy(string $id)
    {
        $news = News::findOrFail($id);
        $newsTitle = $news->title;
        $newsDescription = $news->description;

        $imagePath = public_path('image') . '/' . $news->image;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        $news->delete();

        activity()
            ->performedOn($news)
            ->causedBy(auth()->user())
            ->withProperties([
                'deleted_title' => $newsTitle,
                'deleted_description' => $newsDescription,
            ])
            ->log('News deleted');

        return response()->json('Delete success', 200);
    }


    // logs
    public function getActivityLog(string $id)
{
    $news = News::findOrFail($id);

    $activityLog = Activity::inLog('default')
        ->where('subject_type', News::class)
        ->where('subject_id', $news->id)
        ->get();

    $activityLogData = [];

    foreach ($activityLog as $log) {
        $user = $log->causer;
        $usernameOrEmail = $user ? $user->username ?? $user->email : 'Anonymous';

        $logData = [
            'description' => $log->description,
            'username_or_email' => $usernameOrEmail,
            'properties' => $log->properties->all(),
            'created_at' => $log->created_at,
        ];

        $activityLogData[] = $logData;
    }

    return response()->json([
        'data' => $activityLogData,
    ]);
}

}
