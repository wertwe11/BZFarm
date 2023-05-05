<?php

namespace BZpoultryfarm\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use BZpoultryfarm\User;
use BZpoultryfarm\Sales;
use BZpoultryfarm\Orders;
use BZpoultryfarm\OrderDetails;
use BZpoultryfarm\Products;
use BZpoultryfarm\TotalChickens;
use BZpoultryfarm\Cull;
use BZpoultryfarm\Customers;
use BZpoultryfarm\Feeds;
use BZpoultryfarm\DeadChickens;
use BZpoultryfarm\UsersArchives;
use BZpoultryfarm\Activity;
use BZpoultryfarm\SoldEggs;
use Carbon\Carbon;
use PDF;
use DB;

class SalesController extends Controller
{

	public function __construct()
	{
		$this->middleware('admin');
	}

    // Sales History


    public function history()
    {
        //$sales = Sales::orderBy('id', 'desc')->paginate(10);
        
        $sales = \DB::table('sales')
                    ->leftJoin('customers', 'email', '=', 'cust_email')
                     ->select('sales.*', 'customers.*')
                     ->orderBy('trans_id', 'desc')
                     ->paginate(10);
        
        $soldeggs = SoldEggs::paginate(5);
        return view('admin.allsales')->with(['sales' => $sales, 'soldeggs' => $soldeggs, 'user' => Auth::user()]);
       
    }
    //show searched record
    public function search(Request $request)
    {
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $sales = Sales::whereBetween('trans_date', [$fromDate, $toDate])->paginate(10);
        $soldeggs = SoldEggs::all();

        return view('admin.allsales')->with(['sales' => $sales, 'soldeggs' => $soldeggs, 'user' => Auth::user()]);
        
    }

    public function salesChart(Request $request)
    {
        $from = $request->input('from') ?? Carbon::now()->toDateString();
        $to = $request->input('to') ?? Carbon::now()->toDateString();
        
        $from_time = $from . ' 00:00:00';
        $to_time = $to . ' 23:59:00';

        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        $data = DB::table('sales as s')
        ->leftJoin(DB::raw("(SELECT DATE(changed_at) as consume_date, sum(quantity * unit) as consumption FROM inventory_changes where activity='Used quantity' GROUP BY consume_date) AS o"), function ($join) {
            $join->on('s.trans_date', '=', 'o.consume_date');
        })->select(DB::raw('sum(s.total_cost) as total_sales'), 's.trans_date', 'o.*')
        ->whereBetween('s.trans_date', [$from, $to])
        ->groupBy('s.trans_date')->get();

        return response()->json($data);
    }


    // Show

    public function show(Request $request)
    {
        $from = $request->input('from') ?? Carbon::now()->subDay(1)->toDateString();
        $to = $request->input('to') ?? Carbon::now()->toDateString();

    	return view('admin.sales', ['from'=>$from, 'to'=>$to])->with('user', Auth::user());
    }

 public function salesStats(Request $request)
    {
        // $yesterday = Carbon::now()->subDay(1);
        // $yesterday = Sales::where('trans_id', 'like', $yesterday->format('Ymd') . '%')->sum('total_cost');
        $from = $request->input('from') ?? Carbon::now()->subDay(1)->toDateString();
        $to = $request->input('to') ?? Carbon::now()->toDateString();

        $today = Sales::whereBetween('trans_date', [$from, $to])->selectRaw('sum(total_cost) as total_cost, trans_date')->groupBy('trans_date')->get();

        return response()->json(['data'=>$today]);
    }

    //Generate Report
    //report 1
    public function SalesReport(request $request)
    {
        $today = Carbon::today()->toDateString();
        $orders = Sales::where('trans_date', 'like', '%'.Carbon::now()->toDateString().'%')->get();
        $salesorder = Sales::all();
        $orderdetails=OrderDetails::where('created_at', 'like', '%'.Carbon::now()->toDateString().'%')->get(); 
        $customers=Customers::all();
        $products=Products::all();
        $maxOrder=count($orders);
       ////////////////
        $name = \DB::table('customers')
                        ->join('sales', 'email', '=', 'cust_email')
                        ->select('customers.*', 'sales.*')
                        ->where('sales.trans_date', 'like', '%'.Carbon::now()->toDateString().'%')->get();
                        
                        //$activeCustomers = Customer::all()->where('active', 1);
        
        view()->share('salesPDForder',$orders);
        view()->share('salesPDFdetail',$orderdetails);
        view()->share('salesPDFcust',$customers);
        view()->share('salesPDFprod',$products);
        view()->share('salesPDFmaxorder',$maxOrder);
        ////
        view()->share('customername',$name);
       


        $pdf = PDF::loadView('admin/salesPDF'); $pdf->setPaper('Legal', 'landscape'); 
        return $pdf->stream('sales/pdf.pdf');

        $salesPDForder=PDF::loadView('admin/salesPDF', compact('orders'));
        return $salesPDForder->stream('sales/pdf.pdf');

        $salesPDFdetail=PDF::loadView('admin/salesPDF', compact('orderdetails'));
        return $salesPDFdetail->stream('sales/pdf.pdf');

        $salesPDFcust=PDF::loadView('admin/salesPDF', compact('customers'));
        return $salesPDFcust->stream('sales/pdf.pdf');

        $salesPDFprod=PDF::loadView('admin/salesPDF', compact('products'));
        return $salesPDFprod->stream('sales/pdf.pdf');

        $salesPDFmaxorder=PDF::loadView('admin/salesPDF', compact('maxOrder'));
        return $salesPDFmaxorder->stream('sales/pdf.pdf');
        ///////////////////////
        $customername = PDF::loadView('admin/salesPDF', compact('name'));
        return $customername->stream('sales/pdf.pdf');




        //  $data = Entry::find($id);
        //  $pdf = PDF::loadView('posts.pdfview', compact('data'));
        //  return $pdf->stream();
    }

