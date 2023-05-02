<!doctype html>
<html lang="en">
	<head>
		 <meta charset="utf-8" />
    	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>Population Report</title>
		<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    	<meta name="viewport" content="width=device-width" />

		<!-- Favicons -->
		<link href="{{ asset('img/favicon.png') }}" rel="icon">
		<link href="{{ asset('img/apple-icon.png') }}" rel="apple-touch-icon">

		<style>
				@page {
			 	margin: 100px 25px;
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

			.row{
				display: inline-block;
				vertical-align:top;
				display: inline-block;
    width: 40%;
    height: 30%;
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
				position: center;
				top: 60px;
			}

			header {
				position: fixed;
				top: -60px; 
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
			.pagenum:before {
    content: counter(page);
}

		</style>

	</head>
	<body>
	
		<header>
		<span class="pagenum"></span>
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
			<center><h3>Population Report</h3>

			<br>
			
			<table border="1px" cellpadding="10px" width="100%" >
						<tr>
							<th>Pullet Batch</th>
							<th>Mortality</th>
							<th>Causes of Death</th>
							<th>Date of Death</th>
						</tr>

						@if($deadpullettotal==0)
							<tr>
								<td colspan="9"><center>No data to show</center></td>
							</tr>

				@else
						@foreach($popPDFdeadpullet as $deadpul)
						<tr>
							<td>{{$deadpul->batch_id}}</td>
							<td>{{$deadpul->quantity}}</td>
							<td>{{$deadpul->remarks}}</td>
							<td>{{$deadpul->updated_at}}</td>
								
						</tr>

						@endforeach
						@endif

						
					</table>

					
					&nbsp;&nbsp;

					
			<table border="1px" cellpadding="10px" width="100%">
						<tr>
							<th>Chicken Batch</th>
							<th>Mortality</th>
							<th>Causes of Death</th>
							<th>Date of Death</th>
						</tr>

						
							@foreach($popPDFdead as $deadchic)

							<tr>
								<td>{{$deadchic->batch_id}}</td>
								<td>{{$deadchic->quantity}}</td>
								<td>{{$deadchic->remarks}}</td>
								<td>{{$deadchic->updated_at}}</td>
								
							</tr>

							@endforeach


						
					</table>
					<br>
	
		&nbsp;&nbsp;
			
			<table border="1px" cellpadding="10px" width="100%">
						<tr>
							<th>Pullets Batch</th>
							<th>Population</th>
							<th>Maturity Date</th>
						</tr>

					@for ($i = 0; $i < $popPDFmaxpullet; $i++)

					<tr>
					
						<td>{{$popPDFpullet[$i]->id}}</td>
						<td>{{$popPDFpullet[$i]->quantity}}</td>
						<td>{{$popPDFpul[$i]->maturity}}</td>
						
					</tr>

					@endfor

					<tr>
						<td><b>Total</b></td>
						<td><b>{{$popPDFpullet[2]->total}}</b></td>
						<td>---</td>
					</tr>
				</table>
	<br>

		

		

		</main>
		
	</body>

</html>