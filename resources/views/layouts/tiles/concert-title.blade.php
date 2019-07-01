<div class="x_panel">

    <div class="x_title">
        <h2>Daily active users <small>Sessions</small></h2>
        <ul class="nav navbar-right panel_toolbox">
            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
            </li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Settings 1</a>
                    </li>
                    <li><a href="#">Settings 2</a>
                    </li>
                </ul>
            </li>
            <li><a class="close-link"><i class="fa fa-close"></i></a>
            </li>
        </ul>
        <div class="clearfix"></div>
    </div>

    <div class="x_content">
        <div class="bs-example" data-example-id="simple-jumbotron">
            <div class="jumbotron">
                <h1>{{ $concert->venue }}</h1>
                <h3>{{ $concert->city }}, {{ $concert->country }}</h3>
                <p>{{ $concert->date }}</p>
            </div>
        </div>
    </div>

</div>
