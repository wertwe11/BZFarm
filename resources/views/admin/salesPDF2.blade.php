<!doctype html>
<html lang="en">
	<head>
		 <meta charset="utf-8" />
    	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>Quantity Sold Report</title>
		<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    	<meta name="viewport" content="width=device-width" />

		<!-- Favicons -->
		<link href="{{ asset('img/favicon.png') }}" rel="icon">
		<link href="{{ asset('img/apple-icon.png') }}" rel="apple-touch-icon">

		<style>
			@page {
			 	margin: 100px 25px;

			}

			.page-break {
			    page-break-after: always;
			}

			@font-face {
			    font-family: 'Arial';
			    font-weight: normal;
			    font-style: normal;
			    font-variant: normal;
			}
			
			body {
				font-family: Arial, sans-serif;
			}

			
			.tbl-header {
				margin-left: auto;
				margin-right: auto;
				border-bottom: 3px solid black;
			}

			.tbl-footer {
				border-top: 3px solid black;
			}

			table {
			    font-family: arial, sans-serif;
			    font-size: 14px;
			}

			td, th {
			    padding: 8px;
			}

			tr:nth-child(even) {
			    background-color: #dddddd;
			}


			main {
				margin-top: 60px;
				position: center;
			}

			header {
				position: fixed;
				top: -80px; 
				left: 0px; 
				right: 0px; 
				height: 50px;
			}
			
			footer {
				position: fixed;
				bottom: -90px; 
				left: 0px; 
				right: 0px; 
				height: 50px;
			}

		</style>

	</head>
	<body>
		<header>
			<table class="tbl-header">
				<tr>
					<td>
					
					</td>
					<td>
					<b>BZ Poultry Farms</b><br>
						 Brgy. Talisayan, Zamboanga City, Zamboanga Del  Sur <b>|</b> (012) 345-6789
					</td>
				</tr>
			</table>
		</header>

		<footer>
			<table class="tbl-footer">
				<tr>
					<td><small><i>Date Generated: {{ now() }}</i></small></td>
				</tr>
			</table>
		</footer>
		
		<main>
			<center><h3>Quantity Sold</h3>

			</table>




<table border="1px" cellpadding="10px" width="100%">
					<tr>
						<td colspan="9"><center><b>RETAIL</b></center></td>
						<td colspan="9"><center><b>EGGS</b></center></td>
					</tr>

					<tr>
						<th>Customer</th>
						<th>Jumbo</th>
						<th>Extra Large</th>
						<th>Large</th>
						<th>Medium</th>
						<th>Small</th>
						<th>Peewee</th>
						<th>Broken</th>
						<th>Employee</th>
					</tr>

					@if( $totalorderextra==0)
					<tr>
						<td colspan="9"><center>No data to show</center></td>
					</tr>

					@else

					@for ($i = 0; $i < $totalorderextra; $i++)

					<tr>
					    
					  
                        
                        
                        <td>{{$eggsales[$i]->cust_email}} </td>
						
						@if($eggsales[$i]->product_name == "Jumbo Eggs" && $eggsales[$i]->quantity < 30)
						<td>{{$eggsales[$i]->quantity}}</td>
						@else
						<td>0</td>
						@endif

						@if($eggsales[$i]->product_name == "Extra Large Eggs" && $eggsales[$i]->quantity < 30)
						<td>{{$eggsales[$i]->quantity}}</td>
						@else
						<td>0</td>
						@endif

						@if($eggsales[$i]->product_name == "Large Eggs" && $eggsales[$i]->quantity < 30)
						<td>{{$eggsales[$i]->quantity}}</td>
						@else
						<td>0</td>
						@endif

						@if($eggsales[$i]->product_name == "Medium Eggs" && $eggsales[$i]->quantity < 30)
						<td>{{$eggsales[$i]->quantity}}</td>
						@else
						<td>0</td>
						@endif

						@if($eggsales[$i]->product_name == "Small Eggs" && $eggsales[$i]->quantity < 30)
						<td>{{$eggsales[$i]->quantity}}</td>
						@else
						<td>0</td>
						@endif

						@if($eggsales[$i]->product_name == "Peewee Eggs" && $eggsales[$i]->quantity < 30)
						<td>{{$eggsales[$i]->quantity}}</td>
						@else
						<td>0</td>
						@endif

						@if($eggsales[$i]->product_name == "Broken Eggs" && $eggsales[$i]->quantity < 30)
						<td>{{$eggsales[$i]->quantity}}</td>
						@else
						<td>0</td>
						@endif

						<td>{{$eggsales[$i]->handled_by}}</td>
                        
                        	</tr>

					@endfor
					@endif

                            
                      
                        
					    
					    
					    
					    
					    
						
						
				
				</table>
			<br>
				<table border="1px" cellpadding="10px" width="100%">
	<tr>
		<td colspan="5"><center><b>EXTRA</b></center></td>
	</tr>

	<tr>
		<th>Customer</th>
		<th>Cull</th>
		<th>Manure</th>
		<th>Sacks</th>
		<th>Employee</th>
	</tr>

	@if($totalorderextra ==0)
	<tr>
		<td colspan="5"><center>No data to show</center></td>
	</tr>

	@else

	@for ($i = 0; $i < $totalorderextra ; $i++)

	<tr>
		<td>{{$customername[$i]->cust_email}}</td>
		
		@if($customername[$i]->product_name == "Cull")
		<td>{{$customername[$i]->quantity}}</td>
		@else
		<td>0</td>
		@endif

		@if($customername[$i]->product_name == "Manure")
		<td>{{$customername[$i]->quantity}}</td>
		@else
		<td>0</td>
		@endif

		@if($customername[$i]->product_name == "Sacks")
		<td>{{$customername[$i]->quantity}}</td>
		@else
		<td>0</td>



		@endif

		<td>{{$customername[$i]->handled_by}}</td>
		
	</tr>

	@endfor
	@endif

</table>

<br>



		</main>
		
	</body>

</html>