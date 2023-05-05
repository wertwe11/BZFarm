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

    public function show(Request $request)
    {
        $from = $request->input('from') ?? Carbon::now()->toDateString();
        $to = $request->input('to') ?? Carbon::now()->toDateString();

        $from_time = $from . ' 00:00:00';
        $to_time = $to . ' 23:59:00';

        $eggs = Eggs::whereBetween('created_at', [$from_time, $to_time])->orderBy('id', 'desc')->first();

    	return view('admin.production', ['user' => Auth::user(), 'inv' => $eggs, 'from'=>$from, 'to'=>$to]);
    }

    // Load Chart Data

    public function prodStats(Request $request)
    {
        $from = $request->input('from') ?? Carbon::now()->toDateString();
        $to = $request->input('to') ?? Carbon::now()->toDateString();

        $from_time = $from . ' 00:00:00';
        $to_time = $to . ' 23:59:00';

        $weekly = Eggs::whereBetween('created_at', [$from_time, $to_time])->orderBy('created_at', 'desc')->take(7)->get();

        return response()->json($weekly);
    }

    public function feedConsumption(Request $request)
    {
        $from = $request->input('from') ?? Carbon::now()->toDateString();
        $to = $request->input('to') ?? Carbon::now()->toDateString();
        
        $from_time = $from . ' 00:00:00';
        $to_time = $to . ' 23:59:00';

        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $data = DB::table('total_chickens as c')
        ->join(DB::raw("(SELECT DATE(changed_at) as feed_date, sum(quantity) as consumption FROM inventory_changes where type='Feeds' and unittype='grams' GROUP BY feed_date) AS o"), function ($join) {
            $join->on(DB::raw('DATE(c.created_at)'), '=', 'o.feed_date');
        })->join(DB::raw("(SELECT DATE(updated_at) as feed_stock_date, sum(quantity) as stock_left, updated_at FROM feeds where unit='grams' GROUP BY feed_stock_date) AS n"), function ($join) {
            $join->on(DB::raw('DATE(c.created_at)'), '=', 'n.feed_stock_date');
        })->select(DB::raw('sum(c.quantity) as current_chicken'), 'o.*', 'n.*')
        ->whereBetween('n.updated_at', [$from_time, $to_time])
        ->groupBy('n.updated_at')->get();

        return response()->json($data);
    } 

    public function prodPerf(Request $request)
    {
        $from = $request->input('from') ?? Carbon::now()->toDateString();
        $to = $request->input('to') ?? Carbon::now()->toDateString();

        $from_time = $from . ' 00:00:00';
        $to_time = $to . ' 23:59:00';

        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $data = DB::table('total_chickens as c')
        ->join(DB::raw("(SELECT created_at as egg_date, sum(jumbo + xlarge + large + medium + small + peewee + softshell) as total_eggs FROM eggs GROUP BY egg_date) AS o"), function ($join) {
            $join->on(DB::raw('DATE(c.created_at)'), '=', 'o.egg_date');
        })->join(DB::raw("(SELECT DATE(created_at) as chicken_dead_date, sum(quantity) as total_dead_chickens FROM dead_chickens GROUP BY chicken_dead_date) AS n"), function ($join) {
            $join->on(DB::raw('DATE(c.created_at)'), '=', 'n.chicken_dead_date');
        })->join(DB::raw("(SELECT DATE(created_at) as pullet_date, sum(quantity) as current_pullets FROM total_pullets GROUP BY pullet_date) AS r"), function ($join) {
            $join->on(DB::raw('DATE(c.created_at)'), '=', 'r.pullet_date');
        })->join(DB::raw("(SELECT DATE(created_at) as pullet_dead_date, sum(quantity) as total_dead_pullets FROM dead_pullets GROUP BY pullet_dead_date) AS i"), function ($join) {
            $join->on(DB::raw('DATE(c.created_at)'), '=', 'i.pullet_dead_date');
        })->select(DB::raw('sum(c.quantity) as current_chicken'), 'o.*', 'n.*', 'r.*', 'i.*')
        ->whereBetween('c.created_at', [$from_time, $to_time])
        ->groupBy(DB::raw('DATE(c.created_at)'))->get();

        return response()->json($data);
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
