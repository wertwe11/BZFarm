@extends ('layout.admin-main')

@section ('title', 'Sales')

@section ('token')

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}" />

@endsection

@section ('content')

<div class="row">
    <form action="{{route('sales-range')}}" method="GET">
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
    <button type="button" title="Report" class="btn btn-md btn-info" onclick="window.open('/sales/pdf', '_blank')">Daily Sales Report</button>

    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
    <button type="button" title="Report" class="btn btn-md btn-info" onclick="window.open('/sales/pdf2', '_blank')">Quantity Sold Report</button>
    

    <!-- Sales for the day -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header" data-background-color="blue">
                <h4 class="title">Sales Comparison</h4>
                <!-- <p class="category">Here is a subtitle for this table</p> -->
            </div>
            <div class="card-content">
                <canvas id="saleStats" width="400" height="150"></canvas>
                <br>
            </div>
        </div>
    </div>

</div>

@endsection

@section ('scripts')

<!-- SCRIPTS -->

<script>

$(document).ready(function(){

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
        url: "/sales/sales-stats",
        method: "GET",
        data:{
            from: $('#from').val(),
            to: $('#to').val()
        },
        success: function(data) {
            console.log('data', data.data);

            let x_label = [];
            let values = [];

            const response = data.data;
            for(let i=0;i<response.length;i++){
                x_label.push(response[i].trans_date);
                values.push(response[i].total_cost);
            }

            var chartdata = {
                labels: x_label,
                datasets : [
                    {
                        label: 'Sales',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        hoverBackgroundColor: 'rgba(255, 99, 132, 0.3)',
                        borderWidth: 1,
                        data: values
                    }
                ]
            };

            var ctx = $("#saleStats");

            var saleStats = new Chart(ctx, {
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

    
});

</script>

@endsection