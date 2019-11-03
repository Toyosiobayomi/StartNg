<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Courses;
use App\RegisteredCourses;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.dashboard');
        /*$tutors = User::where('role', '1')->paginate(5);
        return view('admin.tutors')->with('tutors', $tutors);*/
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_course(){
        $tutors = User::where('role', 1)->get();
        return view('admin.add-course')->with('tutors', $tutors);
    }

    public function store_course(Request $request){
        
        $data=request()->validate([
            'title'=>'required',
            'description'=>'required',
            'price'=>'required',
            'duration'=>'required',
            'tutor'=>'required'
        ]);

        $course = new Courses;
        $course->title = $request->input('title');
        $course->description = $request->input('description');
        $course->duration = $request->input('duration');
        $course->price = $request->input('price');     
        $course->user_id = $request->input('tutor');
        $course->save();

        return 'success';
        #return back()->with('success','Course Successfully Created');
    }

    public function create(){
        return view('admin.add-admin');
    }

    public function store(Request $request){
        
        $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:14', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => 'required'
        ]);
        
        $admin = new User;
        $admin->first_name = $request->input('first_name');
        $admin->last_name = $request->input('last_name');
        $admin->username = $request->input('username');
        $admin->email = $request->input('email');
        $admin->phone = $request->input('phone');
        $admin->password = Hash::make($request->input('password'));
        $admin->role = $request->input('role');
        $admin->save();
        
        #return redirect("route('index')");
        return back()->with('success','Admin Successfully Created');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $mentor = User::find($id);
        #return view('admin.mentor_detail')->with('mentor', $mentor);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = User::find($id);
        
        if (!isset($admin)){
            return back()->with('error', 'That admin is not registered');
        }

        if($admin->role == 0){
            return back()->with('error', 'Unauthorized to do that');
        }

        if($admin->role == 2){
            if(auth()->user()->id != $id){
                return back()->with('error', 'Unauthorized Permission');
            }
        }

        return view('admin.edit-admin')->with('admin', $admin);
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
        $this->validate($request, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:14'],
            'role' => 'required'
        ]);
        
        $admin = User::find($id);
        $admin->first_name = $request->input('first_name');
        $admin->last_name = $request->input('last_name');
        $admin->username = $request->input('username');
        $admin->email = $request->input('email');
        $admin->phone = $request->input('phone');
        $admin->role = $request->input('role');
        $admin->save();
        
        return 'success';
        #return back()->with('success','Admin Successfully Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $mentor = User::find($id);
        if (empty($mentor)) {
            Flash::error('Mentor not found');
            return redirect(route('mentors.index'));
        }

        $mentor->active = ($mentor->active == 0) ? 1 : 0;
        $title = ($mentor->active == 1) ? "enabled" : "disabled";


        $mentor->save();
//        Flash::success("User has been $title successfully.");
        return redirect(route('mentors'));
    }

    public function view_courses()
    {
        $data = array(
            'courses' => Courses::all(),
            'registered_courses' => RegisteredCourses::all(),
            'users' => User::all(),
        );
        return view('admin.view-courses')->with($data);
    }

    public function view_students()
    {
        $students = User::where('role', 0)->get();
        return view('admin.view-students')->with('students', $students);
    }

    public function view_user_detail($id)
    {
        $user = User::find($id);
        if ($user->role == 0){
            return view('admin.view-student-detail')->with('user', $user);
        }
        elseif ($user->role == 1){
            return view('admin.view-tutor-detail')->with('user', $user);
        }
    }

    public function view_tutors()
    {
        $students = User::where('role', 1)->get();
        return view('admin.view-tutors')->with('students', $students);
    }
}
