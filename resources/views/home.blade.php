<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <script>
    window.Laravel = <?php echo json_encode([
      'csrfToken' => csrf_token(),
    ]); ?>
    </script>
    <title>Coin</title>
    
    <link href="{!! asset('css/app.css') !!}" media="all" rel="stylesheet" type="text/css" />
    <link href="{!! asset('css/style-coco.css') !!}" media="all" rel="stylesheet" type="text/css" />
    <link href="{!! asset('css/style-responsive-coco.css') !!}" media="all" rel="stylesheet" type="text/css" />
  </head>
  <body>

    <div id="app">

        <nav class="navbar navbar-default">

          <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="#">Coin</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav">
                <li><router-link to="/revenue">Revenue</router-link></li>
                <li><router-link to="/portfolio">Portfolio</router-link></li>
                <li><router-link to="/history">History</router-link></li>
              </ul>

              <ul class="nav nav-pills navbar-right">
                <li class="refresh-button"><router-link to="/clear" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span></router-link></li>
              </ul>
              <p class="navbar-text navbar-right refresh-info"><small>Last refresh: {{ $lastUpdate }}</small></p>
            </div><!-- /.navbar-collapse -->


          </div><!-- /.container-fluid -->

        </nav>

      <router-view fiatsymbol="{{ $fiatsymbol }}" fiat="{{ $fiat }}"></router-view>
    </div>

    <script src="/js/app.js"></script>
    <script src="/js/init.js"></script>
  </body>
</html>
