<?php

namespace BZpoultryfarm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use BZpoultryfarm\Orders;
use BZpoultryfarm\OrderDetails;
use BZpoultryfarm\Products;
use BZpoultryfarm\Cull;
use BZpoultryfarm\order_list;
use BZpoultryfarm\User;
use BZpoultryfarm\Customers;
use Input;

class OrdersController extends Controller
{
    public function __construct()
    {
      $this->middleware('admin');
    }
//show all orders
    public function show()
    {
      //$orders = Orders::all()
       $orders = \DB::table('orders')
                    ->join('customers', 'cust_email', '=', 'email')
                     ->select('orders.*', 'customers.*')
                     ->orderBy('order_id', 'desc')
                     ->paginate(10);
      
      return view('admin.orders', ['user' => Auth::user(), 'orders' => $orders]);
    }

    //show searched record
    public function searchorder(Request $request)
    {
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $orders = Orders::whereBetween('trans_date', [$fromDate, $toDate])->paginate(10);
        

        return view('admin.orders', ['user' => Auth::user(), 'orders' => $orders]);
        
    }


    public function confirmReserve($id)
    {
    	// truncate pos cart list
    	order_list::truncate();

        $order = Orders::find($id);
       
    	$get = OrderDetails::where('order_id', '=', $order->order_id)->get();

        

        $total = 0.00; //initiate total to 0

            //for every items
    	foreach ($get as $item)
		{
            $list = new order_list();
            $product = Products::where('name', '=', $item->product_name)->first();

            $list->product_name = $item->product_name;
            
            if ($item->quantity >= 30) //item is greater than 30 then it is wholesale
                $list->mode = 'Wholesale';

            else
                $list->mode = 'Retail';   // if less than 30 then retail

            if ($list->mode == 'Wholesale') //if selected is wholesale
            {
                $price = $product->wholesale_price;
                $list->unit_price = $price;
            }

            else // if retail is selected
            {
                $price = $product->retail_price;
                $list->unit_price = $price;                
            }

            $list->quantity = $item->quantity;
            $list->unit_price = $price;
            $qty = $item->quantity;
            $list->order_price = $price*$qty;
            $total += $list->order_price;
            $list->total = $total;
            $list->stocks = $product->stocks;

            $list->save(); //save orders
		}

		return redirect('/pos')->with(['reserve' => $order, 'orderdetails' => $get]);
    }




    public function cancelReserve($id) //cancel reserves
    {
    	$order = Orders::find($id);

    	$order->status = 'Cancelled'; // set order status to cancelled

    	$order->update();

    	return redirect('/orders')->with('success', 'You have cancelled this order!');
    }




    public function orderDetails($id)
    {
       //$order = OrderDetails::where('order_id', '=', $id)->get();
    
       
       if(isset($id)){
            $order = \DB::table('order_details')
                 ->join('sales', 'order_details.order_id', '=', 'sales.trans_id')
                 ->join('customers', 'sales.cust_email', '=', 'customers.email')
                 ->where('order_details.order_id', '=', $id)->get();
         

        $total = 0.00;
        $sub = 0.00;

        foreach ($order as $item) // for every details display
        {
            $sub = $item->quantity * $item->unit_price;
            $total += $sub;
        }
        //pass details to admin view
        return view('admin.orderdetails')->with(['user' => Auth::user(), 'order' => $order, 'total' => $total]);
        
           
       }
    
       
        
            
        
        
        
        
        
    }
}






