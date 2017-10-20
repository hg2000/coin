@extends('layouts.main')

@section('content')
  <router-view fiatsymbol="{{ $fiatsymbol }}" fiat="{{ $fiat }}"></router-view>
@endsection
