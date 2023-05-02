<?php

namespace BZpoultryfarm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Input;
use Illuminate\Validation\Rule;
use BZpoultryfarm\Sales;
use BZpoultryfarm\User;
use BZpoultryfarm\Orders;
use BZpoultryfarm\Chickens;
use BZpoultryfarm\Customers;
use BZpoultryfarm\Feeds;
use BZpoultryfarm\Eggs;
use BZpoultryfarm\Medicines;
use BZpoultryfarm\Supplies;
use BZpoultryfarm\DeadChickens;
use BZpoultryfarm\UsersArchives;
use BZpoultryfarm\Activity;
use BZpoultryfarm\Approvals;
use BZpoultryfarm\UserChanges;
use BZpoultryfarm\InventoryChanges;
use BZpoultryfarm\Vet;
use Validator;
use Response;
use Carbon\Carbon;

class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin');
    }

    // Logged in User Information

    public function details()
    {
        $allusers = User::all();
        $approvals = Approvals::where('status', '=', 'Pending')->get();
        $history = Approvals::where('status', '!=', 'Pending')->get();
        $vet = Vet::orderBy('id', 'desc')->get();

        return view('admin.admindetails', ['user' => Auth::user(), 'allusers' => $allusers, 'approvals' => $approvals, 'history' => $history, 'vet' => $vet]);
    }

    // Admin New Account Registration

    public function createNew()
    {
        return view('admin.admincreate')->with('user', Auth::user());
    }

    // Edit Own Information

   // public function editInfo()
   // {
    //    return view('admin.adminedit')->with('user', Auth::user());
    //}
    public function editInfo(Request $request, $id)
    {
        $user =  \DB::table('users')->where('id', $id)->first();
        return view('admin.adminedit')->with(['user'=> $user]);
    }
    

    // Insert Own Edit

    public function insertEdit($id)
    {
        $this->validate(request(), [
            'fname'         => 'required|regex:/^[\pL\s\-]+$/u|max:255',
            'lname'         => 'required|regex:/^[\pL\s\-]+$/u|max:255',
            'mobile'        => 'required|digits:11',
            'address'       => 'required|max:255',
        ]);

        $user = User::find($id);

        $user->fname = request('fname');
        $user->lname = request('lname');
        $user->mobile = request('mobile');
        $user->address = request('address');

        $user->update();
    //records user update to activiy log
        $changes = new UserChanges;

        $changes->activity = 'Updated Account';
        $changes->user_email = Auth::user()->email;
        $changes->remarks = 'Updated user account details';
        $changes->user = Auth::user()->email;
        $changes->changed_at = Carbon::now()->toDateTimeString();

        $changes->save();

        $act = new Activity;

        $act->user_id = Auth::user()->id;
        $act->email = Auth::user()->email;
        $act->module = 'Users';
        $act->activity = 'Updated user account';
        $act->ref_id = $changes->id;
        $act->date_time = Carbon::now();

        $act->save();

        $allusers = User::all();

        return redirect('/admin/details')->with(['user' => Auth::user(), 'allusers' => $allusers, 'success' => 'Successfully updated user details!']);
    }

    // Delete A User

    public function deleteUser($id)
    {
        $del = User::find($id);

        $archive = new UsersArchives;

        $archive->user_id = $del->id;
        $archive->lname = $del->lname;
        $archive->fname = $del->fname;
        $archive->email = $del->email;
        $archive->password = str_random(10);
        $archive->mobile = $del->mobile;
        $archive->address = $del->address;
        $archive->access = $del->access;
        $archive->token = $del->token;
        $archive->remember_token = $del->remember_token;
        $archive->last_login = $del->last_login;
        $archive->user_created = $del->created_at;
        $archive->disabled_by = Auth::user()->email;
        $archive->status = "Disabled";

        $archive->save();
        //save user account to activiy log
        $changes = new UserChanges;

        $changes->activity = 'Disabled Account';
        $changes->user_email = $del->email;
        $changes->remarks = 'Disabled Account of ' . $del->fname . ' ' . $del->lname;
        $changes->user = Auth::user()->email;
        $changes->changed_at = Carbon::now()->toDateTimeString();

        $changes->save();

        $act = new Activity;

        $act->user_id = Auth::user()->id;
        $act->email = Auth::user()->email;
        $act->module = 'Users';
        $act->activity = 'Disabled Account: ' . $del->email;
        $act->ref_id = $changes->id;
        $act->date_time = Carbon::now();

        $act->save();

        $del->delete();


        return response()->json($del);
    }
