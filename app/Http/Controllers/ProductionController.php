<?php

namespace BZpoultryfarm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use BZpoultryfarm\Eggs;
use BZpoultryfarm\BrokenEggs;
use BZpoultryfarm\RejectEggs;
use BZpoultryfarm\InventoryChanges;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use BZpoultryfarm\User;
use BZpoultryfarm\Orders;
use BZpoultryfarm\OrderDetails;
use BZpoultryfarm\Products;
use BZpoultryfarm\Chickens;
use BZpoultryfarm\Cull;
use BZpoultryfarm\Customers;
use BZpoultryfarm\Feeds;
use BZpoultryfarm\DeadChickens;
use BZpoultryfarm\UsersArchives;
use BZpoultryfarm\Activity;
use PDF;
use Input;

class ProductionController extends Controller
{

	public function __construct()
	{
		$this->middleware('admin');
	}

    // Show

    public function show()
    {
        $eggs = Eggs::orderBy('id', 'desc')->first();

    	return view('admin.production', ['user' => Auth::user(), 'inv' => $eggs]);
    }

    // Load Chart Data

    public function prodStats()
    {
        $weekly = Eggs::orderBy('created_at', 'desc')->take(7)->get();

        return response()->json($weekly);
    } 


    //production PDF
    public function ProdReport(request $request)
    {
        $eggs = Eggs::where('created_at', 'like', '%'.Carbon::now()->toDateString().'%')->get();
        $broken = BrokenEggs::all();
        $reject = RejectEggs::where('created_at', 'like', '%'.Carbon::now()->toDateString().'%')->get();
        $returns = InventoryChanges::where('changed_at', 'like', '%'.Carbon::now()->toDateString().'%')->get()->where('remarks', '=', 'Returned Eggs.');
        $return = count($returns);
        $count = count($eggs);
        $countrjk = count($reject);


        view()->share('prodPDFeggs',$eggs);
        view()->share('prodPDFbroken',$broken);
        view()->share('prodPDFreject',$reject);
        view()->share('prodPDFcount',$count);
        view()->share('prodPDFcountrjk',$countrjk);
        view()->share('prodPDFreturn',$return);

        $pdf = PDF::loadView('admin/prodPDF'); 
        $pdf->setPaper('Legal', 'landscape');
        return $pdf->stream('prod/pdf.pdf');

        $prodPDFeggs=PDF::loadView('admin/prodPDF', compact('eggs'));
        return $prodPDFeggs->stream('prod/pdf.pdf');

        $prodPDFbroken=PDF::loadView('admin/prodPDF', compact('broken'));
        return $prodPDFbroken->stream('prod/pdf.pdf');

        $prodPDFreject=PDF::loadView('admin/prodPDF', compact('reject'));
        return $prodPDFreject->stream('prod/pdf.pdf'); 

        $prodPDFcount=PDF::loadView('admin/prodPDF', compact('count'));
        return $prodPDFcount->stream('prod/pdf.pdf');

        $prodPDFcountrjk=PDF::loadView('admin/prodPDF', compact('countrjk'));
        return $prodPDFcountrjk->stream('prod/pdf.pdf');

        $prodPDFreturn=PDF::loadView('admin/prodPDF', compact('return'));
        return $prodPDFreturn->stream('prod/pdf.pdf');




        // $data = Entry::find($id);
        // $pdf = PDF::loadView('posts.pdfview', compact('data'));
        // return $pdf->stream();
    }

    
    // // Generate Reports, highlight selection then press Ctrl+/ to remove/add comments

    // public function pdfview(Request $request, $id)
    // {
    //     $today = Carbon::now();
    //     $eggs = Eggs::where('created_at', '=', $today->toDateString())->get();

    //     view()->share('eggs', $eggs);

    //     $pdf=PDF::loadView('pdfview');
    //     return $pdf->stream('pdfview.pdf');
    // }

    

}
