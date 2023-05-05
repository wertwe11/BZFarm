<?php

namespace BZpoultryfarm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use BZpoultryfarm\User;
use BZpoultryfarm\Orders;
use BZpoultryfarm\Chickens;
use BZpoultryfarm\TotalChickens;
use BZpoultryfarm\Cull;
use BZpoultryfarm\Customers;
use BZpoultryfarm\Feeds;
use BZpoultryfarm\DeadChickens;
use BZpoultryfarm\Pullets;
use BZpoultryfarm\TotalPullets;
use BZpoultryfarm\DeadPullets;
use BZpoultryfarm\UsersArchives;
use BZpoultryfarm\Activity;
use BZpoultryfarm\RejectEggs;

use Carbon\Carbon;
use PDF;

class PopulationController extends Controller
{

	// Show page only when authenticated

    private $from;
    private $to;
    private $from_time;
    private $to_time;

	public function __construct(Request $request)
	{
		$this->middleware('admin');
        $this->from = $request->input('from') ?? Carbon::now()->toDateString();
        $this->to = $request->input('to') ?? Carbon::now()->toDateString();

        $this->from_time = $this->from . ' 00:00:00';
        $this->to_time = $this->to . ' 23:59:00';

	}

    // Show

    public function show()
    {
    	return view('admin.population', ['from'=>$this->from, 'to'=>$this->to])->with('user', Auth::user());
    }
    //chart -> chicken
    public function popStats()
    {
        $chickens = TotalChickens::whereBetween('updated_at', [$this->from_time, $this->to_time])->get();

        return response()->json($chickens);
    }
    //chart -> culled chickens
    public function cullStats()
    {
        $cull = Cull::whereBetween('updated_at', [$this->from_time, $this->to_time])->take(5)->get();

        return response()->json($cull);
    }
    //chart -> chicken mortality
    public function deadStats()
    {
        $dead = DeadChickens::whereBetween('updated_at', [$this->from_time, $this->to_time])->get();

        return response()->json($dead);
    }

    public function popStatsPullets()
    {
        $pullets = TotalPullets::whereBetween('updated_at', [$this->from_time, $this->to_time])->get();

        return response()->json($pullets);
    }

    public function deadStatsPullets()
    {
        $deadpullets = DeadPullets::whereBetween('updated_at', [$this->from_time, $this->to_time])->get();

        return response()->json($deadpullets);
    }

    //Generate Report

    public function ChickenReport(request $request)
    {
    	$chickens=TotalChickens::all();
        $chic=Chickens::all();
        $culls=Cull::all();
        $deads=DeadChickens::all();
        $reject=RejectEggs::all();
        $pullets=TotalPullets::all();
        $pul=Pullets::all();
        $deadpullets=DeadPullets::all();
        $deadpullettotal=DeadPullets::sum('id');
        $maxChicken=TotalChickens::max('id');
        $maxPullet=TotalPullets::max('id');

        view()->share('popPDFchicken',$chickens);
        view()->share('popPDFchic',$chic);
        view()->share('popPDFcull',$culls);
        view()->share('popPDFdead',$deads);
        view()->share('popPDFreject',$reject);
        view()->share('popPDFpullet',$pullets);
        view()->share('popPDFpul',$pul);
        view()->share('popPDFdeadpullet',$deadpullets);
        view()->share('popPDFmaxchicken',$maxChicken);
        view()->share('popPDFmaxpullet',$maxPullet);
        view()->share('deadpullettotal',$deadpullettotal);


        $pdf = PDF::loadView('admin/popPDF'); $pdf->setPaper('Legal', 'landscape'); 
        return $pdf->stream('population/pdf.pdf');

        $popPDFchicken=PDF::loadView('admin/popPDF', compact('chickens'));
        return $popPDFchicken->stream('population/pdf.pdf');

        $popPDFchic=PDF::loadView('admin/popPDF', compact('chic'));
        return $popPDFchic->stream('population/pdf.pdf');

        $popPDFcull=PDF::loadView('admin/popPDF', compact('culls'));
        return $popPDFcull->stream('population/pdf.pdf');

        $popPDFdead=PDF::loadView('admin/popPDF', compact('deads'));
        return $popPDFdead->stream('population/pdf.pdf');
    
        $popPDFreject=PDF::loadView('admin/popPDF', compact('reject'));
        return $popPDFreject->stream('population/pdf.pdf');

        $popPDFpullet=PDF::loadView('admin/popPDF', compact('pullets'));
        return $popPDFpullet->stream('population/pdf.pdf');

        $popPDFpul=PDF::loadView('admin/popPDF', compact('pul'));
        return $popPDFpul->stream('population/pdf.pdf');

        $popPDFdeadpullet=PDF::loadView('admin/popPDF', compact('deadpullets'));
        return $popPDFdeadpullet->stream('population/pdf.pdf');

        $popPDFmaxchicken=PDF::loadView('admin/popPDF', compact('maxChicken'));
        return $popPDFmaxchicken->stream('population/pdf.pdf');

        $popPDFmaxpullet=PDF::loadView('admin/popPDF', compact('maxPullet'));
        return $popPDFmaxpullet->stream('population/pdf.pdf');


        $deadpullettotal=PDF::loadView('admin/popPDF', compact('deadpullettotal'));
        return $deadpullettotal->stream('population/pdf.pdf');

        // $data = Entry::find($id);
        // $pdf = PDF::loadView('posts.pdfview', compact('data'));
        // return $pdf->stream();
    }
}