//displays archived users
    public function archivesUser()
    {
        $arc = UsersArchives::all();

        return view('admin.userarchives', ['arc' => $arc, 'user' => Auth::user()]);
    }
//change pass request
    public function changePass(Request $request, $id)
    {
        $validator = Validator::make(Input::all(), ['password' => 'required|string|min:4']);
        if ($validator->fails()) {
            return Response::json(array('errors' => $validator->getMessageBag()->toArray()));
        } else {

            $user = User::find($id);

            $user->password = bcrypt($request->password);

            $user->update();
            //save records to activity log
            $changes = new UserChanges;

            $changes->activity = 'Change Password';
            $changes->user_email = $user->email;
            $changes->remarks = 'Changed own password';
            $changes->user = Auth::user()->email;
            $changes->changed_at = Carbon::now()->toDateTimeString();

            $changes->save();

            $act = new Activity;

            $act->user_id = Auth::user()->id;
            $act->email = Auth::user()->email;
            $act->module = 'Users';
            $act->activity = 'Changed own password';
            $act->ref_id = $changes->id;
            $act->date_time = Carbon::now();

            $act->save();

            return response()->json($user);
        }

    }

	//Admin Dashboard

    public function index()
    {

        $today = Carbon::now();
        $lastWk = Carbon::now()->subWeek();
        $lastMt = Carbon::now()->subMonth();
        $newcust = Customers::whereBetween('created_at', [$lastWk->toDateTimeString(), $today->toDateTimeString()])->count('id');
        $orders = Orders::where('trans_date', 'like', Carbon::now()->toDateString() . '%')->where('status', '=', 'Successful')->take(5)->get();
        
        $dead = DeadChickens::whereBetween('created_at', [$lastWk->toDateTimeString(), $today->toDateTimeString()])->sum('quantity'); 
        
        $feeds = Feeds::sum('quantity');
        $reorder = Feeds::sum('reorder_level');
        $act = Activity::orderBy('date_time', 'desc')->take(5)->get();
        $sales = Sales::where('trans_date', '=', Carbon::now()->toDateString())->sum('total_cost');
        $totalsales = Sales::whereBetween('trans_date', [$lastMt->toDateTimeString(), $today->toDateTimeString()])->sum('total_cost');

        $meds = Medicines::all();
        $medstotal= Medicines::sum('quantity');

        $suppliescost = Supplies::sum(\DB::raw('price * quantity'));
        $feedscost = Feeds::sum(\DB::raw('price * quantity'));
        $medicinecost = Medicines::sum(\DB::raw('price * quantity'));
        $totalcost = $suppliescost + $feedscost + $medicinecost;


        //Transaction::sum(\DB::raw('jumla * harga'));
        //Supplies::sum(\DB::raw('price * quantity'));
        // $eggsales = \DB::table('sales')
        //         ->join('order_details', 'trans_id', '=', 'order_id')
        //         ->select('order_details.*', 'sales.*')
        //         ->where('product_name', '!=', 'Manure')
        //         ->orwhere('product_name', '!=', 'Cull')
        //         ->orwhere('product_name', '!=', 'Sacks')
        //         ->get();
        //SELECT name,price,quantity, price * quantity as cost FROM supplies;


        $chickens = InventoryChanges::where('type', '=', 'Chickens')->get();
        $chickenstotal= Chickens::sum('quantity');
        $vet = Vet::orderBy('id', 'desc')->first();


    	return view('admin.index')->with(['user' => Auth::user(), 'date' => Carbon::now()->format('l, j F Y'), 'orders' => $orders
        , 'dead' => $dead, 'newcust' => $newcust, 'totalcost'=> $totalcost ,'feeds' => $feeds, 'chickenstotal'=> $chickenstotal, 'medstotal'=>$medstotal,'totalsales'=> $totalsales, 'reorder' => $reorder, 'act' => $act, 'sales' => $sales, 'meds' => $meds
        , 'chickens' => $chickens, 'prescription' => $vet->prescription, 'diagnosis' => $vet->diagnosis, 'acknowledge' => $vet->acknowledge]);
    }

}