    public function SalesReport2(request $request)
    {
        $today = Carbon::today()->toDateString();
        $orders = Sales::all();   
        $orderdetails=OrderDetails::all(); 
        $customers=Customers::all();
        $products=Products::all();
        $sales=Sales::all();
        $maxOrder=count($orders);
        $maxSales=count($sales);
        //////////-
        $eggsales = \DB::table('sales')
                ->join('order_details', 'trans_id', '=', 'order_id')
                ->select('order_details.*', 'sales.*')
                ->where('product_name', '!=', 'Manure')
                ->orwhere('product_name', '!=', 'Cull')
                ->orwhere('product_name', '!=', 'Sacks')
                ->get();

        $extra = \DB::table('sales')
                  ->join('order_details', 'trans_id', '=', 'order_id')
                  ->select('order_details.*', 'sales.*')
                  ->where('product_name', '=', 'Manure')
                  ->orwhere('product_name', '=', 'Cull')
                  ->orwhere('product_name', '=', 'Sacks')
                  ->get();

        $totalorderextra =count($extra);
        $totalorderegg = count($eggsales);
       
        view()->share('salesPDForder',$orders);
        view()->share('salesPDFdetail',$orderdetails);
        view()->share('salesPDFcust',$customers);
        view()->share('salesPDFprod',$products);
        view()->share('salesPDFmaxorder',$maxOrder);
        view()->share('maxSales',$maxSales);
        //////
        view()->share('customername',$extra);
        view()->share('eggsales',$eggsales);
        view()->share('totalorderextra',$totalorderextra);
        view()->share('totalorderegg ',$totalorderegg );
       


        $pdf = PDF::loadView('admin/salesPDF2'); $pdf->setPaper('Legal', 'landscape'); 
        return $pdf->stream('sales/pdf2.pdf');

        $salesPDForder=PDF::loadView('admin/salesPDF2', compact('orders'));
        return $salesPDForder->stream('sales/pdf2.pdf');

        $salesPDFdetail=PDF::loadView('admin/salesPDF2', compact('orderdetails'));
        return $salesPDFdetail->stream('sales/pdf2.pdf');

        $salesPDFcust=PDF::loadView('admin/salesPDF2', compact('customers'));
        return $salesPDFcust->stream('sales/pdf2.pdf');

        $salesPDFprod=PDF::loadView('admin/salesPDF2', compact('products'));
        return $salesPDFprod->stream('sales/pdf2.pdf');

        $salesPDFmaxorder=PDF::loadView('admin/salesPDF2', compact('maxOrder'));
        return $salesPDFmaxorder->stream('sales/pdf2.pdf');
        /////
        $customername = PDF::loadView('admin/salesPDF', compact('extra'));
        return $customername->stream('sales/pdf2.pdf');

        $totalorderextra = PDF::loadView('admin/salesPDF', compact('totalorderextra'));
        return $totalorderextra->stream('sales/pdf2.pdf');

        $totalorderegg = PDF::loadView('admin/salesPDF', compact('totalorderegg'));
        return $totalorderegg->stream('sales/pdf2.pdf');

        $eggsales = PDF::loadView('admin/salesPDF', compact('eggsales'));
        return $eggsales->stream('sales/pdf2.pdf');


        $maxSales = PDF::loadView('admin/salesPDF', compact('maxSales'));
        return $maxSales->stream('sales/pdf2.pdf');


       

        // $data = Entry::find($id);
        // $pdf = PDF::loadView('posts.pdfview', compact('data'));
        // return $pdf->stream();
    }
   
   
}
