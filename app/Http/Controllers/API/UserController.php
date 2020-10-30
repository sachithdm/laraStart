<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;
//use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return ['message' => 'I Have your data'];
        //return $request->all();
        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:6'
        ]);


        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'type' => $request['type'],
            'bio' => $request['bio'],
            'photo' => $request['photo'],
            'password' => Hash::make($request['password'])
        ]);
    }


    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        //return $request->photo;

        //Validations
        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|required|min:6'
        ]);
        

        //Get Current Profile Photo Name
        $currentPhoto = $user->photo;

        if($request->photo != $currentPhoto){
            //set unique name for the image and find the file extention
            $name = time().'.' . explode('/',explode(':',substr($request->photo,0,strpos($request->photo,';')))[1])[1];

            \Image::make($request->photo)->save(public_path('img/profile/').$name);

            //
            $request->merge(['photo' => $name]);
            
        }  
        //
        $user->update($request->all());
        return ['message' => "Success"];

    } 


    /**
     * Return Authrnticated User Information 
     */
    public function profile()
    {
        return auth('api')->user();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $this->validate($request,[
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:6'
        ]);
        //unique:users,email'.$user->id, UNIQUE WITHOUT CURRENT USER ID
        $user->update($request->all());
        return ['message' => 'update the user info'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        //delete the user
        $user->delete();
        return ['message' => 'User Deleted'];
    }
}
