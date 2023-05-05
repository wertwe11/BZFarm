@extends ('layout.admin-main')

@section ('title', 'Production')

@section ('token')

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}" />

@endsection

@section ('content')


<div class="row">
    <form action="{{route('prod-range')}}" method="GET">
        <div class="form-group row">
            <label for="date">Start Date</label>
            <input type="date" name="from" id="from" value="{{ $from }}">
            <label for="date">End Date</label>
            <input type="date" name="to" id="to" value="{{ $to }}">
            <button type="submit" class="btn btn-md btn-info">Search</button>
        </div>
    </form>
</div>
	
<div class="row">

<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
    <button type="button" title="Report" class="btn btn-md btn-info" onclick="window.open('/prod/pdf', '_blank')">Production Report</button>

	<!-- Production Chart for Total Number of Eggs -->
	<div class="col-lg-12">
		<div class="card">
			<div class="card-header" data-background-color="blue">
	            <h4 class="title">Total Number of Eggs</h4>
	            <!-- <p class="category">Here is a subtitle for this table</p> -->
	        </div>  
	        <div class="card-content">
				<div class="table-responsive">
                    <table class="table">
                        <thead class="text-primary bold">
                            <tr>
                                <th>Jumbo</th>
                                <th>Extra Large</th>
                                <th>Large</th>
                                <th>Medium</th>
                                <th>Small</th>
                                <th>Peewee</th>
                                <th>Soft-shell</th>
                           </tr>
                        </thead>
                        <tbody>

                            @if ($inv == null)
                            <tr>
                                <td colspan="7"><center><b>No items to show.</b></center></td>
                            </tr>

                            @else

                                <tr>
                                    <td>{{ $inv->total_jumbo }}</td>
                                    <td>{{ $inv->total_xlarge }}</td>
                                    <td>{{ $inv->total_large }}</td>
                                    <td>{{ $inv->total_medium }}</td>
                                    <td>{{ $inv->total_small }}</td>
                                    <td>{{ $inv->total_peewee }}</td>
                                    <td>{{ $inv->total_softshell }}</td>
                                </tr>

                            @endif
                            
                        </tbody>
                    </table>
                </div>
			</div>
		</div>
	</div>

</div>

<div class="row">

	<div class="col-lg-12">
		<div class="card">
			<div class="card-header" data-background-color="blue">
	            <h4 class="title">Production Statistics</h4>
	        </div>
	        <div class="card-content">
				<canvas id="prodStats" width="400" height="150"></canvas>
	        </div>
		</div>
	</div>
    
</div>

<div class="row">
	<div class="col-lg-12">
        <div class="card">
	        <div class="card-header" data-background-color="green">
	            <h4 class="title">Feed Consumption Statistics</h4>
	        </div>
	        <div class="card-content">

				<table class="table table-responsive table-hover" id="items">
					<thead class="text-primary bold">
						<tr>
							<th>Date</th>
							<th>Current Chicken</th>
							<th>Feed stock in grams</th>
							<th>Feed stock left</th>
							<th>Feed stock left in %</th>
						</tr>
					</thead>
					<tbody id="fc">
                        <tr>
                            <td colspan="5"><center><b>No Record Found.</b></center></td>
                        </tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
        <div class="card">
	        <div class="card-header" data-background-color="red">
	            <h4 class="title">Production performance</h4>
	        </div>
	        <div class="card-content">

				<table class="table table-responsive table-hover" id="items">
					<thead class="text-primary bold">
						<tr>
							<th>Date</th>
							<th>Current Eggs</th>
							<th>Egg Production Rate</th>
							<th>Current Chickens</th>
							<th>Chickens Mortality Rate</th>
							<th>Current Pullets</th>
							<th>Pullets Mortality Rate</th>
							<th>Bird Mortality Rate</th>
						</tr>
					</thead>
					<tbody id="pp">
                        <tr>
                            <td colspan="5"><center><b>No Record Found.</b></center></td>
                        </tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection

@section ('scripts')

<script>

$(document).ready(function(){

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
        data:{
            from: $('#from').val(),
            to: $('#to').val()
      }
    });

    $.ajax({
        url: "/production/production-stats",
        method: "GET",
        success: function(data) {
            console.log(data);
            var batch = [];
            var total = [];
           
            for (var i in data) {
                batch.push('Batch ' + data[i].batch_id);
                total.push(data[i].jumbo + data[i].xlarge + data[i].large + data[i].medium + data[i].small + data[i].peewee);
            }

            var chartdata = {
                labels: batch,
                datasets : [
                    {
                        label: 'Total Number of Eggs',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        hoverBackgroundColor: 'rgba(255, 99, 132, 0.3)',
                        borderWidth: 1,
                        data: total
                    }
                ]
            };

            var ctx = $("#prodStats");

            var prodStats = new Chart(ctx, {
                type: 'line',
                data: chartdata,
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        },
        error: function(data) {
            console.log(data);
        }
    });
    $.ajax({
        url: "/production/feed-consumption",
        method: "GET",
        success: function(data) {
            console.log(data);
            if(!data.length){
               return false;
            }
            $('#fc').empty();
            for(let i=0;i<data.length;i++){
                const perc = (parseFloat(data[i].stock_left) / (parseFloat(data[i].stock_left) + parseFloat(data[i].consumption))) * 100
                let row = `<tr>
                            <td>${data[i].feed_date}</td>
                            <td>${data[i].current_chicken}</td>
                            <td>${data[i].consumption}</td>
                            <td>${data[i].stock_left}</td>
                            <td>${perc.toFixed(2)} %</td>
                        </tr>`;
                $('#fc').append(row);
            }
        },
        error: function(data) {
            console.log(data);
        }
    });
    $.ajax({
        url: "/production/prod-perf",
        method: "GET",
        success: function(data) {
            console.log("prod-perf", data);
            if(!data.length){
               return false;
            }
            $('#pp').empty();
            for(let i=0;i<data.length;i++){
                const egg_perc = (parseFloat(data[i].total_eggs) / parseFloat(data[i].current_chicken)) * 100;
                const chick_mort = (parseFloat(data[i].total_dead_pullets) / parseFloat(data[i].current_chicken)) * 100;
                const pull_mort = (parseFloat(data[i].total_dead_pullets) / parseFloat(data[i].current_pullets)) * 100;
                const bird_mort = ((parseFloat(data[i].total_dead_pullets) + parseFloat(data[i].total_dead_pullets)) / (parseFloat(data[i].current_chicken) + parseFloat(data[i].current_pullets))) * 100;
                let row = `<tr>
                            <td>${data[i].egg_date}</td>
                            <td>${data[i].total_eggs}</td>
                            <td>${egg_perc.toFixed(2)} %</td>
                            <td>${data[i].current_chicken}</td>
                            <td>${chick_mort.toFixed(2)} %</td>
                            <td>${data[i].current_pullets}</td>
                            <td>${pull_mort.toFixed(2)} %</td>
                            <td>${bird_mort.toFixed(2)} %</td>
                        </tr>`;
                $('#pp').append(row);
            }
        },
        error: function(data) {
            console.log(data);
        }
    });
});

</script>

@endsection