
<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <script>
    window.Laravel = <?php echo json_encode([
      'csrfToken' => csrf_token(),
    ]); ?>
    </script>
    <title>Coin</title>
    <link href="{!! asset('css/morris.css') !!}" media="all" rel="stylesheet" type="text/css" />
    <link href="{!! asset('css/app.css') !!}" media="all" rel="stylesheet" type="text/css" />
    <link href="{!! asset('css/style-coco.css') !!}" media="all" rel="stylesheet" type="text/css" />
    <link href="{!! asset('css/style-responsive-coco.css') !!}" media="all" rel="stylesheet" type="text/css" />
    <link href="{!! asset('css/theme-customization.css') !!}" media="all" rel="stylesheet" type="text/css" />
  </head>
  <body class="fixed-left">
	   <div id="wrapper" >
       <div id="app">
         <!-- Top Bar Start -->
         <div class="topbar">
             <div class="topbar-left">
                 <div class="logo">
                     <h1><strong>Coin</strong>tracker</h1>
                 </div>

             </div>

             <div class="navbar navbar-default" role="navigation">
                 <div class="container">
                     <div class="navbar-collapse2">
                       @if (Auth::check())
                         <ul class="nav navbar-nav hidden-xs">

                             <li>
                               <ul class="nav navbar-nav">
                                 <li><router-link to="/revenue">Revenue</router-link></li>
                                 <li><router-link to="/portfolio">Portfolio</router-link></li>
                                 <li><router-link to="/history">History</router-link></li>
                               </ul>
                             </li>
                         </ul>

                         <ul class="nav navbar-nav navbar-right top-navbar mobile-nav">

                           <li class="dropdown topbar-profile">
                               <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i></a>
                               <ul class="dropdown-menu">
                                   <li><a href="#">Account Setting</a></li>
                                   <li class="divider"></li>
                                   <li><a href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                       Logout
                                   </a></li>
                                   <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                       {{ csrf_field() }}
                                   </form>
                               </ul>
                           </li>

                            <li class="dropdown topbar-profile topbar-mobile-nav">
                               <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bars"></i></a>
                               <ul class="dropdown-menu">
                                 <li><router-link to="/revenue" class="active subdrop">Revenue</router-link></li>
                                 <li><router-link to="/portfolio">Portfolio</router-link></li>
                                 <li><router-link to="/history">History</router-link></li>
                               </ul>
                            </li>

                            <li class="right-opener">
                               <a href="javascript:;" class="open-right"><i class="fa fa-angle-double-left"></i><i class="fa fa-angle-double-right"></i></a>
                            </li>
                         </ul>
                        @endif
                     </div>
                     <!--/.nav-collapse -->
                 </div>
             </div>
         </div>
         <!-- Top Bar End -->


          @yield('content')

     </div>
    <script src="/js/app.js"></script>
    <script src="/js/theme.js"></script>
  </body>
</html>
