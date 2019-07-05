<div class="x_panel">

    <div class="x_title">
        <h2>Setlist <small>Try hovering over the rows</small></h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
        </ul>
        <div class="clearfix"></div>
    </div>

    <div class="x_content">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Song</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($concert->setlist as $song)
                <tr>
                    <th scope="row">{{ $loop->iteration }}</th>
                    <td>{{ $song->title }} @include('layouts.partials.debut')</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
