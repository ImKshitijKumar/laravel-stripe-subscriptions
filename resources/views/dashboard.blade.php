@extends('layout')

@section('container')
<!-- Begin Page Content -->

<div class="container-fluid">

<!-- Page Heading -->
<h1 class="h3 mb-2 text-gray-800">Customers Subscription</h1>
@if(session('error') && session('error') !== "")
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{session('error')}}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <a href="{{url('export')}}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm float-right"><i class="fas fa-download fa-sm text-white-50"></i> Export</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Created At</th>
                        <th>Renewal Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                @if(isset($data) && $data !== "")
                <?php $total = 0; ?>
                <tbody>
                    @foreach($data as $list)
                    <tr>
                        <td>{{$list->customer}}</td>
                        <td>{{date('d-m-Y', $list->created)}}</td>
                        <td>{{date('d-m-Y', $list->current_period_end)}}</td>
                        <td>{{$list->items->data[0]->plan->amount/100}}</td>
                    </tr>
                    <?php $total = $total + ($list->items->data[0]->plan->amount/100); ?>
                    @endforeach
                    
                </tbody>
                <tr>
                    <td colspan="3"></td>
                    <td colspan="1"><b>Total :</b> {{$total}}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
</div>

</div>
<!-- /.container-fluid -->
@endsection