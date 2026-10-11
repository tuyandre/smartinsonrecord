@if (session('swal'))
  <script>swal(@json(session('swal')));</script>
@endif
@if ($errors->any())
  <script>swal({ title: 'Error Occurred!', text: @json($errors->first()), icon: 'warning' });</script>
@endif
